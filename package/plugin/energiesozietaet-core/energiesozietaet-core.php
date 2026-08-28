<?php
/**
 * Plugin Name:       Energiesozietät Core
 * Plugin URI:        https://energiesozietaet.de/
 * Description:       Custom Post Types (Team, Einzelleistungen, Karriere, Veranstaltungen, News, Publikationen), Taxonomie Beratungsfeld, Grid-Shortcodes, Meta-Felder, Zweisprachigkeit (DE/EN) und Content-Importer für die Energiesozietät-Website.
 * Version:           1.6.30
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
require_once ESC_DIR . 'inc/lang.php';
require_once ESC_DIR . 'inc/lang-import.php';
require_once ESC_DIR . 'inc/lang-xlsx.php';
require_once ESC_DIR . 'inc/page-blueprints.php';
require_once ESC_DIR . 'inc/upgrades.php';
require_once ESC_DIR . 'inc/importer.php';
require_once ESC_DIR . 'inc/admin.php';
require_once ESC_DIR . 'inc/theme-options.php';
require_once ESC_DIR . 'inc/colors-settings.php';
require_once ESC_DIR . 'inc/footer-settings.php';
require_once ESC_DIR . 'inc/karriere-settings.php';
require_once ESC_DIR . 'inc/typography-settings.php';
require_once ESC_DIR . 'inc/elementor-globals.php';
require_once ESC_DIR . 'inc/layout-settings.php';
require_once ESC_DIR . 'inc/contact-form.php';
require_once ESC_DIR . 'inc/linkedin.php';
require_once ESC_DIR . 'inc/team-links.php';

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
 * Grid-/Card-Styling kommt vollständig aus dem Theme (style.css) – die alte
 * Plugin-CSS-Schicht assets/css/grid.css wird NICHT mehr geladen, da sie mit
 * dem ah5-Re-Skin kollidierte (eigene .esc-grid/.esc-card/.esc-team-card-Regeln,
 * z.B. aspect-ratio 1/1 + auto-fill-Spalten → Layout-Konflikte). Datei bleibt
 * für ältere Installs/Referenz erhalten, wird aber nicht enqueued.
 */

