<?php

declare(strict_types=1);

namespace AiPageAssistant\Admin;

use AiPageAssistant\Support\Sanitizer;
use AiPageAssistant\Support\Settings;

final class SettingsPage
{
    public function __construct(private readonly Settings $settings)
    {
    }

    public function register(): void
    {
        add_options_page(
            __('AI Assistant', 'ai-page-assistant'),
            __('AI Assistant', 'ai-page-assistant'),
            'manage_options',
            'ai-page-assistant',
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting('ai_page_assistant', Settings::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize'],
            'default' => $this->settings->defaults(),
        ]);
    }

    /** @param array<string, mixed> $input */
    public function sanitize(array $input): array
    {
        $postTypes = array_map(
            [Sanitizer::class, 'key'],
            isset($input['enabled_post_types']) && is_array($input['enabled_post_types']) ? $input['enabled_post_types'] : []
        );

        return [
            'api_key' => Sanitizer::text($input['api_key'] ?? '', 300),
            'model' => Sanitizer::choice(
                $input['model'] ?? '',
                array_keys(self::models()),
                'anthropic/claude-3.5-haiku'
            ),
            'system_prompt' => Sanitizer::textarea($input['system_prompt'] ?? '', 8000),
            'hourly_limit' => Sanitizer::int($input['hourly_limit'] ?? 20, 1, 500),
            'daily_limit' => Sanitizer::int($input['daily_limit'] ?? 100, 1, 5000),
            'enabled_post_types' => array_values(array_filter($postTypes)),
            'widget_position' => Sanitizer::choice($input['widget_position'] ?? '', ['bottom-right', 'bottom-left'], 'bottom-right'),
            'primary_color' => Sanitizer::hexColor($input['primary_color'] ?? '#2563eb'),
            'greeting' => Sanitizer::text($input['greeting'] ?? '', 160),
            'store_logs' => Sanitizer::bool($input['store_logs'] ?? false),
            'consent_required' => Sanitizer::bool($input['consent_required'] ?? false),
            'retention_days' => Sanitizer::int($input['retention_days'] ?? 30, 1, 365),
            'ip_anonymization' => Sanitizer::choice($input['ip_anonymization'] ?? '', ['immediate', 'delayed', 'never'], 'immediate'),
        ];
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $settings = $this->settings->all();
        $models = self::models();
        $postTypes = get_post_types(['public' => true], 'objects');

        require AI_PAGE_ASSISTANT_PATH . 'src/Admin/views/settings.php';
    }

    /** @return array<string, string> */
    public static function models(): array
    {
        return [
            'anthropic/claude-3.5-haiku' => 'Claude 3.5 Haiku (fast, low cost)',
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet (strong reasoning)',
            'openai/gpt-4o-mini' => 'GPT-4o mini (budget FAQ)',
            'google/gemini-flash-1.5' => 'Gemini Flash 1.5 (fast)',
            'meta-llama/llama-3.3-70b-instruct' => 'Llama 3.3 70B Instruct',
        ];
    }
}
