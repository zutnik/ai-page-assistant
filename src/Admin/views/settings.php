<?php
/**
 * @var array<string, mixed> $settings
 * @var array<string, string> $models
 * @var array<string, WP_Post_Type> $postTypes
 */

if (! defined('ABSPATH')) {
    exit;
}

$enabledPostTypes = is_array($settings['enabled_post_types'] ?? null) ? $settings['enabled_post_types'] : [];
?>
<div class="wrap ai-assistant-admin">
    <h1><?php esc_html_e('AI Page Assistant', 'ai-page-assistant'); ?></h1>
    <p><?php esc_html_e('Configure OpenRouter, context-aware answers, rate limits and GDPR behaviour.', 'ai-page-assistant'); ?></p>

    <form method="post" action="options.php">
        <?php settings_fields('ai_page_assistant'); ?>

        <h2><?php esc_html_e('OpenRouter', 'ai-page-assistant'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="ai-api-key"><?php esc_html_e('API Key', 'ai-page-assistant'); ?></label></th>
                <td>
                    <input
                        id="ai-api-key"
                        class="regular-text"
                        type="password"
                        name="ai_page_assistant_settings[api_key]"
                        value="<?php echo esc_attr((string) ($settings['api_key'] ?? '')); ?>"
                        autocomplete="off"
                    >
                    <p class="description"><?php esc_html_e('Stored in the WordPress options table. Use an OpenRouter key with a small monthly budget limit.', 'ai-page-assistant'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-model"><?php esc_html_e('Model', 'ai-page-assistant'); ?></label></th>
                <td>
                    <select id="ai-model" name="ai_page_assistant_settings[model]">
                        <?php foreach ($models as $value => $label) : ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected((string) ($settings['model'] ?? ''), $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-system-prompt"><?php esc_html_e('System Prompt', 'ai-page-assistant'); ?></label></th>
                <td>
                    <textarea id="ai-system-prompt" class="large-text" rows="6" name="ai_page_assistant_settings[system_prompt]"><?php echo esc_textarea((string) ($settings['system_prompt'] ?? '')); ?></textarea>
                    <p class="description"><?php esc_html_e('Optional. The plugin adds page context and language rules automatically.', 'ai-page-assistant'); ?></p>
                </td>
            </tr>
        </table>

        <h2><?php esc_html_e('Widget', 'ai-page-assistant'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Post Types', 'ai-page-assistant'); ?></th>
                <td>
                    <?php foreach ($postTypes as $type => $object) : ?>
                        <label>
                            <input type="checkbox" name="ai_page_assistant_settings[enabled_post_types][]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $enabledPostTypes, true)); ?>>
                            <?php echo esc_html($object->labels->singular_name); ?>
                        </label><br>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-greeting"><?php esc_html_e('Greeting', 'ai-page-assistant'); ?></label></th>
                <td><input id="ai-greeting" class="regular-text" type="text" name="ai_page_assistant_settings[greeting]" value="<?php echo esc_attr((string) ($settings['greeting'] ?? '')); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-primary-color"><?php esc_html_e('Primary Color', 'ai-page-assistant'); ?></label></th>
                <td><input id="ai-primary-color" type="color" name="ai_page_assistant_settings[primary_color]" value="<?php echo esc_attr((string) ($settings['primary_color'] ?? '#2563eb')); ?>"></td>
            </tr>
        </table>

        <h2><?php esc_html_e('Cost Protection', 'ai-page-assistant'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="ai-hourly-limit"><?php esc_html_e('Hourly Limit per IP', 'ai-page-assistant'); ?></label></th>
                <td><input id="ai-hourly-limit" type="number" min="1" max="500" name="ai_page_assistant_settings[hourly_limit]" value="<?php echo esc_attr((string) ($settings['hourly_limit'] ?? 20)); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-daily-limit"><?php esc_html_e('Daily Limit per IP', 'ai-page-assistant'); ?></label></th>
                <td><input id="ai-daily-limit" type="number" min="1" max="5000" name="ai_page_assistant_settings[daily_limit]" value="<?php echo esc_attr((string) ($settings['daily_limit'] ?? 100)); ?>"></td>
            </tr>
        </table>

        <h2><?php esc_html_e('GDPR', 'ai-page-assistant'); ?></h2>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><?php esc_html_e('Logging', 'ai-page-assistant'); ?></th>
                <td>
                    <label><input type="checkbox" name="ai_page_assistant_settings[store_logs]" value="1" <?php checked((bool) ($settings['store_logs'] ?? true)); ?>> <?php esc_html_e('Store conversation logs', 'ai-page-assistant'); ?></label><br>
                    <label><input type="checkbox" name="ai_page_assistant_settings[consent_required]" value="1" <?php checked((bool) ($settings['consent_required'] ?? true)); ?>> <?php esc_html_e('Require consent before first message', 'ai-page-assistant'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-retention"><?php esc_html_e('Retention Days', 'ai-page-assistant'); ?></label></th>
                <td><input id="ai-retention" type="number" min="1" max="365" name="ai_page_assistant_settings[retention_days]" value="<?php echo esc_attr((string) ($settings['retention_days'] ?? 30)); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ai-ip-anonymization"><?php esc_html_e('IP Anonymization', 'ai-page-assistant'); ?></label></th>
                <td>
                    <select id="ai-ip-anonymization" name="ai_page_assistant_settings[ip_anonymization]">
                        <option value="immediate" <?php selected((string) ($settings['ip_anonymization'] ?? ''), 'immediate'); ?>><?php esc_html_e('Immediate', 'ai-page-assistant'); ?></option>
                        <option value="delayed" <?php selected((string) ($settings['ip_anonymization'] ?? ''), 'delayed'); ?>><?php esc_html_e('Delayed by retention job', 'ai-page-assistant'); ?></option>
                        <option value="never" <?php selected((string) ($settings['ip_anonymization'] ?? ''), 'never'); ?>><?php esc_html_e('Never', 'ai-page-assistant'); ?></option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
