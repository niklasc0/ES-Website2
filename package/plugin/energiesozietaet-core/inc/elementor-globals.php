<?php
/**
 * Elementor Global Fonts – die native „globale Vorgabe" für Schriftfamilien.
 *
 * Setzt die Global-Fonts des aktiven Kits (Site Settings → Global Fonts) auf
 * die ah5-Schriften (Sora für Überschriften, Manrope für Text). Dadurch sind
 * die Schriften nativ in Elementor sichtbar und pro Widget auf „Global"
 * umstellbar. Läuft einmalig als versionierte Migration bei admin_init –
 * kein Demo-Import nötig. Ändert nichts an der Größen-Skala (die bleibt als
 * überschreibbarer Theme-Default) und überschreibt keine anderen Kit-Werte.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Elementor_Globals {

	const VER = 1;
	const OPT = 'esc_elementor_globals_ver';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'maybe_migrate' ) );
	}

	public static function maybe_migrate() {
		if ( (int) get_option( self::OPT, 0 ) >= self::VER ) { return; }
		if ( ! class_exists( '\Elementor\Plugin' ) ) { return; }
		self::apply();
		update_option( self::OPT, self::VER );
	}

	/** Global-Fonts in das aktive Kit schreiben (nur system_typography). */
	public static function apply() {
		$kit_id = (int) get_option( 'elementor_active_kit' );
		if ( ! $kit_id ) { return; }

		$settings = get_post_meta( $kit_id, '_elementor_page_settings', true );
		if ( ! is_array( $settings ) ) { $settings = array(); }

		$settings['system_typography'] = array(
			array( '_id' => 'primary',   'title' => 'Primary',   'typography_typography' => 'custom', 'typography_font_family' => 'Sora',    'typography_font_weight' => '400' ),
			array( '_id' => 'secondary', 'title' => 'Secondary', 'typography_typography' => 'custom', 'typography_font_family' => 'Sora',    'typography_font_weight' => '500' ),
			array( '_id' => 'text',      'title' => 'Text',      'typography_typography' => 'custom', 'typography_font_family' => 'Manrope', 'typography_font_weight' => '400' ),
			array( '_id' => 'accent',    'title' => 'Accent',    'typography_typography' => 'custom', 'typography_font_family' => 'Manrope', 'typography_font_weight' => '500' ),
		);

		update_post_meta( $kit_id, '_elementor_page_settings', $settings );

		// Elementor-CSS neu generieren, damit die Global-Fonts greifen.
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
}
ESC_Elementor_Globals::init();
