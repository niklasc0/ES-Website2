<?php
/**
 * Einmalige Daten-Migrationen (option-gated, laufen bei Plugin-Update automatisch).
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Upgrades {

	public static function init() {
		// Nach der CPT-Registrierung ausführen.
		add_action( 'init', array( __CLASS__, 'run' ), 20 );
	}

	public static function run() {
		self::purge_cpt_elementor();
		self::rename_leistungen();
	}

	/**
	 * Elementor-Überlagerungen von Inhalts-Datensätzen entfernen. Auf dem
	 * Live-System wurden einzelne Einzelleistungen mit Elementor bearbeitet;
	 * die dabei entstandene Kopie übermalt die regulären Inhaltsfelder auch
	 * dann noch, wenn Elementor für diese Inhaltstypen deaktiviert ist.
	 * Die regulären Inhalte sind vorher per Import auf den bereinigten,
	 * aktuellen Stand gebracht worden; die Kopien sind also nur noch Altlast.
	 */
	protected static function purge_cpt_elementor() {
		if ( get_option( 'esc_purge_cpt_elementor_v1' ) ) { return; }
		$types = array( 'es_team', 'es_einzelleistung', 'es_karriere', 'es_news', 'es_veranstaltung', 'es_publikation', 'es_linkedin' );
		$metas = array( '_elementor_data', '_elementor_edit_mode', '_elementor_template_type', '_elementor_version', '_elementor_css', '_elementor_element_cache', '_elementor_controls_usage', '_elementor_page_assets' );
		$purged = 0;
		foreach ( get_posts( array( 'post_type' => $types, 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $pid ) {
			if ( ! get_post_meta( $pid, '_elementor_data', true ) ) { continue; }
			foreach ( $metas as $mk ) { delete_post_meta( $pid, $mk ); }
			$purged++;
		}
		if ( $purged && class_exists( '\Elementor\Plugin' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
		update_option( 'esc_purge_cpt_elementor_v1', $purged . ' bereinigt am ' . current_time( 'mysql' ) );
	}

	/**
	 * Umbenennung der Unternehmensberatungs-Leistungen und von Real Estate
	 * inklusive URL (Kundenwunsch). Alte URLs leiten über _wp_old_slug
	 * automatisch per 301 auf die neuen weiter. Muss VOR einem erneuten
	 * Import laufen, damit der Importer die Einträge unter dem neuen Slug
	 * wiederfindet (statt Duplikate anzulegen).
	 */
	protected static function rename_leistungen() {
		if ( get_option( 'esc_rename_leistungen_v1' ) ) { return; }
		$map = array(
			'erneuerbare-energien'        => array( 'investition-und-transformation', 'Investition und Transformation' ),
			'projektmanagement'           => array( 'strukturen-governance', 'Strukturen & Governance' ),
			'kooperation-und-transaktion' => array( 'transaktion-und-kooperation', 'Transaktion und Kooperation' ),
			'bau-und-planungsrecht'       => array( 'real-estate', 'Real Estate' ),
		);
		$done = array();
		foreach ( $map as $old => $target ) {
			list( $new, $title ) = $target;
			if ( get_page_by_path( $new, OBJECT, 'es_einzelleistung' ) ) { continue; } // schon umbenannt
			$p = get_page_by_path( $old, OBJECT, 'es_einzelleistung' );
			if ( ! $p ) { continue; }
			wp_update_post( wp_slash( array( 'ID' => $p->ID, 'post_name' => $new, 'post_title' => $title ) ) );
			add_post_meta( $p->ID, '_wp_old_slug', $old );
			$done[] = $old . ' zu ' . $new;
		}
		flush_rewrite_rules();
		update_option( 'esc_rename_leistungen_v1', implode( ', ', $done ) . ' am ' . current_time( 'mysql' ) );
	}
}
ESC_Upgrades::init();
