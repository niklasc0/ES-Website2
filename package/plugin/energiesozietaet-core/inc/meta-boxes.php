<?php
/**
 * Meta boxes for CPTs.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_MetaBoxes {

	const FIELDS = array(
		'rechtsberatung'       => 'Rechtsberatung',
		'steuerberatung'       => 'Steuerberatung',
		'unternehmensberatung' => 'Unternehmensberatung',
		'management'           => 'Management / Büroleitung',
	);

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
		add_action( 'save_post',      array( __CLASS__, 'save' ), 10, 2 );
	}

	public static function register() {
		add_meta_box( 'esc_team',          'Teammitglied-Details',    array( __CLASS__, 'box_team' ),         'es_team',           'normal', 'default' );
		add_meta_box( 'esc_einzel',        'Einzelleistung-Details',  array( __CLASS__, 'box_einzel' ),       'es_einzelleistung', 'normal', 'default' );
		add_meta_box( 'esc_karriere',      'Karriere-Details',        array( __CLASS__, 'box_karriere' ),     'es_karriere',       'normal', 'default' );
		add_meta_box( 'esc_veranstaltung', 'Veranstaltungs-Details',  array( __CLASS__, 'box_veranst' ),      'es_veranstaltung',  'normal', 'default' );
		add_meta_box( 'esc_publikation',   'Publikations-Details',    array( __CLASS__, 'box_publikation' ),  'es_publikation',    'normal', 'default' );
	}

	protected static function nonce() {
		wp_nonce_field( 'esc_meta', 'esc_meta_nonce' );
	}

	protected static function field( $label, $name, $value, $type = 'text', $opts = array() ) {
		$id = 'esc_' . $name;
		echo '<p style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">';
		echo '<label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label>';
		if ( $type === 'textarea' ) {
			echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="4" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
		} elseif ( $type === 'date' ) {
			echo '<input type="date" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
		} elseif ( $type === 'url' ) {
			echo '<input type="url" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" />';
		} elseif ( $type === 'select' ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" style="max-width:360px;">';
			echo '<option value="">— bitte wählen —</option>';
			foreach ( (array) ( $opts['options'] ?? array() ) as $k => $v ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" />';
		}
		echo '</p>';
	}

	/** Returns Team posts as [post_id => "Vorname Nachname (Rolle)"] for selects. */
	protected static function team_options() {
		$q = new WP_Query( array( 'post_type' => 'es_team', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC', 'fields' => 'all' ) );
		$out = array();
		foreach ( $q->posts as $p ) {
			$role = (string) get_post_meta( $p->ID, 'es_role', true );
			$out[ $p->ID ] = $p->post_title . ( $role ? ' · ' . $role : '' );
		}
		return $out;
	}

	public static function box_team( $post ) {
		self::nonce();
		self::field( 'Rolle / Position', 'es_role',     get_post_meta( $post->ID, 'es_role', true ) );
		self::field(
			'Geschlecht (Anrede „Ihr/e Ansprechpartner/in")',
			'es_gender',
			get_post_meta( $post->ID, 'es_gender', true ),
			'select',
			array( 'options' => array(
				'm' => 'männlich — „Ihr Ansprechpartner"',
				'w' => 'weiblich — „Ihre Ansprechpartnerin"',
				'd' => 'divers — „Ihr:e Ansprechpartner:in"',
			) )
		);
		self::field( 'E-Mail',           'es_email',    get_post_meta( $post->ID, 'es_email', true ) );
		self::field( 'Telefon',          'es_phone',    get_post_meta( $post->ID, 'es_phone', true ) );
		self::field( 'LinkedIn-URL',     'es_linkedin', get_post_meta( $post->ID, 'es_linkedin', true ), 'url' );
		self::field( 'Standort',         'es_location', get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Nachname für Sortierung (optional)', 'es_sort_name', get_post_meta( $post->ID, 'es_sort_name', true ) );
		echo '<p class="description" style="margin:-8px 0 16px;">Leer lassen — dann wird automatisch nach dem letzten Namensteil sortiert (z.&nbsp;B. „Otto" bei „Prof. Dr. Sven-Joachim Otto"). Nur bei mehrteiligen Nachnamen (z.&nbsp;B. „van der Berg") hier den Sortier-Nachnamen eintragen.</p>';
		self::field(
			'Beratungsfeld',
			'es_field',
			get_post_meta( $post->ID, 'es_field', true ),
			'select',
			array( 'options' => self::FIELDS )
		);
		self::field( 'Erweiterte Vita (optional)', 'es_more_bio', get_post_meta( $post->ID, 'es_more_bio', true ), 'textarea' );

		// Schwerpunkte — eine Zeile pro Stichpunkt
		$focus = get_post_meta( $post->ID, 'es_focus_areas', true );
		$focus_lines = array();
		if ( is_array( $focus ) ) {
			foreach ( $focus as $f ) {
				if ( is_array( $f ) ) {
					$focus_lines[] = (string) ( $f['title'] ?? '' );
				} else {
					$focus_lines[] = (string) $f;
				}
			}
		}
		echo '<p><label><strong>Ausgewählte Schwerpunkte</strong><br /><em style="color:#667;font-weight:400;">Ein Schwerpunkt pro Zeile.</em></label>';
		echo '<textarea name="es_focus_areas_raw" rows="6" style="width:100%;">' . esc_textarea( implode( "\n", $focus_lines ) ) . '</textarea></p>';

		// Werdegang — Zeile je Station: "Zeit ||| Text"
		$career = get_post_meta( $post->ID, 'es_career', true );
		$career_lines = array();
		if ( is_array( $career ) ) {
			foreach ( $career as $c ) {
				$when = is_array( $c ) ? (string) ( $c['when'] ?? '' ) : '';
				$what = is_array( $c ) ? (string) ( $c['what'] ?? '' ) : (string) $c;
				$career_lines[] = trim( $when . ' ||| ' . $what );
			}
		}
		echo '<p><label><strong>Werdegang</strong><br /><em style="color:#667;font-weight:400;">Eine Station pro Zeile, Format <code>Zeit ||| Beschreibung</code> (z.B. <code>seit 2023 ||| Partner, Energiesozietät</code>)</em></label>';
		echo '<textarea name="es_career_raw" rows="6" style="width:100%;">' . esc_textarea( implode( "\n", $career_lines ) ) . '</textarea></p>';
	}

	public static function box_einzel( $post ) {
		self::nonce();
		self::field( 'Untertitel (Hero)',  'es_subtitle', get_post_meta( $post->ID, 'es_subtitle', true ), 'textarea' );
		self::field( 'Abschluss-Absatz',   'es_closing',  get_post_meta( $post->ID, 'es_closing', true ),  'textarea' );

		$bullets = get_post_meta( $post->ID, 'es_bullets', true );
		$txt = is_array( $bullets ) ? implode( "\n", $bullets ) : '';
		echo '<p><label><strong>Kernpunkte (eine Zeile = ein Bullet)</strong></label>';
		echo '<textarea name="es_bullets_raw" rows="6" style="width:100%;">' . esc_textarea( $txt ) . '</textarea></p>';

		self::field( 'Ansprechpartner (Kontaktkarte)', 'es_ansprechpartner', get_post_meta( $post->ID, 'es_ansprechpartner', true ), 'select', array( 'options' => self::team_options() ) );
	}

	public static function box_karriere( $post ) {
		self::nonce();
		self::field( 'Rolle / Titel-Kürzel', 'es_department',      get_post_meta( $post->ID, 'es_department', true ) );
		self::field(
			'Beratungsbereich',
			'es_field',
			get_post_meta( $post->ID, 'es_field', true ),
			'select',
			array( 'options' => self::FIELDS )
		);
		self::field( 'Standort',           'es_location',        get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Anstellungsart',     'es_employment_type', get_post_meta( $post->ID, 'es_employment_type', true ) );
		self::field( 'Eintrittsdatum',     'es_start_date',      get_post_meta( $post->ID, 'es_start_date', true ), 'date' );

		// Aufgaben (Was erwarten Dich für Aufgaben?) — Legacy-Fallback: es_bullets
		$tasks = get_post_meta( $post->ID, 'es_tasks', true );
		if ( empty( $tasks ) ) {
			$legacy = get_post_meta( $post->ID, 'es_bullets', true );
			if ( is_array( $legacy ) ) { $tasks = $legacy; }
		}
		$tasks_txt = is_array( $tasks ) ? implode( "\n", $tasks ) : '';
		echo '<p><label><strong>Was erwarten Dich für Aufgaben?</strong><br /><em style="color:#667;font-weight:400;">Eine Aufgabe pro Zeile — wird als Bullet-Liste ausgegeben.</em></label>';
		echo '<textarea name="es_tasks_raw" rows="6" style="width:100%;">' . esc_textarea( $tasks_txt ) . '</textarea></p>';

		// Profil (Dein Profil)
		$profile = get_post_meta( $post->ID, 'es_profile', true );
		$profile_txt = is_array( $profile ) ? implode( "\n", $profile ) : '';
		echo '<p><label><strong>Dein Profil</strong><br /><em style="color:#667;font-weight:400;">Eine Anforderung pro Zeile — wird als Bullet-Liste ausgegeben.</em></label>';
		echo '<textarea name="es_profile_raw" rows="6" style="width:100%;">' . esc_textarea( $profile_txt ) . '</textarea></p>';
	}

	public static function box_veranst( $post ) {
		self::nonce();
		self::field( 'Startdatum', 'es_start_date', get_post_meta( $post->ID, 'es_start_date', true ), 'date' );
		self::field( 'Enddatum',   'es_end_date',   get_post_meta( $post->ID, 'es_end_date', true ),   'date' );
		self::field( 'Ort',        'es_location',   get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Art',        'es_kind',       get_post_meta( $post->ID, 'es_kind', true ) );
		self::field( 'Anmelde-URL','es_registration_url', get_post_meta( $post->ID, 'es_registration_url', true ), 'url' );
	}

	public static function box_publikation( $post ) {
		self::nonce();
		self::field( 'Kategorie (z.B. Aufsatz, Buch, Kommentar)', 'es_cat',    get_post_meta( $post->ID, 'es_cat', true ) );
		self::field( 'Fundstelle / Quelle',                        'es_source', get_post_meta( $post->ID, 'es_source', true ) );
		self::field( 'Veröffentlichungsdatum',                     'es_publication_date', get_post_meta( $post->ID, 'es_publication_date', true ), 'date' );
		self::field( 'Externer Link (Zur Publikation)',            'es_link',   get_post_meta( $post->ID, 'es_link', true ), 'url' );
		self::field( 'Autor:innen (Freitext, Fallback)',           'es_author', get_post_meta( $post->ID, 'es_author', true ) );

		// Team-Autoren (Relation, mehrere)
		$selected = (array) get_post_meta( $post->ID, 'es_author_ids', true );
		$options  = self::team_options();
		echo '<p><label><strong>Autor:innen aus Team</strong><br /><em style="color:#667;font-weight:400;">Strg/Cmd-Klick für Mehrfachauswahl. Diese Zuordnung führt dazu, dass die Publikation auf der jeweiligen Team-Einzelseite und im Beratungsfeld erscheint.</em></label>';
		echo '<select name="es_author_ids[]" multiple size="8" style="width:100%;max-width:480px;">';
		foreach ( $options as $id => $label ) {
			echo '<option value="' . (int) $id . '"' . ( in_array( (string) $id, array_map( 'strval', $selected ), true ) ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select></p>';

		// Beratungsfelder (Mehrfach via es_beratungsfeld-Taxonomie existiert schon für Einzelleistung — hier nutzen wir zusätzlich ein Meta für Publikation, da Taxonomie auf es_einzelleistung limitiert ist)
		$field_selected = (array) get_post_meta( $post->ID, 'es_fields', true );
		echo '<p><label><strong>Beratungsfelder</strong><br /><em style="color:#667;font-weight:400;">Diese Zuordnung führt dazu, dass die Publikation auf der jeweiligen Beratungsfeld-Seite als Fachbeitrag erscheint.</em></label>';
		foreach ( self::FIELDS as $k => $v ) {
			echo '<label style="display:inline-flex;align-items:center;gap:6px;margin-right:20px;margin-top:6px;">';
			echo '<input type="checkbox" name="es_fields[]" value="' . esc_attr( $k ) . '"' . ( in_array( $k, $field_selected, true ) ? ' checked' : '' ) . ' /> ' . esc_html( $v );
			echo '</label>';
		}
		echo '</p>';
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['esc_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['esc_meta_nonce'] ), 'esc_meta' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$scalars = array(
			'es_role','es_gender','es_email','es_phone','es_linkedin','es_location','es_sort_name','es_field','es_more_bio',
			'es_subtitle','es_closing','es_ansprechpartner',
			'es_department','es_employment_type','es_start_date',
			'es_end_date','es_kind','es_registration_url',
			'es_cat','es_source','es_publication_date','es_link','es_author',
		);
		foreach ( $scalars as $k ) {
			if ( array_key_exists( $k, $_POST ) ) {
				$v = wp_unslash( $_POST[ $k ] );
				if ( in_array( $k, array( 'es_link', 'es_linkedin', 'es_registration_url' ), true ) ) {
					$v = esc_url_raw( $v );
				} elseif ( in_array( $k, array( 'es_more_bio', 'es_closing', 'es_subtitle' ), true ) ) {
					$v = wp_kses_post( $v );
				} else {
					$v = sanitize_text_field( $v );
				}
				update_post_meta( $post_id, $k, $v );
			}
		}

		if ( isset( $_POST['es_bullets_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_bullets_raw'] ) );
			$bullets = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $bullets[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_bullets', $bullets );
		}

		if ( isset( $_POST['es_tasks_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_tasks_raw'] ) );
			$arr = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_tasks', $arr );
		}

		if ( isset( $_POST['es_profile_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_profile_raw'] ) );
			$arr = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_profile', $arr );
		}

		if ( isset( $_POST['es_focus_areas_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_focus_areas_raw'] ) );
			$focus = array();
			foreach ( $lines as $l ) {
				$l = trim( $l );
				if ( ! $l ) { continue; }
				// Support both "Titel ||| Inhalt" legacy and plain "Titel"
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				$focus[] = array( 'title' => wp_kses_post( $parts[0] ), 'content' => isset( $parts[1] ) ? wp_kses_post( $parts[1] ) : '' );
			}
			update_post_meta( $post_id, 'es_focus_areas', $focus );
		}

		if ( isset( $_POST['es_career_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_career_raw'] ) );
			$career = array();
			foreach ( $lines as $l ) {
				$l = trim( $l );
				if ( ! $l ) { continue; }
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				$career[] = array( 'when' => sanitize_text_field( $parts[0] ), 'what' => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '' );
			}
			update_post_meta( $post_id, 'es_career', $career );
		}

		if ( isset( $_POST['es_author_ids'] ) && is_array( $_POST['es_author_ids'] ) ) {
			$ids = array_values( array_filter( array_map( 'intval', $_POST['es_author_ids'] ) ) );
			update_post_meta( $post_id, 'es_author_ids', $ids );
		} elseif ( 'es_publikation' === $post->post_type ) {
			update_post_meta( $post_id, 'es_author_ids', array() );
		}

		if ( 'es_publikation' === $post->post_type ) {
			$fields = isset( $_POST['es_fields'] ) && is_array( $_POST['es_fields'] ) ? array_values( array_intersect( array_keys( self::FIELDS ), array_map( 'sanitize_text_field', wp_unslash( $_POST['es_fields'] ) ) ) ) : array();
			update_post_meta( $post_id, 'es_fields', $fields );
		}
	}
}
ESC_MetaBoxes::init();
