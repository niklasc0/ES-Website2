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
		self::rename_leistungen_swap();
		self::accordion_fields();
		self::vorteile_field();
	}

	/**
	 * Korrektur der UB-Kachel-Zuordnung: Die Titel "Investition und
	 * Transformation" und "Strukturen und Governance" werden zwischen den
	 * beiden Einträgen getauscht (passt besser zu den Texten), das "&" wird
	 * durch "und" ersetzt. Identifikation über die alten Slugs, damit die
	 * Migration unabhängig vom Zwischenstand funktioniert.
	 */
	protected static function rename_leistungen_swap() {
		if ( get_option( 'esc_rename_leistungen_v2' ) ) { return; }
		$find_by_old_slug = function ( $old ) {
			$posts = get_posts( array(
				'post_type' => 'es_einzelleistung', 'post_status' => 'any', 'numberposts' => 1,
				'meta_key' => '_wp_old_slug', 'meta_value' => $old,
			) );
			return $posts ? $posts[0] : null;
		};
		$a = $find_by_old_slug( 'erneuerbare-energien' );  // bisher: Investition und Transformation
		$b = $find_by_old_slug( 'projektmanagement' );     // bisher: Strukturen & Governance
		$done = 'nichts zu tun';
		if ( $a && $b && 'investition-und-transformation' === $a->post_name ) {
			wp_update_post( wp_slash( array( 'ID' => $a->ID, 'post_name' => 'strukturen-und-governance', 'post_title' => 'Strukturen und Governance' ) ) );
			wp_update_post( wp_slash( array( 'ID' => $b->ID, 'post_name' => 'investition-und-transformation', 'post_title' => 'Investition und Transformation' ) ) );
			add_post_meta( $b->ID, '_wp_old_slug', 'strukturen-governance' );
			flush_rewrite_rules();
			$done = 'getauscht';
		}
		update_option( 'esc_rename_leistungen_v2', $done . ' am ' . current_time( 'mysql' ) );
	}

	/**
	 * Standardisierung "Vorteile": Listen im Fließtext, die unter einer
	 * "Vorteile"/"Ihre Vorteile"-Zeile stehen, in das Vorteile-Feld
	 * (es_bullets) überführen; die Zeile selbst entfällt, das Frontend rendert
	 * die standardisierte grüne Überschrift. Redundante
	 * "Unsere Schwerpunkte und Kernkompetenzen…"-Zeilen entfallen ebenfalls,
	 * weil über den Rubriken jetzt die Standard-Überschrift
	 * "Unsere Leistungen zum Thema …" steht.
	 */
	protected static function vorteile_field() {
		if ( get_option( 'esc_vorteile_v1' ) ) { return; }
		$done = array();
		$pat  = '/<p>\s*(?:Ihre\s+)?Vorteile:?\s*<\/p>\s*<ul[^>]*>(.*?)<\/ul>/isu';
		foreach ( get_posts( array( 'post_type' => 'es_einzelleistung', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
			$c = $p->post_content;
			$changed = false;
			if ( ! get_post_meta( $p->ID, 'es_bullets', true ) && preg_match( $pat, $c, $m ) ) {
				preg_match_all( '/<li[^>]*>(.*?)<\/li>/isu', $m[1], $lis );
				$bullets = array();
				foreach ( $lis[1] as $li ) {
					$li = trim( wp_kses_post( $li ) );
					if ( '' !== $li ) { $bullets[] = $li; }
				}
				if ( $bullets ) {
					update_post_meta( $p->ID, 'es_bullets', $bullets );
					$c = preg_replace( $pat, '', $c, 1 );
					$changed = true;
				}
			}
			$c2 = preg_replace( '/<p>\s*Unsere Schwerpunkte und Kernkompetenzen[^<]*<\/p>/iu', '', $c );
			if ( $c2 !== $c && get_post_meta( $p->ID, 'es_accordion', true ) ) { $c = $c2; $changed = true; }
			if ( $changed ) {
				wp_update_post( wp_slash( array( 'ID' => $p->ID, 'post_content' => trim( $c ) ) ) );
				$done[] = $p->post_name;
			}
		}
		update_option( 'esc_vorteile_v1', implode( ', ', $done ) . ' am ' . current_time( 'mysql' ) );
	}

	/**
	 * Bestehende Aufklapp-Strukturen (drei oder mehr h3-Zwischenüberschriften
	 * im Inhalt) in das strukturierte Rubriken-Feld überführen: Der Einleitungs-
	 * text bleibt im Editor, die Rubriken wandern nach es_accordion (bzw.
	 * es_accordion_en für bereits übersetzte Inhalte).
	 */
	protected static function accordion_fields() {
		if ( get_option( 'esc_accordion_fields_v1' ) ) { return; }
		$done = 0;
		foreach ( get_posts( array( 'post_type' => 'es_einzelleistung', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
			// DE: post_content
			if ( ! get_post_meta( $p->ID, 'es_accordion', true ) ) {
				$split = self::split_h3( $p->post_content );
				if ( $split ) {
					update_post_meta( $p->ID, 'es_accordion', $split['rows'] );
					wp_update_post( wp_slash( array( 'ID' => $p->ID, 'post_content' => $split['intro'] ) ) );
					$done++;
				}
			}
			// EN: es_content_en
			if ( ! get_post_meta( $p->ID, 'es_accordion_en', true ) ) {
				$en = (string) get_post_meta( $p->ID, 'es_content_en', true );
				$split = self::split_h3( $en );
				if ( $split ) {
					update_post_meta( $p->ID, 'es_accordion_en', $split['rows'] );
					update_post_meta( $p->ID, 'es_content_en', $split['intro'] );
				}
			}
		}
		update_option( 'esc_accordion_fields_v1', $done . ' überführt am ' . current_time( 'mysql' ) );
	}

	/**
	 * Zerlegt HTML mit mindestens drei h3-Überschriften in Einleitung +
	 * Rubriken (Spiegel der bisherigen Frontend-Automatik es_accordionize,
	 * inklusive der Ausnahme für "Kernkompetenz"-Überschriften).
	 */
	protected static function split_h3( $html ) {
		if ( substr_count( (string) $html, '<h3' ) < 3 ) { return null; }
		$parts = preg_split( '/(<h3[^>]*>.*?<\/h3>)/s', (string) $html, -1, PREG_SPLIT_DELIM_CAPTURE );
		$intro = $parts[0];
		$rows = array();
		for ( $i = 1; $i < count( $parts ); $i += 2 ) {
			$heading = trim( wp_strip_all_tags( $parts[ $i ] ) );
			$body    = isset( $parts[ $i + 1 ] ) ? trim( $parts[ $i + 1 ] ) : '';
			if ( false !== stripos( $heading, 'Kernkompetenz' ) ) {
				$intro .= $parts[ $i ] . $body;
				continue;
			}
			$rows[] = array( 'title' => $heading, 'content' => $body );
		}
		if ( count( $rows ) < 3 ) { return null; }
		return array( 'intro' => trim( $intro ), 'rows' => $rows );
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
