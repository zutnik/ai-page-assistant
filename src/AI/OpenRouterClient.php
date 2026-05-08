<?php

declare(strict_types=1);

namespace AiPageAssistant\AI;

use RuntimeException;

final class OpenRouterClient implements AiClientInterface
{
    private const ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly int $timeoutSec = 60
    ) {
    }

    /**
     * @param list<array{role:string,content:string}> $messages
     * @return \Generator<string>
     */
    public function streamChat(array $messages): \Generator
    {
        if ($this->apiKey === '') {
            if (defined('AI_PAGE_ASSISTANT_DEV_MODE') && AI_PAGE_ASSISTANT_DEV_MODE) {
                yield from $this->fakeDevStream($messages);
                return;
            }

            throw new RuntimeException('OpenRouter API key is missing.');
        }

        if (! function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for streaming.');
        }

        $payload = wp_json_encode([
            'model' => $this->model,
            'stream' => true,
            'messages' => $messages,
            'temperature' => 0.2,
        ]);

        if (! is_string($payload)) {
            throw new RuntimeException('Could not encode OpenRouter payload.');
        }

        $chunkBuffer = '';
        $tokenQueue = [];
        $errorMessage = null;

        $curl = curl_init(self::ENDPOINT);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . home_url('/'),
                'X-Title: AI Page Assistant',
            ],
            CURLOPT_TIMEOUT => $this->timeoutSec,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER => false,
            CURLOPT_WRITEFUNCTION => static function ($handle, string $chunk) use (&$chunkBuffer, &$tokenQueue, &$errorMessage): int {
                $chunkBuffer .= $chunk;

                while (($pos = strpos($chunkBuffer, "\n\n")) !== false) {
                    $event = substr($chunkBuffer, 0, $pos);
                    $chunkBuffer = substr($chunkBuffer, $pos + 2);

                    foreach (explode("\n", $event) as $line) {
                        $line = trim($line);

                        if (! str_starts_with($line, 'data: ')) {
                            continue;
                        }

                        $data = substr($line, 6);

                        if ($data === '[DONE]') {
                            continue;
                        }

                        $json = json_decode($data, true);

                        if (! is_array($json)) {
                            continue;
                        }

                        if (isset($json['error']['message'])) {
                            $errorMessage = (string) $json['error']['message'];
                            continue;
                        }

                        $delta = $json['choices'][0]['delta']['content'] ?? '';

                        if (is_string($delta) && $delta !== '') {
                            $tokenQueue[] = $delta;
                        }
                    }
                }

                return strlen($chunk);
            },
        ]);

        $multi = curl_multi_init();
        curl_multi_add_handle($multi, $curl);

        try {
            do {
                $status = curl_multi_exec($multi, $running);

                while ($tokenQueue !== []) {
                    yield array_shift($tokenQueue);
                }

                if ($status > CURLM_OK) {
                    throw new RuntimeException('OpenRouter streaming failed.');
                }

                if ($running) {
                    curl_multi_select($multi, 0.2);
                }
            } while ($running);

            while ($tokenQueue !== []) {
                yield array_shift($tokenQueue);
            }

            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($errorMessage !== null) {
                throw new RuntimeException($errorMessage);
            }

            if ($httpCode >= 400) {
                throw new RuntimeException('OpenRouter returned HTTP ' . $httpCode . '.');
            }
        } finally {
            curl_multi_remove_handle($multi, $curl);
            curl_multi_close($multi);
            curl_close($curl);
        }
    }

    /**
     * @param list<array{role:string,content:string}> $messages
     * @return \Generator<string>
     */
    private function fakeDevStream(array $messages): \Generator
    {
        $question = end($messages)['content'] ?? 'this page';
        $answer = 'Dev mode response: I would answer this question using the current page context: "' . substr((string) $question, 0, 120) . '".';

        foreach (str_split($answer, 12) as $part) {
            yield $part;
            usleep(50000);
        }
    }
}
