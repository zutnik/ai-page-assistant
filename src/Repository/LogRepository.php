<?php

declare(strict_types=1);

namespace AiPageAssistant\Repository;

use AiPageAssistant\Support\Settings;

final class LogRepository
{
    /** @param array<string, mixed> $data */
    public function insert(array $data): int
    {
        global $wpdb;

        $table = $this->table();
        $wpdb->insert(
            $table,
            [
                'visitor_id' => (string) ($data['visitor_id'] ?? ''),
                'page_id' => isset($data['page_id']) ? (int) $data['page_id'] : null,
                'page_url' => (string) ($data['page_url'] ?? ''),
                'visitor_language' => (string) ($data['visitor_language'] ?? ''),
                'model' => (string) ($data['model'] ?? ''),
                'question' => (string) ($data['question'] ?? ''),
                'answer' => (string) ($data['answer'] ?? ''),
                'ip_hash' => (string) ($data['ip_hash'] ?? ''),
                'tokens_prompt' => (int) ($data['tokens_prompt'] ?? 0),
                'tokens_completion' => (int) ($data['tokens_completion'] ?? 0),
                'latency_ms' => (int) ($data['latency_ms'] ?? 0),
                'error_message' => (string) ($data['error_message'] ?? ''),
                'created_at' => current_time('mysql', true),
            ],
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    /** @return list<object> */
    public function latest(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        global $wpdb;

        $offset = max(0, ($page - 1) * $perPage);
        $table = $this->table();

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';

            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table} WHERE question LIKE %s OR answer LIKE %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
                    $like,
                    $like,
                    $perPage,
                    $offset
                )
            ) ?: [];
        }

        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d", $perPage, $offset)
        ) ?: [];
    }

    public function count(string $search = ''): int
    {
        global $wpdb;

        $table = $this->table();

        if ($search !== '') {
            $like = '%' . $wpdb->esc_like($search) . '%';

            return (int) $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE question LIKE %s OR answer LIKE %s", $like, $like)
            );
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    /** @return list<object> */
    public function exportRows(): array
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM {$this->table()} ORDER BY created_at DESC LIMIT 10000") ?: []; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    public function deleteByVisitorId(string $visitorId): int
    {
        global $wpdb;

        return (int) $wpdb->delete($this->table(), ['visitor_id' => $visitorId], ['%s']);
    }

    public function pruneByRetentionSetting(): void
    {
        $settings = new Settings();
        $days = $settings->retentionDays();

        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$this->table()} WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
                $days
            )
        );

        $this->anonymizeOldIpHashes();
    }

    public function anonymizeOldIpHashes(): void
    {
        $settings = new Settings();

        if ($settings->anonymizationMode() !== 'delayed') {
            return;
        }

        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->table()} SET ip_hash = '' WHERE created_at < DATE_SUB(UTC_TIMESTAMP(), INTERVAL %d DAY)",
                $settings->retentionDays()
            )
        );
    }

    private function table(): string
    {
        global $wpdb;

        return $wpdb->prefix . 'ai_assistant_logs';
    }
}
