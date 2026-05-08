<?php

declare(strict_types=1);

namespace AiPageAssistant\AI;

final class FreeModelResolver
{
    private const ENDPOINT = 'https://shir-man.com/api/free-llm/top-models';
    private const TRANSIENT = 'ai_page_assistant_free_model';
    private const FALLBACK = 'openrouter/free';

    public function resolve(string $configuredModel): string
    {
        if ($configuredModel !== 'auto/free-best') {
            return $configuredModel;
        }

        $cached = get_transient(self::TRANSIENT);

        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = wp_remote_get(self::ENDPOINT, [
            'timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            return self::FALLBACK;
        }

        $body = wp_remote_retrieve_body($response);
        $payload = json_decode($body, true);
        $model = $payload['models'][0]['id'] ?? $payload['fallback']['id'] ?? self::FALLBACK;
        $model = is_string($model) && $model !== '' ? $model : self::FALLBACK;

        set_transient(self::TRANSIENT, $model, 6 * HOUR_IN_SECONDS);

        return $model;
    }
}
