<?php

declare(strict_types=1);

namespace AiPageAssistant\Api;

use AiPageAssistant\AI\ContextBuilder;
use AiPageAssistant\AI\FreeModelResolver;
use AiPageAssistant\AI\OpenRouterClient;
use AiPageAssistant\AI\PromptBuilder;
use AiPageAssistant\Repository\LogRepository;
use AiPageAssistant\Repository\PageContentRepository;
use AiPageAssistant\Support\IpAnonymizer;
use AiPageAssistant\Support\Sanitizer;
use AiPageAssistant\Support\Settings;
use Throwable;
use WP_REST_Request;
use WP_REST_Response;

final class ChatEndpoint
{
    public function __construct(
        private readonly Settings $settings,
        private readonly LogRepository $logs
    ) {
    }

    public function handle(WP_REST_Request $request): WP_REST_Response
    {
        $ip = $this->clientIp();
        $rate = (new RateLimiter($this->settings))->checkAndIncrement($ip);

        if (! $rate['allowed']) {
            return new WP_REST_Response([
                'error' => ['message' => 'Rate limit exceeded. Please try again later.'],
                'retry_after' => $rate['retry_after'],
            ], 429);
        }

        $message = Sanitizer::textarea($request->get_param('message'), 2000);
        $pageId = Sanitizer::int($request->get_param('page_id'), 0, PHP_INT_MAX);
        $pageTitle = Sanitizer::text($request->get_param('page_title'), 240);
        $pageUrl = esc_url_raw((string) $request->get_param('page_url'));
        $pageText = Sanitizer::textarea($request->get_param('page_text'), 12000);
        $visitorId = Sanitizer::text($request->get_param('visitor_id'), 80);
        $language = Sanitizer::text($request->get_param('language'), 16);

        if ($message === '') {
            return new WP_REST_Response(['error' => ['message' => 'Message is required.']], 400);
        }

        $started = microtime(true);
        $answer = '';
        $ipHash = (new IpAnonymizer())->hash($ip);

        try {
            $context = (new ContextBuilder(new PageContentRepository(), $this->settings))->buildForQuery($pageId, $message, [
                'title' => $pageTitle,
                'url' => $pageUrl,
                'text' => $pageText,
            ]);
            $messages = (new PromptBuilder($this->settings->systemPrompt()))->build($context, $message, $language);
            $model = (new FreeModelResolver())->resolve($this->settings->model());
            $client = new OpenRouterClient($this->settings->apiKey(), $model);

            $this->startStream();

            foreach ($client->streamChat($messages) as $token) {
                $answer .= $token;
                $this->sendEvent(['type' => 'token', 'content' => $token]);
            }

            $this->sendEvent(['type' => 'done']);
            $this->storeLog($visitorId, $pageId, $language, $message, $answer, $ipHash, $started, '', $model);
            exit;
        } catch (Throwable $throwable) {
            if (headers_sent()) {
                $this->sendEvent(['type' => 'error', 'message' => $throwable->getMessage()]);
                $this->storeLog($visitorId, $pageId, $language, $message, $answer, $ipHash, $started, $throwable->getMessage(), $model ?? $this->settings->model());
                exit;
            }

            $this->storeLog($visitorId, $pageId, $language, $message, $answer, $ipHash, $started, $throwable->getMessage(), $model ?? $this->settings->model());

            return new WP_REST_Response(['error' => ['message' => $throwable->getMessage()]], 500);
        }
    }

    private function startStream(): void
    {
        nocache_headers();
        header('Content-Type: text/event-stream; charset=utf-8');
        header('X-Accel-Buffering: no');

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_implicit_flush(true);
    }

    /** @param array<string, mixed> $payload */
    private function sendEvent(array $payload): void
    {
        echo 'data: ' . wp_json_encode($payload) . "\n\n";
        flush();
    }

    private function storeLog(
        string $visitorId,
        int $pageId,
        string $language,
        string $question,
        string $answer,
        string $ipHash,
        float $started,
        string $error = '',
        ?string $model = null
    ): void {
        if (! $this->settings->storeLogs()) {
            return;
        }

        $this->logs->insert([
            'visitor_id' => $visitorId,
            'page_id' => $pageId,
            'page_url' => get_permalink($pageId) ?: '',
            'visitor_language' => $language,
            'model' => $model ?? $this->settings->model(),
            'question' => $question,
            'answer' => $answer,
            'ip_hash' => $ipHash,
            'latency_ms' => (int) ((microtime(true) - $started) * 1000),
            'error_message' => $error,
        ]);
    }

    private function clientIp(): string
    {
        $candidates = [$_SERVER['REMOTE_ADDR'] ?? ''];

        foreach ($candidates as $candidate) {
            $ip = trim((string) $candidate);

            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '127.0.0.1';
    }
}
