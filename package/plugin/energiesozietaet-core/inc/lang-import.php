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
		return self::apply_rows( $rows );
	}

	/** Übersetzungszeilen ({ref, en} bzw. {typ, de, en}) anwenden. */
	public static function apply_rows( $rows ) {
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
					if ( ! $en_id ) {
						// Neue Seite ohne EN-Kopie: Kopie automatisch anlegen
						ES_Lang::create_page_copies();
						$en_id = self::en_copy_id( $slug );
					}
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

			if ( 0 === strpos( $ref, 'url:' ) ) {
				list( , $typ, $de ) = array_pad( explode( ':', $ref, 3 ), 3, '' );
				$r = array( 'typ' => $typ, 'de' => $de, 'en' => $en );
			}
			// URL-Kürzel (typ/de/en)
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

	// ---- Export: aktuelle DE/EN-Texte als Tabellenzeilen ----

	const TEXT_FIELDS = array( 'title' => 'Überschrift', 'editor' => 'Absatz', 'text' => 'Button/Text', 'button_text' => 'Button', 'eyebrow' => 'Kicker', 'caption' => 'Bildunterschrift', 'quote' => 'Zitat', 'author' => 'Zitatgeber', 'cite' => 'Quelle', 'headline' => 'Überschrift', 'item_text' => 'Listenpunkt', 'description_text' => 'Beschreibung' );

	protected static function collect_widget_texts( $els, &$out ) {
		foreach ( $els as $el ) {
			$wid = isset( $el['id'] ) ? $el['id'] : '';
			if ( isset( $el['settings'] ) && is_array( $el['settings'] ) ) {
				foreach ( self::TEXT_FIELDS as $k => $label ) {
					if ( empty( $el['settings'][ $k ] ) || ! is_string( $el['settings'][ $k ] ) ) { continue; }
					$plain = trim( wp_strip_all_tags( $el['settings'][ $k ] ) );
					if ( '' === $plain || preg_match( '#^(\[|https?://|/)#', $plain ) || ! preg_match( '/[A-Za-zÄÖÜäöüß]{2}/u', $plain ) ) { continue; }
					$out[ $wid . ':' . $k ] = array( 'kind' => $label, 'text' => trim( $el['settings'][ $k ] ) );
				}
			}
			if ( ! empty( $el['elements'] ) ) { self::collect_widget_texts( $el['elements'], $out ); }
		}
	}

	protected static function join_lines( $val, $career = false ) {
		if ( ! is_array( $val ) ) { return (string) $val; }
		$lines = array();
		foreach ( $val as $v ) {
			if ( $career && is_array( $v ) ) { $lines[] = trim( (string) ( $v['when'] ?? '' ) . ' ||| ' . (string) ( $v['what'] ?? '' ) ); }
			elseif ( is_array( $v ) ) { $lines[] = (string) ( $v['title'] ?? '' ); }
			else { $lines[] = (string) $v; }
		}
		return implode( "\n", array_filter( $lines ) );
	}

	/** Zeilen [Bereich, Feld, Deutsch, Englisch, Referenz] des aktuellen Stands. */
	public static function export_rows() {
		$rows = array( array( 'Bereich', 'Element / Feld', 'Deutsch (aktuell)', 'Englisch (bitte ausfüllen/ändern)', 'Referenz (nicht ändern)' ) );

		// Feste Seiten: alle veröffentlichten DE-Seiten mit Elementor-Inhalt
		$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC' ) );
		foreach ( $pages as $pg ) {
			if ( 'en' === get_post_meta( $pg->ID, 'es_lang', true ) ) { continue; }
			$de_data = json_decode( (string) get_post_meta( $pg->ID, '_elementor_data', true ), true );
			if ( ! is_array( $de_data ) ) { continue; }
			$de_texts = array();
			self::collect_widget_texts( $de_data, $de_texts );
			$en_texts = array();
			$en_id = self::en_copy_id( $pg->post_name );
			if ( $en_id ) {
				$en_data = json_decode( (string) get_post_meta( $en_id, '_elementor_data', true ), true );
				if ( is_array( $en_data ) ) { self::collect_widget_texts( $en_data, $en_texts ); }
			}
			foreach ( $de_texts as $key => $t ) {
				$en_val = ( isset( $en_texts[ $key ] ) && $en_texts[ $key ]['text'] !== $t['text'] ) ? $en_texts[ $key ]['text'] : '';
				$rows[] = array( 'Seite: ' . $pg->post_title, $t['kind'], $t['text'], $en_val, 'live:' . $pg->post_name . ':' . str_replace( ':', ':', $key ) );
			}
		}

		// Inhaltstypen
		$cpt_sources = array(
			'einzelleistung' => array( 'es_einzelleistung', array( 'Titel' => 'title', 'Untertitel' => 'meta:es_subtitle', 'Beschreibung' => 'content', 'Kernpunkte' => 'lines:es_bullets', 'Abschluss-Absatz' => 'meta:es_closing' ) ),
			'team'           => array( 'es_team', array( 'Rolle/Position' => 'meta:es_role', 'Kurzvita' => 'content', 'Erweiterte Vita' => 'meta:es_more_bio', 'Schwerpunkte' => 'lines:es_focus_areas', 'Werdegang' => 'career:es_career' ) ),
			'karriere'       => array( 'es_karriere', array( 'Titel' => 'title', 'Rollen-Kürzel' => 'meta:es_department', 'Über die Rolle' => 'content', 'Aufgaben' => 'lines:es_tasks', 'Profil' => 'lines:es_profile', 'Wir bieten' => 'lines:es_bullets', 'Anstellungsart' => 'meta:es_employment_type' ) ),
		);
		$area_names = array( 'einzelleistung' => 'Einzelleistung', 'team' => 'Team', 'karriere' => 'Stelle' );
		foreach ( $cpt_sources as $sheet => $cfg ) {
			list( $pt, $fields ) = $cfg;
			$posts = get_posts( array( 'post_type' => $pt, 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
			foreach ( $posts as $po ) {
				foreach ( $fields as $label => $src ) {
					if ( 'title' === $src ) { $de = $po->post_title; }
					elseif ( 'content' === $src ) { $de = trim( wp_strip_all_tags( $po->post_content ) ) ? $po->post_content : ''; }
					elseif ( 0 === strpos( $src, 'meta:' ) ) { $de = (string) get_post_meta( $po->ID, substr( $src, 5 ), true ); }
					elseif ( 0 === strpos( $src, 'lines:' ) ) { $de = self::join_lines( get_post_meta( $po->ID, substr( $src, 6 ), true ) ); }
					else { $de = self::join_lines( get_post_meta( $po->ID, substr( $src, 7 ), true ), true ); }
					if ( '' === trim( (string) $de ) ) { continue; }
					// Vorhandene EN-Fassung über die Import-Zuordnung ermitteln
					$target = self::FIELD_MAP[ $sheet ][ $label ];
					if ( 'content' === $target ) { $en = (string) get_post_meta( $po->ID, 'es_content_en', true ); }
					elseif ( 0 === strpos( $target, 'lines:' ) ) { $en = self::join_lines( get_post_meta( $po->ID, substr( $target, 6 ), true ) ); }
					elseif ( 0 === strpos( $target, 'career:' ) ) { $en = self::join_lines( get_post_meta( $po->ID, substr( $target, 7 ), true ), true ); }
					else { $en = (string) get_post_meta( $po->ID, $target, true ); }
					$rows[] = array( $area_names[ $sheet ] . ': ' . $po->post_title, $label, $de, $en, $sheet . '.' . $po->post_name . '.' . $label );
				}
			}
		}

		// Zentrale Einstellungen
		foreach ( array( 'Karriere-Seiten' => array( 'esc_karriere', 'ESC_Karriere_Settings' ), 'Footer' => array( 'esc_footer', 'ESC_Footer_Settings' ) ) as $area => $cfg ) {
			list( $opt, $cls ) = $cfg;
			if ( ! class_exists( $cls ) ) { continue; }
			$opts = array_merge( $cls::defaults(), (array) get_option( $opt, array() ) );
			foreach ( $cls::en_keys() as $key ) {
				$de = isset( $opts[ $key ] ) ? (string) $opts[ $key ] : '';
				if ( '' === trim( $de ) ) { continue; }
				$en = isset( $opts[ $key . '_en' ] ) ? (string) $opts[ $key . '_en' ] : '';
				$rows[] = array( 'Einstellungen: ' . $area, $key, $de, $en, 'settings.' . $area . '.' . $key );
			}
		}

		// URL-Kürzel
		foreach ( $pages as $pg ) {
			if ( 'en' === get_post_meta( $pg->ID, 'es_lang', true ) ) { continue; }
			$en_id = self::en_copy_id( $pg->post_name );
			if ( ! $en_id ) { continue; }
			$rows[] = array( 'URL-Kürzel', 'Seite', $pg->post_name, (string) get_post_meta( $en_id, 'es_public_slug', true ), 'url:Seite:' . $pg->post_name );
		}
		foreach ( array( 'Einzelleistung' => 'es_einzelleistung', 'Stellenangebot' => 'es_karriere' ) as $typ => $pt ) {
			foreach ( get_posts( array( 'post_type' => $pt, 'post_status' => 'publish', 'numberposts' => -1 ) ) as $po ) {
				$rows[] = array( 'URL-Kürzel', $typ, $po->post_name, (string) get_post_meta( $po->ID, 'es_slug_en', true ), 'url:' . $typ . ':' . $po->post_name );
			}
		}
		return $rows;
	}

	/** Hochgeladene XLSX-Zeilen (Spalten 0-4) in {ref, en}-Zeilen wandeln. */
	public static function rows_from_sheet( $sheet_rows ) {
		$out = array();
		$known_ref = 0;
		foreach ( $sheet_rows as $cells ) {
			$ref = isset( $cells[4] ) ? trim( (string) $cells[4] ) : '';
			$en  = isset( $cells[3] ) ? trim( (string) $cells[3] ) : '';
			// Kopf-/Leer-/Fremdzeilen anhand des Inhalts erkennen – die Position
			// im Blatt ist egal (eingefügte Zeilen, Sortierungen etc. schaden nicht)
			if ( ! preg_match( '/^(live:|settings\.|url:|einzelleistung\.|team\.|karriere\.)/', $ref ) ) { continue; }
			$known_ref++;
			if ( '' === $en ) { continue; }
			$out[] = array( 'ref' => $ref, 'en' => $en );
		}
		// Keine einzige bekannte Referenz → vermutlich falsche/umgebaute Datei
		if ( 0 === $known_ref ) {
			return new WP_Error( 'wrong_file', 'Keine gültigen Referenzen gefunden – bitte die über „Übersetzungsdatei herunterladen" erzeugte Datei verwenden (Spalte „Referenz" darf nicht verändert werden).' );
		}
		return $out;
	}
}

/** Export-Download: Übersetzungsdatei mit aktuellem DE/EN-Stand. */
add_action( 'admin_post_esc_lang_export', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Keine Berechtigung.' ); }
	check_admin_referer( 'esc_lang_export' );
	require_once ESC_DIR . 'inc/lang-xlsx.php';
	$rows = ES_Lang_Import::export_rows();
	$tmp  = wp_tempnam( 'uebersetzungen.xlsx' );
	ES_Lang_Xlsx::write( $tmp, $rows );
	header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
	header( 'Content-Disposition: attachment; filename="Uebersetzungen-Energiesozietaet-' . gmdate( 'Y-m-d' ) . '.xlsx"' );
	header( 'Content-Length: ' . filesize( $tmp ) );
	readfile( $tmp );
	unlink( $tmp );
	exit;
} );

/** Admin-Seite: Theme Options → EN-Import. */
add_action( 'admin_menu', function () {
	add_submenu_page( 'es-theme-options', 'EN-Import', 'EN-Import', 'manage_options', 'esc-lang-import', function () {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$result = null;
		if ( isset( $_POST['esc_lang_import_run'] ) && check_admin_referer( 'esc_lang_import' ) ) {
			$result = ES_Lang_Import::apply_file();
		}
		if ( isset( $_POST['esc_lang_upload_run'] ) && check_admin_referer( 'esc_lang_import' ) ) {
			if ( ! empty( $_FILES['esc_lang_file']['tmp_name'] ) ) {
				require_once ESC_DIR . 'inc/lang-xlsx.php';
				$sheet = ES_Lang_Xlsx::read( $_FILES['esc_lang_file']['tmp_name'] );
				if ( is_wp_error( $sheet ) ) {
					$result = $sheet;
				} else {
					$rows = ES_Lang_Import::rows_from_sheet( $sheet );
					$result = is_wp_error( $rows ) ? $rows : ES_Lang_Import::apply_rows( $rows );
				}
			} else {
				$result = new WP_Error( 'no_upload', 'Bitte eine XLSX-Datei auswählen.' );
			}
		}
		if ( isset( $_POST['esc_lang_copies_run'] ) && check_admin_referer( 'esc_lang_import' ) ) {
			$made = ES_Lang::create_page_copies();
			echo '<div class="notice notice-success"><p>Neu angelegte EN-Seitenkopien: ' . ( $made ? esc_html( implode( ', ', $made ) ) : 'keine (alle vorhanden)' ) . '</p></div>';
		}
		$file = ESC_DIR . 'data/translations-en.json';
		echo '<div class="wrap"><h1>Englische Übersetzungen</h1>';
		$copies = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1, 'meta_key' => 'es_lang', 'meta_value' => 'en', 'fields' => 'ids' ) );
		echo '<p><strong>Status:</strong> ' . count( $copies ) . ' englische Seitenkopien vorhanden. Zeigen englische Unterseiten „Page not found", fehlen Kopien – hier nachziehen:</p>';
		echo '<form method="post" style="margin-bottom:20px;">';
		wp_nonce_field( 'esc_lang_import' );
		submit_button( 'Fehlende EN-Seitenkopien anlegen', 'secondary', 'esc_lang_copies_run', false );
		echo '</form><hr>';
		echo '<h2>1 · Übersetzungsdatei exportieren</h2>';
		echo '<p>Erzeugt eine Excel-Datei mit allen aktuellen deutschen Texten und den bereits vorhandenen englischen Fassungen zum Ausfüllen. Neue Seiten und Inhalte sind automatisch enthalten.</p>';
		echo '<p><a class="button button-secondary" href="' . esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=esc_lang_export' ), 'esc_lang_export' ) ) . '">Übersetzungsdatei herunterladen (XLSX)</a></p>';
		echo '<h2>2 · Ausgefüllte Datei hochladen</h2>';
		echo '<p>Nur die Spalte „Englisch" und die Spalte „Referenz" werden gelesen; leere Englisch-Zellen bleiben unverändert. Fehlende EN-Seitenkopien werden automatisch angelegt.</p>';
		echo '<form method="post" enctype="multipart/form-data">';
		wp_nonce_field( 'esc_lang_import' );
		echo '<input type="file" name="esc_lang_file" accept=".xlsx" /> ';
		submit_button( 'Hochladen und einspielen', 'primary', 'esc_lang_upload_run', false );
		echo '</form><hr>';
		if ( file_exists( $file ) ) {
			echo '<h2>Alternativ: mitgelieferte Übersetzungsdatei</h2>';
			echo '<p>Spielt die im Plugin enthaltene Übersetzungsdatei ein. Bereits eingespielte Werte werden aktualisiert; deutsche Inhalte bleiben unberührt.</p>';
		}
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
		}
		echo '</div>';
	}, 45 );
} );
