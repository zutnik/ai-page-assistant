<?php

declare(strict_types=1);

namespace AiPageAssistant;

use AiPageAssistant\Admin\LogsPage;
use AiPageAssistant\Admin\SettingsPage;
use AiPageAssistant\Api\RestController;
use AiPageAssistant\Frontend\AssetLoader;
use AiPageAssistant\Frontend\ChatWidget;
use AiPageAssistant\Repository\LogRepository;
use AiPageAssistant\Support\Settings;

final class Plugin
{
    private static ?self $instance = null;

    private bool $booted = false;

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public static function deactivate(): void
    {
        wp_clear_scheduled_hook('ai_page_assistant_daily_retention');
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $settings = new Settings();
        $logRepository = new LogRepository();

        add_action('init', [$this, 'loadTextDomain']);
        add_action('admin_menu', [new SettingsPage($settings), 'register']);
        add_action('admin_menu', [new LogsPage($logRepository, $settings), 'register']);
        add_action('admin_init', [new SettingsPage($settings), 'registerSettings']);
        add_action('rest_api_init', [new RestController($settings, $logRepository), 'registerRoutes']);
        add_action('wp_enqueue_scripts', [new AssetLoader($settings), 'enqueue']);
        add_action('wp_footer', [new ChatWidget($settings), 'render']);
        add_action('ai_page_assistant_daily_retention', [$logRepository, 'pruneByRetentionSetting']);

        $this->booted = true;
    }

    public function loadTextDomain(): void
    {
        load_plugin_textdomain(
            'ai-page-assistant',
            false,
            dirname(plugin_basename(AI_PAGE_ASSISTANT_FILE)) . '/languages'
        );
    }

    private function __construct()
    {
    }
}
