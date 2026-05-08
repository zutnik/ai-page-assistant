<?php
/**
 * Plugin Name: AI Page Assistant
 * Plugin URI: https://github.com/zutnik/ai-page-assistant
 * Description: Floating AI chat widget that answers visitor questions using the current WordPress page as context via OpenRouter.
 * Version: 0.1.0
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Author: AI Page Assistant contributors
 * License: MIT
 * License URI: https://opensource.org/license/mit
 * Text Domain: ai-page-assistant
 */

declare(strict_types=1);

use AiPageAssistant\Activator;
use AiPageAssistant\Plugin;

if (! defined('ABSPATH')) {
    exit;
}

define('AI_PAGE_ASSISTANT_VERSION', '0.1.0');
define('AI_PAGE_ASSISTANT_FILE', __FILE__);
define('AI_PAGE_ASSISTANT_PATH', plugin_dir_path(__FILE__));
define('AI_PAGE_ASSISTANT_URL', plugin_dir_url(__FILE__));

$autoload = AI_PAGE_ASSISTANT_PATH . 'vendor/autoload.php';

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'AiPageAssistant\\';

        if (! str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = AI_PAGE_ASSISTANT_PATH . 'src/' . str_replace('\\', '/', $relative) . '.php';

        if (is_readable($file)) {
            require_once $file;
        }
    });
}

register_activation_hook(__FILE__, [Activator::class, 'activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'deactivate']);

add_action('plugins_loaded', static function (): void {
    Plugin::instance()->boot();
});
