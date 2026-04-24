<?php
/**
 * Plugin Name:       Energiesozietät Core
 * Plugin URI:        https://energiesozietaet.de/
 * Description:       Custom Post Types (Team, Einzelleistungen, Karriere, Veranstaltungen, News, Publikationen), Taxonomie Beratungsfeld, Grid-Shortcodes, Meta-Felder und Demo-Content-Importer für die Energiesozietät-Website.
 * Version:           1.0.0
 * Author:            Energiesozietät
 * License:           GPL v2 or later
 * Text Domain:       energiesozietaet-core
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * @package Energiesozietaet_Core
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'ESC_VERSION', '1.0.0' );
define( 'ESC_DIR', plugin_dir_path( __FILE__ ) );
define( 'ESC_URL', plugin_dir_url( __FILE__ ) );
define( 'ESC_FILE', __FILE__ );

require_once ESC_DIR . 'inc/cpts.php';
require_once ESC_DIR . 'inc/meta-boxes.php';
require_once ESC_DIR . 'inc/shortcodes.php';
require_once ESC_DIR . 'inc/elementor-widgets.php';
require_once ESC_DIR . 'inc/elementor-builder.php';
require_once ESC_DIR . 'inc/page-blueprints.php';
require_once ESC_DIR . 'inc/importer.php';
require_once ESC_DIR . 'inc/admin.php';
require_once ESC_DIR . 'inc/footer-settings.php';
require_once ESC_DIR . 'inc/karriere-settings.php';
require_once ESC_DIR . 'inc/typography-settings.php';

/**
 * Activation: flush rewrite rules after CPTs registered.
 */
register_activation_hook( __FILE__, function () {
	ESC_CPTs::register();
	flush_rewrite_rules();
	// Mark importer as available for one-click trigger (does NOT auto-import, to avoid
	// surprises when activating on a populated site).
	if ( false === get_option( 'esc_importer_ready' ) ) {
		update_option( 'esc_importer_ready', 1 );
	}
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

/**
 * Load CSS for grid rendering on front-end.
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'esc-grid', ESC_URL . 'assets/css/grid.css', array(), ESC_VERSION );
} );
