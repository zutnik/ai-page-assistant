<?php

declare(strict_types=1);

namespace AiPageAssistant\Frontend;

use AiPageAssistant\Support\Settings;

final class ChatWidget
{
    public function __construct(private readonly Settings $settings)
    {
    }

    public function render(): void
    {
        if (is_admin() || is_feed() || wp_is_json_request()) {
            return;
        }

        ?>
        <div
            id="ai-page-assistant"
            class="ai-pa ai-pa--<?php echo esc_attr((string) $this->settings->get('widget_position', 'bottom-right')); ?>"
            data-page-id="<?php echo esc_attr((string) get_queried_object_id()); ?>"
        ></div>
        <?php
    }
}
