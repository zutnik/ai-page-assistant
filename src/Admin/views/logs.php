<?php
/**
 * @var list<object> $rows
 * @var int $page
 * @var int $totalPages
 * @var string $search
 * @var \AiPageAssistant\Support\Settings $settings
 */

if (! defined('ABSPATH')) {
    exit;
}

$exportUrl = wp_nonce_url(
    add_query_arg(['page' => 'ai-page-assistant-logs', 'ai_pa_export' => '1'], admin_url('tools.php')),
    'ai_pa_export_logs'
);
?>
<div class="wrap ai-assistant-admin">
    <h1><?php esc_html_e('AI Conversation Logs', 'ai-page-assistant'); ?></h1>

    <?php if (! $settings->storeLogs()) : ?>
        <div class="notice notice-warning"><p><?php esc_html_e('Conversation logging is currently disabled in settings.', 'ai-page-assistant'); ?></p></div>
    <?php endif; ?>

    <form method="get">
        <input type="hidden" name="page" value="ai-page-assistant-logs">
        <p class="search-box">
            <label class="screen-reader-text" for="ai-log-search"><?php esc_html_e('Search Logs', 'ai-page-assistant'); ?></label>
            <input id="ai-log-search" type="search" name="s" value="<?php echo esc_attr($search); ?>">
            <?php submit_button(__('Search Logs', 'ai-page-assistant'), '', '', false); ?>
        </p>
    </form>

    <p><a class="button" href="<?php echo esc_url($exportUrl); ?>"><?php esc_html_e('Export CSV', 'ai-page-assistant'); ?></a></p>

    <table class="widefat striped">
        <thead>
            <tr>
                <th><?php esc_html_e('Time', 'ai-page-assistant'); ?></th>
                <th><?php esc_html_e('Page', 'ai-page-assistant'); ?></th>
                <th><?php esc_html_e('Visitor', 'ai-page-assistant'); ?></th>
                <th><?php esc_html_e('Question', 'ai-page-assistant'); ?></th>
                <th><?php esc_html_e('Answer', 'ai-page-assistant'); ?></th>
                <th><?php esc_html_e('Model', 'ai-page-assistant'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rows === []) : ?>
                <tr><td colspan="6"><?php esc_html_e('No logs yet.', 'ai-page-assistant'); ?></td></tr>
            <?php endif; ?>

            <?php foreach ($rows as $row) : ?>
                <tr>
                    <td><?php echo esc_html((string) $row->created_at); ?></td>
                    <td>
                        <?php if (! empty($row->page_id)) : ?>
                            <a href="<?php echo esc_url(get_edit_post_link((int) $row->page_id)); ?>">#<?php echo esc_html((string) $row->page_id); ?></a>
                        <?php else : ?>
                            &mdash;
                        <?php endif; ?>
                    </td>
                    <td><code><?php echo esc_html((string) $row->visitor_id); ?></code><br><small><?php echo esc_html((string) $row->visitor_language); ?></small></td>
                    <td><?php echo esc_html(wp_trim_words((string) $row->question, 24)); ?></td>
                    <td>
                        <?php echo esc_html(wp_trim_words((string) $row->answer, 32)); ?>
                        <?php if (! empty($row->error_message)) : ?>
                            <br><small class="error"><?php echo esc_html((string) $row->error_message); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><code><?php echo esc_html((string) $row->model); ?></code></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="tablenav">
        <div class="tablenav-pages">
            <?php
            echo paginate_links([
                'base' => add_query_arg(['paged' => '%#%', 's' => rawurlencode($search)]),
                'format' => '',
                'current' => $page,
                'total' => $totalPages,
            ]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
    </div>
</div>
