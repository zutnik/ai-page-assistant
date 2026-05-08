<?php

declare(strict_types=1);

namespace AiPageAssistant\Support;

final class Settings
{
    public const OPTION = 'ai_page_assistant_settings';

    /** @return array<string, mixed> */
    public function all(): array
    {
        $stored = get_option(self::OPTION, []);

        return array_merge($this->defaults(), is_array($stored) ? $stored : []);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        return $settings[$key] ?? $default;
    }

    public function apiKey(): string
    {
        return (string) $this->get('api_key', '');
    }

    public function model(): string
    {
        return (string) $this->get('model', 'anthropic/claude-3.5-haiku');
    }

    public function systemPrompt(): string
    {
        return (string) $this->get('system_prompt', '');
    }

    public function hourlyLimit(): int
    {
        return max(1, (int) $this->get('hourly_limit', 20));
    }

    public function dailyLimit(): int
    {
        return max($this->hourlyLimit(), (int) $this->get('daily_limit', 100));
    }

    /** @return list<string> */
    public function enabledPostTypes(): array
    {
        $types = $this->get('enabled_post_types', ['page', 'post']);

        if (! is_array($types)) {
            return ['page', 'post'];
        }

        return array_values(array_filter(array_map('sanitize_key', $types)));
    }

    public function storeLogs(): bool
    {
        return (bool) $this->get('store_logs', true);
    }

    public function consentRequired(): bool
    {
        return (bool) $this->get('consent_required', true);
    }

    public function retentionDays(): int
    {
        return max(1, (int) $this->get('retention_days', 30));
    }

    public function anonymizationMode(): string
    {
        $mode = (string) $this->get('ip_anonymization', 'immediate');

        return in_array($mode, ['immediate', 'delayed', 'never'], true) ? $mode : 'immediate';
    }

    public function primaryColor(): string
    {
        return (string) $this->get('primary_color', '#2563eb');
    }

    public function greeting(): string
    {
        return (string) $this->get('greeting', 'Hi! Ask me anything about this page.');
    }

    /** @return array<string, mixed> */
    public function defaults(): array
    {
        return [
            'api_key' => '',
            'model' => 'anthropic/claude-3.5-haiku',
            'system_prompt' => '',
            'hourly_limit' => 20,
            'daily_limit' => 100,
            'enabled_post_types' => ['page', 'post'],
            'widget_position' => 'bottom-right',
            'primary_color' => '#2563eb',
            'greeting' => 'Hi! Ask me anything about this page.',
            'store_logs' => true,
            'consent_required' => true,
            'retention_days' => 30,
            'ip_anonymization' => 'immediate',
        ];
    }
}
