<?php

declare(strict_types=1);

namespace AiPageAssistant;

final class Activator
{
    private const DB_VERSION = '1.0.0';

    public static function activate(): void
    {
        self::createLogsTable();
        self::createDefaultSettings();

        if (! wp_next_scheduled('ai_page_assistant_daily_retention')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'ai_page_assistant_daily_retention');
        }
    }

    private static function createLogsTable(): void
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $table = $wpdb->prefix . 'ai_assistant_logs';
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            visitor_id varchar(64) NOT NULL DEFAULT '',
            page_id bigint(20) unsigned NULL,
            page_url text NULL,
            visitor_language varchar(12) NOT NULL DEFAULT '',
            model varchar(120) NOT NULL DEFAULT '',
            question text NOT NULL,
            answer longtext NULL,
            ip_hash char(64) NOT NULL DEFAULT '',
            tokens_prompt int unsigned NOT NULL DEFAULT 0,
            tokens_completion int unsigned NOT NULL DEFAULT 0,
            latency_ms int unsigned NOT NULL DEFAULT 0,
            error_message text NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY visitor_id (visitor_id),
            KEY page_id (page_id),
            KEY created_at (created_at),
            KEY ip_hash (ip_hash)
        ) {$charsetCollate};";

        dbDelta($sql);
        update_option('ai_page_assistant_db_version', self::DB_VERSION);
    }

    private static function createDefaultSettings(): void
    {
        if (get_option('ai_page_assistant_settings') !== false) {
            return;
        }

        add_option('ai_page_assistant_settings', [
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
        ]);
    }
}
