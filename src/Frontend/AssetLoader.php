<?php

declare(strict_types=1);

namespace AiPageAssistant\Frontend;

use AiPageAssistant\Api\RestController;
use AiPageAssistant\Support\Settings;

final class AssetLoader
{
    public function __construct(private readonly Settings $settings)
    {
    }

    public function enqueue(): void
    {
        if (! $this->shouldLoad()) {
            return;
        }

        $css = 'assets/css/widget.css';
        $js = 'assets/js/dist/widget.js';

        wp_enqueue_style(
            'ai-page-assistant-widget',
            AI_PAGE_ASSISTANT_URL . $css,
            [],
            $this->assetVersion($css)
        );

        wp_enqueue_script(
            'ai-page-assistant-widget',
            AI_PAGE_ASSISTANT_URL . $js,
            [],
            $this->assetVersion($js),
            true
        );

        wp_localize_script('ai-page-assistant-widget', 'aiPageAssistant', [
            'apiBase' => esc_url_raw(rest_url(RestController::NAMESPACE)),
            'nonce' => wp_create_nonce('wp_rest'),
            'pageId' => get_queried_object_id(),
            'language' => substr(get_locale(), 0, 2),
            'greeting' => $this->settings->greeting(),
            'primaryColor' => $this->settings->primaryColor(),
            'consentRequired' => $this->settings->consentRequired(),
            'strings' => [
                'button' => __('Ask AI', 'ai-page-assistant'),
                'placeholder' => __('Ask about this page...', 'ai-page-assistant'),
                'send' => __('Send', 'ai-page-assistant'),
                'consent' => __('AI answers may be inaccurate. Do not share sensitive personal data.', 'ai-page-assistant'),
                'accept' => __('I understand', 'ai-page-assistant'),
                'deleteData' => __('Delete my AI chat data', 'ai-page-assistant'),
            ],
        ]);
    }

    private function shouldLoad(): bool
    {
        if (is_admin() || is_feed() || wp_is_json_request()) {
            return false;
        }

        return true;
    }

    private function assetVersion(string $relativePath): string
    {
        $path = AI_PAGE_ASSISTANT_PATH . $relativePath;

        return file_exists($path) ? (string) filemtime($path) : AI_PAGE_ASSISTANT_VERSION;
    }
}
