<?php

declare(strict_types=1);

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table = $wpdb->prefix . 'ai_assistant_logs';
$wpdb->query("DROP TABLE IF EXISTS {$table}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

delete_option('ai_page_assistant_settings');
delete_option('ai_page_assistant_db_version');

wp_clear_scheduled_hook('ai_page_assistant_daily_retention');
