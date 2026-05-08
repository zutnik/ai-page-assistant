<?php

declare(strict_types=1);

namespace AiPageAssistant\Api;

use AiPageAssistant\Repository\LogRepository;
use AiPageAssistant\Support\Sanitizer;
use AiPageAssistant\Support\Settings;
use WP_REST_Request;
use WP_REST_Response;

final class RestController
{
    public const NAMESPACE = 'ai-assistant/v1';

    public function __construct(
        private readonly Settings $settings,
        private readonly LogRepository $logs
    ) {
    }

    public function registerRoutes(): void
    {
        register_rest_route(self::NAMESPACE, '/chat', [
            'methods' => 'POST',
            'callback' => [new ChatEndpoint($this->settings, $this->logs), 'handle'],
            'permission_callback' => [$this, 'verifyNonce'],
            'args' => [
                'message' => ['required' => true, 'type' => 'string'],
                'page_id' => ['required' => true, 'type' => 'integer'],
                'page_title' => ['required' => false, 'type' => 'string'],
                'page_url' => ['required' => false, 'type' => 'string'],
                'page_text' => ['required' => false, 'type' => 'string'],
                'visitor_id' => ['required' => false, 'type' => 'string'],
                'language' => ['required' => false, 'type' => 'string'],
            ],
        ]);

        register_rest_route(self::NAMESPACE, '/data', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteVisitorData'],
            'permission_callback' => [$this, 'verifyNonce'],
            'args' => [
                'visitor_id' => ['required' => true, 'type' => 'string'],
            ],
        ]);
    }

    public function verifyNonce(WP_REST_Request $request): bool
    {
        $nonce = (string) $request->get_header('X-WP-Nonce');

        return wp_verify_nonce($nonce, 'wp_rest') !== false;
    }

    public function deleteVisitorData(WP_REST_Request $request): WP_REST_Response
    {
        $visitorId = Sanitizer::text($request->get_param('visitor_id'), 80);

        if ($visitorId === '') {
            return new WP_REST_Response(['error' => ['message' => 'Missing visitor id.']], 400);
        }

        return new WP_REST_Response(['deleted' => $this->logs->deleteByVisitorId($visitorId)], 200);
    }
}
