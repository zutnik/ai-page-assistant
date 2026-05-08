<?php
/**
 * Local development helpers for the Docker WordPress container.
 *
 * This file is mounted as an MU plugin, not shipped in release archives.
 */

declare(strict_types=1);

add_filter('wp_is_application_passwords_available', '__return_true');
