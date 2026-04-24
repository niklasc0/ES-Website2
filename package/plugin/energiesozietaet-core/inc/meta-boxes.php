<?php
/**
 * Meta boxes for CPTs.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_MetaBoxes {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
		add_action( 'save_post',      array( __CLASS__, 'save' ), 10, 2 );
	}

	public static function register() {
		add_meta_box( 'esc_team',       'Teammitglied-Details',   array( __CLASS__, 'box_team' ),       'es_team',          'normal', 'default' );
		add_meta_box( 'esc_einzel',     'Einzelleistung-Details', array( __CLASS__, 'box_einzel' ),     'es_einzelleistung','normal', 'default' );
		add_meta_box( 'esc_karriere',   'Karriere-Details',       array( __CLASS__, 'box_karriere' ),   'es_karriere',      'normal', 'default' );
		add_meta_box( 'esc_veranstaltung','Veranstaltungs-Details', array( __CLASS__, 'box_veranst' ), 'es_veranstaltung', 'normal', 'default' );
		add_meta_box( 'esc_publikation','Publikations-Details',   array( __CLASS__, 'box_publikation' ),'es_publikation',   'normal', 'default' );
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
		} else {
			echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" />';
		}
		echo '</p>';
	}

	public static function box_team( $post ) {
		self::nonce();
		self::field( 'Rolle / Position', 'es_role',     get_post_meta( $post->ID, 'es_role', true ) );
		self::field( 'E-Mail',           'es_email',    get_post_meta( $post->ID, 'es_email', true ) );
		self::field( 'Telefon',          'es_phone',    get_post_meta( $post->ID, 'es_phone', true ) );
		self::field( 'LinkedIn-URL',     'es_linkedin', get_post_meta( $post->ID, 'es_linkedin', true ), 'url' );
		self::field( 'Erweiterte Vita (optional)', 'es_more_bio', get_post_meta( $post->ID, 'es_more_bio', true ), 'textarea' );

		// Focus areas (accordion): render as repeater via textarea of title|content per line
		$focus = get_post_meta( $post->ID, 'es_focus_areas', true );
		$lines = array();
		if ( is_array( $focus ) ) {
			foreach ( $focus as $f ) {
				$lines[] = ( isset( $f['title'] ) ? $f['title'] : '' ) . ' ||| ' . ( isset( $f['content'] ) ? str_replace( "\n", ' ', $f['content'] ) : '' );
			}
		}
		?>
		<p><label><strong>Schwerpunkte</strong><br /><em style="color:#667;font-weight:400;">Ein Schwerpunkt pro Zeile, Format: <code>Titel ||| Inhalt</code></em></label>
			<textarea name="es_focus_areas_raw" rows="5" style="width:100%;"><?php echo esc_textarea( implode( "\n", $lines ) ); ?></textarea>
		</p>
		<?php
	}

	public static function box_einzel( $post ) {
		self::nonce();
		self::field( 'Untertitel (Hero)',  'es_subtitle', get_post_meta( $post->ID, 'es_subtitle', true ), 'textarea' );
		self::field( 'Abschluss-Absatz',   'es_closing',  get_post_meta( $post->ID, 'es_closing', true ),  'textarea' );

		$bullets = get_post_meta( $post->ID, 'es_bullets', true );
		$txt = is_array( $bullets ) ? implode( "\n", $bullets ) : '';
		echo '<p><label><strong>Kernpunkte (eine Zeile = ein Bullet)</strong></label>';
		echo '<textarea name="es_bullets_raw" rows="6" style="width:100%;">' . esc_textarea( $txt ) . '</textarea></p>';
	}

	public static function box_karriere( $post ) {
		self::nonce();
		self::field( 'Bereich',            'es_department',      get_post_meta( $post->ID, 'es_department', true ) );
		self::field( 'Standort',           'es_location',        get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Anstellungsart',     'es_employment_type', get_post_meta( $post->ID, 'es_employment_type', true ) );

		$bullets = get_post_meta( $post->ID, 'es_bullets', true );
		$txt = is_array( $bullets ) ? implode( "\n", $bullets ) : '';
		echo '<p><label><strong>Aufgaben / Anforderungen (Bullet-Liste)</strong></label>';
		echo '<textarea name="es_bullets_raw" rows="6" style="width:100%;">' . esc_textarea( $txt ) . '</textarea></p>';
	}

	public static function box_veranst( $post ) {
		self::nonce();
		self::field( 'Startdatum', 'es_start_date', get_post_meta( $post->ID, 'es_start_date', true ), 'date' );
		self::field( 'Enddatum',   'es_end_date',   get_post_meta( $post->ID, 'es_end_date', true ),   'date' );
		self::field( 'Ort',        'es_location',   get_post_meta( $post->ID, 'es_location', true ) );
	}

	public static function box_publikation( $post ) {
		self::nonce();
		self::field( 'Autor:innen',           'es_authors',          get_post_meta( $post->ID, 'es_authors', true ) );
		self::field( 'Veröffentlichungsdatum','es_publication_date', get_post_meta( $post->ID, 'es_publication_date', true ), 'date' );
		self::field( 'Externer Link',         'es_link',             get_post_meta( $post->ID, 'es_link', true ), 'url' );
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['esc_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['esc_meta_nonce'] ), 'esc_meta' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$keys = array(
			'es_role','es_email','es_phone','es_linkedin','es_more_bio',
			'es_subtitle','es_closing',
			'es_department','es_location','es_employment_type',
			'es_start_date','es_end_date',
			'es_authors','es_publication_date','es_link',
		);
		foreach ( $keys as $k ) {
			if ( array_key_exists( $k, $_POST ) ) {
				$v = wp_unslash( $_POST[ $k ] );
				if ( in_array( $k, array( 'es_link', 'es_linkedin' ), true ) ) {
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

		if ( isset( $_POST['es_focus_areas_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_focus_areas_raw'] ) );
			$focus = array();
			foreach ( $lines as $l ) {
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				if ( ! empty( $parts[0] ) ) {
					$focus[] = array( 'title' => wp_kses_post( $parts[0] ), 'content' => isset( $parts[1] ) ? wp_kses_post( $parts[1] ) : '' );
				}
			}
			update_post_meta( $post_id, 'es_focus_areas', $focus );
		}
	}
}
ESC_MetaBoxes::init();
