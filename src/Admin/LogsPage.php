<?php

declare(strict_types=1);

namespace AiPageAssistant\Admin;

use AiPageAssistant\Repository\LogRepository;
use AiPageAssistant\Support\Sanitizer;
use AiPageAssistant\Support\Settings;

final class LogsPage
{
    public function __construct(
        private readonly LogRepository $logs,
        private readonly Settings $settings
    ) {
    }

    public function register(): void
    {
        add_management_page(
            __('AI Logs', 'ai-page-assistant'),
            __('AI Logs', 'ai-page-assistant'),
            'manage_options',
            'ai-page-assistant-logs',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['ai_pa_export']) && check_admin_referer('ai_pa_export_logs')) {
            $this->export();
            return;
        }

        $page = Sanitizer::int($_GET['paged'] ?? 1, 1, 99999);
        $search = Sanitizer::text($_GET['s'] ?? '', 120);
        $perPage = 20;
        $rows = $this->logs->latest($page, $perPage, $search);
        $total = $this->logs->count($search);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $settings = $this->settings;

        require AI_PAGE_ASSISTANT_PATH . 'src/Admin/views/logs.php';
    }

    private function export(): void
    {
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ai-assistant-logs.csv');

        $out = fopen('php://output', 'w');

        if ($out === false) {
            return;
        }

        fputcsv($out, ['id', 'created_at', 'visitor_id', 'page_id', 'language', 'model', 'question', 'answer', 'error']);

        foreach ($this->logs->exportRows() as $row) {
            fputcsv($out, [
                $row->id,
                $row->created_at,
                $row->visitor_id,
                $row->page_id,
                $row->visitor_language,
                $row->model,
                $row->question,
                $row->answer,
                $row->error_message,
            ]);
        }

        fclose($out);
        exit;
    }
}
