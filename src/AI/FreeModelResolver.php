<?php

declare(strict_types=1);

namespace AiPageAssistant\AI;

final class FreeModelResolver
{
    private const ENDPOINT = 'https://shir-man.com/api/free-llm/top-models';
    private const TRANSIENT = 'ai_page_assistant_free_model';
    private const LAST_GOOD_OPTION = 'ai_page_assistant_last_free_model';
    private const FALLBACK = 'openrouter/free';
    private const FALLBACK_MODELS = [
        'openrouter/owl-alpha',
        'nvidia/nemotron-3-super-120b-a12b:free',
        'google/gemma-4-31b-it:free',
        'minimax/minimax-m2.5:free',
        'openrouter/free',
    ];

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
            return $this->fallbackModel();
        }

        $body = wp_remote_retrieve_body($response);
        $payload = json_decode($body, true);
        $model = $payload['models'][0]['id'] ?? $payload['fallback']['id'] ?? self::FALLBACK;
        $model = is_string($model) && $this->isAllowedFreeModel($model) ? $model : $this->fallbackModel();

        set_transient(self::TRANSIENT, $model, DAY_IN_SECONDS);
        update_option(self::LAST_GOOD_OPTION, $model, false);

        return $model;
    }

    private function isAllowedFreeModel(string $model): bool
    {
        if ($model === self::FALLBACK || $model === 'openrouter/owl-alpha') {
            return true;
        }

        return str_ends_with($model, ':free');
    }

    private function fallbackModel(): string
    {
        $lastGood = get_option(self::LAST_GOOD_OPTION);

        if (is_string($lastGood) && $this->isAllowedFreeModel($lastGood)) {
            return $lastGood;
        }

        return self::FALLBACK_MODELS[0];
    }
}
