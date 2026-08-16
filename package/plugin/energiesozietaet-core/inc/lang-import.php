<?php
/**
 * Einspielen der Kundenübersetzungen (EN) aus data/translations-en.json.
 *
 * Die JSON-Datei wird aus der ausgefüllten Übersetzungsvorlage (XLSX)
 * generiert und enthält Zeilen { sheet, ref, en } bzw. für URL-Kürzel
 * { sheet, typ, de, en }. Referenzformate:
 *   live:<seiten-slug>:<widget-id>:<feld>  → Elementor-Text der EN-Seitenkopie
 *   einzelleistung|team|karriere.<slug>.<Feldlabel> → CPT-Meta (_en)
 *   settings.<Bereich>.<key>               → Options-Feld {key}_en
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ES_Lang_Import {

	/** Feldlabel (Vorlage) => Meta-Key-Logik je CPT-Blatt. */
	const FIELD_MAP = array(
		'einzelleistung' => array(
			'Titel' => 'es_title_en', 'Untertitel' => 'es_subtitle_en', 'Beschreibung' => 'content',
			'Kernpunkte' => 'lines:es_bullets_en', 'Abschluss-Absatz' => 'es_closing_en',
		),
		'team' => array(
			'Rolle/Position' => 'es_role_en', 'Kurzvita' => 'content', 'Erweiterte Vita' => 'es_more_bio_en',
			'Schwerpunkte' => 'lines:es_focus_areas_en', 'Werdegang' => 'career:es_career_en',
		),
		'karriere' => array(
			'Titel' => 'es_title_en', 'Rollen-Kürzel' => 'es_department_en', 'Über die Rolle' => 'content',
			'Aufgaben' => 'lines:es_tasks_en', 'Profil' => 'lines:es_profile_en', 'Wir bieten' => 'lines:es_bullets_en',
			'Anstellungsart' => 'es_employment_type_en',
		),
	);

	const CPT_OF_SHEET = array( 'einzelleistung' => 'es_einzelleistung', 'team' => 'es_team', 'karriere' => 'es_karriere' );

	public static function apply_file() {
		$file = ESC_DIR . 'data/translations-en.json';
		if ( ! file_exists( $file ) ) { return new WP_Error( 'no_file', 'data/translations-en.json nicht gefunden.' ); }
		$rows = json_decode( (string) file_get_contents( $file ), true );
		if ( ! is_array( $rows ) ) { return new WP_Error( 'bad_file', 'translations-en.json fehlerhaft.' ); }

		$stats = array( 'pages' => 0, 'cpt' => 0, 'settings' => 0, 'slugs' => 0, 'skipped' => array() );
		$page_data = array(); // de_slug => decoded elementor array der EN-Kopie (gesammelt, einmal speichern)

		foreach ( $rows as $r ) {
			$en = isset( $r['en'] ) ? trim( (string) $r['en'] ) : '';
			if ( '' === $en ) { continue; }
			$ref = isset( $r['ref'] ) ? (string) $r['ref'] : '';

			if ( 0 === strpos( $ref, 'live:' ) ) {
				list( , $slug, $wid, $field ) = array_pad( explode( ':', $ref, 4 ), 4, '' );
				if ( ! isset( $page_data[ $slug ] ) ) {
					$en_id = self::en_copy_id( $slug );
					if ( ! $en_id ) { $stats['skipped'][] = $ref . ' (keine EN-Kopie)'; continue; }
					$page_data[ $slug ] = array( 'id' => $en_id, 'data' => json_decode( (string) get_post_meta( $en_id, '_elementor_data', true ), true ), 'hits' => 0 );
				}
				if ( ! is_array( $page_data[ $slug ]['data'] ) ) { $stats['skipped'][] = $ref . ' (keine Elementor-Daten)'; continue; }
				if ( self::set_widget_text( $page_data[ $slug ]['data'], $wid, $field, $en ) ) {
					$page_data[ $slug ]['hits']++;
					$stats['pages']++;
				} else {
					$stats['skipped'][] = $ref . ' (Widget nicht gefunden)';
				}
				continue;
			}

			if ( 0 === strpos( $ref, 'settings.' ) ) {
				list( , $area, $key ) = array_pad( explode( '.', $ref, 3 ), 3, '' );
				$opt = ( false !== stripos( $area, 'footer' ) ) ? 'esc_footer' : 'esc_karriere';
				$opts = (array) get_option( $opt, array() );
				$opts[ $key . '_en' ] = $en;
				update_option( $opt, $opts );
				$stats['settings']++;
				continue;
			}

			// CPT-Referenz: <blatt>.<slug>.<Feldlabel>
			if ( preg_match( '/^(einzelleistung|team|karriere)\.([^.]+)\.(.+)$/u', $ref, $m ) ) {
				$post = get_page_by_path( $m[2], OBJECT, self::CPT_OF_SHEET[ $m[1] ] );
				$map  = self::FIELD_MAP[ $m[1] ];
				if ( ! $post || ! isset( $map[ $m[3] ] ) ) { $stats['skipped'][] = $ref; continue; }
				self::set_cpt_field( $post->ID, $map[ $m[3] ], $en );
				$stats['cpt']++;
				continue;
			}

			// URL-Kürzel (Blatt ohne ref: typ/de/en)
			if ( isset( $r['typ'], $r['de'] ) ) {
				$en_slug = sanitize_title( $en );
				if ( 'Seite' === $r['typ'] ) {
					$en_id = self::en_copy_id( (string) $r['de'] );
					if ( $en_id ) { update_post_meta( $en_id, 'es_public_slug', $en_slug ); $stats['slugs']++; }
				} elseif ( 'Einzelleistung' === $r['typ'] || 'Stellenangebot' === $r['typ'] ) {
					$pt = ( 'Einzelleistung' === $r['typ'] ) ? 'es_einzelleistung' : 'es_karriere';
					$post = get_page_by_path( (string) $r['de'], OBJECT, $pt );
					if ( $post ) { update_post_meta( $post->ID, 'es_slug_en', $en_slug ); $stats['slugs']++; }
				} else {
					$stats['skipped'][] = 'URL-Basis ' . $r['de'] . ' (im Code zu ändern)';
				}
				continue;
			}

			$stats['skipped'][] = $ref ? $ref : wp_json_encode( $r );
		}

		// Geänderte Seitenkopien speichern + als übersetzt markieren
		foreach ( $page_data as $slug => $pd ) {
			if ( $pd['hits'] > 0 ) {
				update_post_meta( $pd['id'], '_elementor_data', wp_slash( wp_json_encode( $pd['data'], JSON_UNESCAPED_UNICODE ) ) );
				update_post_meta( $pd['id'], 'es_translated', 1 );
			}
		}
		if ( class_exists( '\Elementor\Plugin' ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_elementor_element_cache'" );
		flush_rewrite_rules();
		return $stats;
	}

	/** EN-Kopie einer DE-Seite (per DE-Slug). */
	protected static function en_copy_id( $de_slug ) {
		$de = get_page_by_path( $de_slug );
		if ( ! $de ) { return 0; }
		$partner = (int) get_post_meta( $de->ID, 'es_translation_of', true );
		return ( $partner && 'en' === get_post_meta( $partner, 'es_lang', true ) ) ? $partner : 0;
	}

	/** Text eines Widgets (per Elementor-ID) in einem Elementor-Baum setzen. */
	protected static function set_widget_text( &$els, $wid, $field, $value ) {
		foreach ( $els as &$el ) {
			if ( isset( $el['id'] ) && $el['id'] === $wid && isset( $el['settings'] ) ) {
				$el['settings'][ $field ] = $value;
				return true;
			}
			if ( ! empty( $el['elements'] ) && self::set_widget_text( $el['elements'], $wid, $field, $value ) ) {
				return true;
			}
		}
		return false;
	}

	protected static function set_cpt_field( $post_id, $target, $en ) {
		if ( 'content' === $target ) {
			update_post_meta( $post_id, 'es_content_en', wp_kses_post( $en ) );
			return;
		}
		if ( 0 === strpos( $target, 'lines:' ) ) {
			$key = substr( $target, 6 );
			$arr = array();
			foreach ( preg_split( '/\r?\n/', $en ) as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, $key, $arr );
			return;
		}
		if ( 0 === strpos( $target, 'career:' ) ) {
			$key = substr( $target, 7 );
			$career = array();
			foreach ( preg_split( '/\r?\n/', $en ) as $l ) {
				$l = trim( $l );
				if ( ! $l ) { continue; }
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				$career[] = array( 'when' => sanitize_text_field( $parts[0] ), 'what' => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '' );
			}
			update_post_meta( $post_id, $key, $career );
			return;
		}
		update_post_meta( $post_id, $target, sanitize_text_field( $en ) );
	}
}

/** Admin-Seite: Theme Options → EN-Import. */
add_action( 'admin_menu', function () {
	add_submenu_page( 'es-theme-options', 'EN-Import', 'EN-Import', 'manage_options', 'esc-lang-import', function () {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$result = null;
		if ( isset( $_POST['esc_lang_import_run'] ) && check_admin_referer( 'esc_lang_import' ) ) {
			$result = ES_Lang_Import::apply_file();
		}
		$file = ESC_DIR . 'data/translations-en.json';
		echo '<div class="wrap"><h1>Englische Übersetzungen einspielen</h1>';
		echo '<p>Spielt die Übersetzungsdatei <code>data/translations-en.json</code> (generiert aus der ausgefüllten Kundenvorlage) in die EN-Seitenkopien, Inhaltstypen und Einstellungen ein. Bereits eingespielte Werte werden aktualisiert; deutsche Inhalte bleiben unberührt.</p>';
		if ( $result ) {
			if ( is_wp_error( $result ) ) {
				echo '<div class="notice notice-error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="notice notice-success"><p>Eingespielt: ' . (int) $result['pages'] . ' Seiten-Texte, ' . (int) $result['cpt'] . ' Inhalts-Felder, ' . (int) $result['settings'] . ' Einstellungen, ' . (int) $result['slugs'] . ' URL-Kürzel.</p>';
				if ( ! empty( $result['skipped'] ) ) {
					echo '<p><strong>Übersprungen:</strong><br>' . esc_html( implode( ' · ', array_slice( $result['skipped'], 0, 20 ) ) ) . '</p>';
				}
				echo '</div>';
			}
		}
		if ( file_exists( $file ) ) {
			echo '<form method="post">';
			wp_nonce_field( 'esc_lang_import' );
			submit_button( 'Übersetzungen jetzt einspielen', 'primary', 'esc_lang_import_run' );
			echo '</form>';
		} else {
			echo '<p><em>Keine Übersetzungsdatei im Plugin enthalten – sie wird mit einem Plugin-Update geliefert, sobald die Kundenübersetzungen vorliegen.</em></p>';
		}
		echo '</div>';
	}, 45 );
} );
