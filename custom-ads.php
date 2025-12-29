<?php
/*
Plugin Name: PS BeitragsAds
Plugin URI: https://cp-psource.github.io/ps-post-ads/
Description: Definiere benutzerdefinierte Werbeanzeigen für Beitragstypen und mehr, das einfachste Werkzeug um effektiv Werbeanzeigen zu schalten.
Version: 1.0.0
Author: PSOURCE
Author URI: https://github.com/Power-Source
Text Domain: wdca


Copyright 2020-2025 PSOURCE (https://github.com/Power-Source)
Author - DerN3rd (PSOURCE)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
// PS Update Manager - Hinweis wenn nicht installiert
add_action( 'admin_notices', function() {
    // Prüfe ob Update Manager aktiv ist
    if ( ! function_exists( 'ps_register_product' ) && current_user_can( 'install_plugins' ) ) {
        $screen = get_current_screen();
        if ( $screen && in_array( $screen->id, array( 'plugins', 'plugins-network' ) ) ) {
            // Prüfe ob bereits installiert aber inaktiv
            $plugin_file = 'ps-update-manager/ps-update-manager.php';
            $all_plugins = get_plugins();
            $is_installed = isset( $all_plugins[ $plugin_file ] );
            
            echo '<div class="notice notice-warning is-dismissible"><p>';
            echo '<strong>PSOURCE MANAGER:</strong> ';
            
            if ( $is_installed ) {
                // Installiert aber inaktiv - Aktivierungs-Link
                $activate_url = wp_nonce_url(
                    admin_url( 'plugins.php?action=activate&plugin=' . urlencode( $plugin_file ) ),
                    'activate-plugin_' . $plugin_file
                );
                echo sprintf(
                    __( 'Aktiviere den <a href="%s">PS Update Manager</a> für automatische Updates von GitHub.', 'psource-chat' ),
                    esc_url( $activate_url )
                );
            } else {
                // Nicht installiert - Download-Link
                echo sprintf(
                    __( 'Installiere den <a href="%s" target="_blank">PS Update Manager</a> für automatische Updates aller PSource Plugins & Themes.', 'psource-chat' ),
                    'https://github.com/Power-Source/ps-update-manager/releases/latest'
                );
            }
            
            echo '</p></div>';
        }
    }
});

define ('WDCA_PLUGIN_SELF_DIRNAME', basename(dirname(__FILE__)));
define('WDCA_PROTOCOL', (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on' ? 'https://' : 'http://')); // Protocol check

//Setup proper paths/URLs and load text domains
if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDCA_PLUGIN_LOCATION', 'mu-plugins');
	define ('WDCA_PLUGIN_BASE_DIR', WPMU_PLUGIN_DIR);
	define ('WDCA_PLUGIN_URL', str_replace('http://', WDCA_PROTOCOL, WPMU_PLUGIN_URL));
	$textdomain_handler = 'load_muplugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . WDCA_PLUGIN_SELF_DIRNAME . '/' . basename(__FILE__))) {
	define ('WDCA_PLUGIN_LOCATION', 'subfolder-plugins');
	define ('WDCA_PLUGIN_BASE_DIR', WP_PLUGIN_DIR . '/' . WDCA_PLUGIN_SELF_DIRNAME);
	define ('WDCA_PLUGIN_URL', str_replace('http://', WDCA_PROTOCOL, WP_PLUGIN_URL) . '/' . WDCA_PLUGIN_SELF_DIRNAME);
	$textdomain_handler = 'load_plugin_textdomain';
} else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
	define ('WDCA_PLUGIN_LOCATION', 'plugins');
	define ('WDCA_PLUGIN_BASE_DIR', WP_PLUGIN_DIR,);
	define ('WDCA_PLUGIN_URL', str_replace('http://', WDCA_PROTOCOL, WP_PLUGIN_URL));
	$textdomain_handler = 'load_plugin_textdomain';
} else {
	// No textdomain is loaded because we can't determine the plugin location.
	// No point in trying to add textdomain to string and/or localizing it.
	wp_die(__('Es gab ein Problem beim Bestimmen, wo das Plugin "PS BeitragsAds" installiert ist. Bitte erneut installieren.'));
}
$textdomain_handler('wdca', false, WDCA_PLUGIN_SELF_DIRNAME . '/languages/');


require_once WDCA_PLUGIN_BASE_DIR . '/lib/class_wdca_data.php';
require_once WDCA_PLUGIN_BASE_DIR . '/lib/class_wdca_codec.php';
require_once WDCA_PLUGIN_BASE_DIR . '/lib/class_wdca_custom_ad.php';

function wdca__init () {
	Wdca_CustomAd::init();

	if (is_admin()) {
		require_once WDCA_PLUGIN_BASE_DIR . '/lib/class_wdca_admin_form_renderer.php';
		require_once WDCA_PLUGIN_BASE_DIR . '/lib/class_wdca_admin_pages.php';
		Wdca_AdminPages::serve();
	} else {
		require_once WDCA_PLUGIN_BASE_DIR . '/lib/class_wdca_public_pages.php';
		Wdca_PublicPages::serve();
	}
}
add_action('init', 'wdca__init', 0); // Make sure we're as early with this as possible.