<?php

/**
 * Plugin Name: Adless
 * Plugin URI: https://adless.net/get-started/wordpress
 * Description: Monetize easily with Adless
 * Version: 1.0
 * Author: Adless
 * Author URI: https://adless.net
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: adless
 */

$adless_opts = get_option('adless_options');

if (is_admin()) {
    require_once __DIR__ . '/admin/settings.php';
    $adless_settings_page = new AdlessSettingsPage();
} elseif(!empty($adless_opts['earnerID'])) {
    $opts = get_option('adless_options');
    wp_enqueue_script('adless', 'https://static.adless.net/adless.js');
    wp_localize_script('adless', 'adless_config', $opts);
    add_action('wp_footer', 'adless_initialize');
}

function adless_initialize()
{
?>
    <script>
        (window.adless = window.adless || []).push({
            earner: adless_config.earnerID,
            services: (adless_config.services || []),
            ...{
                paywall: !!adless_config.paywallMessage ? {
                    message: atob(adless_config.paywallMessage)
                } : {}
            },
            ...(adless_config.customConfiguration ? JSON.parse(adless_config.customConfiguration) : {})
        })
    </script>
<?php
}
?>
