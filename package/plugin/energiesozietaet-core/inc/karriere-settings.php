<?php
/**
 * Karriere-Einstellungen – globale Textbausteine für Stellen-Detailseiten.
 * Konfiguriert den Section-Titel "Was Dich bei uns erwartet" inkl. 4 Kacheln
 * sowie den "Deine Bewerbung"-Call-Out unten. Die Mailto-URL im Button wird
 * automatisch pro Stelle generiert (unverändert gelassen).
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Karriere_Settings {

	const OPT = 'esc_karriere';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
	}

	public static function defaults() {
		return array(
			'benefits_title'   => 'Was Dich bei uns erwartet',
			'benefit1_title'   => 'Echte Verantwortung',
			'benefit1_desc'    => 'Du arbeitest von Tag eins direkt am Mandat.',
			'benefit2_title'   => 'Interdisziplinarität',
			'benefit2_desc'    => 'Recht, Steuern und Unternehmensberatung zusammengedacht.',
			'benefit3_title'   => 'Persönliche Entwicklung',
			'benefit3_desc'    => 'Fortbildung, Promotion, Fachanwaltschaften.',
			'benefit4_title'   => 'Modernes Büro',
			'benefit4_desc'    => 'Im Herzen Düsseldorfs – mit flexiblen Arbeitsmodellen.',

			'cta_eyebrow'      => 'Deine Bewerbung',
			'cta_title'        => 'Bereit, gemeinsam durchzustarten?',
			'cta_subtitle'     => 'Wir freuen uns auf Deine Bewerbung.',
			'cta_button_label' => 'Jetzt bewerben',
			'cta_recipient'    => 'karriere@energiesozietaet.de',
		);
	}

	/** Keys, die eine englische Fassung haben können (Suffix _en). */
	public static function en_keys() {
		$keys = array( 'benefits_title', 'cta_eyebrow', 'cta_title', 'cta_subtitle', 'cta_button_label' );
		for ( $i = 1; $i <= 4; $i++ ) { $keys[] = 'benefit' . $i . '_title'; $keys[] = 'benefit' . $i . '_desc'; }
		return $keys;
	}

	public static function get( $key = null ) {
		$opts = self::raw();
		if ( function_exists( 'es_is_en' ) && es_is_en() ) {
			foreach ( self::en_keys() as $k ) {
				if ( ! empty( $opts[ $k . '_en' ] ) ) { $opts[ $k ] = $opts[ $k . '_en' ]; }
			}
		}
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	protected static function raw( $key = null ) {
		$opts = array_merge( self::defaults(), (array) get_option( self::OPT, array() ) );
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	public static function register() {
		register_setting( 'esc_karriere_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$out = array();
		foreach ( $defaults as $k => $v ) {
			$val = isset( $input[ $k ] ) ? wp_unslash( $input[ $k ] ) : $v;
			$out[ $k ] = sanitize_text_field( $val );
		}
		foreach ( self::en_keys() as $k ) {
			$out[ $k . '_en' ] = isset( $input[ $k . '_en' ] ) ? sanitize_text_field( wp_unslash( $input[ $k . '_en' ] ) ) : '';
		}
		if ( empty( $out['cta_recipient'] ) || ! is_email( $out['cta_recipient'] ) ) {
			$out['cta_recipient'] = $defaults['cta_recipient'];
		}
		return $out;
	}

	public static function menu() {
		add_submenu_page(
			'edit.php?post_type=es_karriere',
			'Globale Informationen',
			'Globale Informationen',
			'manage_options',
			'esc-karriere',
			array( __CLASS__, 'render' )
		);
	}

	protected static function input( $name, $label ) {
		$val = self::raw( $name );
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		if ( in_array( $name, self::en_keys(), true ) ) {
			// Deutsch + Englisch nebeneinander
			$val_en = self::raw( $name . '_en' );
			echo '<div style="display:flex;gap:16px;flex-wrap:wrap;max-width:980px;">';
			echo '<div style="flex:1;min-width:280px;"><input type="text" name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" value="' . esc_attr( $val ) . '" style="width:100%;" />';
			echo '<p class="description" style="font-style:normal;margin:2px 0 0;">Deutsch</p></div>';
			echo '<div style="flex:1;min-width:280px;"><input type="text" name="' . esc_attr( self::OPT . '[' . $name . '_en]' ) . '" value="' . esc_attr( $val_en ) . '" style="width:100%;" placeholder="EN" />';
			echo '<p class="description" style="font-style:normal;margin:2px 0 0;">Englisch – leer = deutsche Fassung</p></div>';
			echo '</div>';
		} else {
			echo '<input type="text" name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" value="' . esc_attr( $val ) . '" style="width:100%;max-width:640px;" />';
		}
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Globale Informationen</h1>
			<p>Globale Textbausteine für alle Stellen-Detailseiten. Die Mailto-URL im Bewerbungs-Button wird pro Stelle automatisch generiert.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_karriere_group' ); ?>

				<h2>Abschnitt „Was erwartet Dich bei uns?"</h2>
				<table class="form-table"><tbody>
					<?php self::input( 'benefits_title', 'Überschrift' ); ?>
					<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
						<tr><td colspan="2"><h3 style="margin:16px 0 0;">Kachel <?php echo (int) $i; ?></h3></td></tr>
						<?php
						self::input( 'benefit' . $i . '_title', 'Titel' );
						self::input( 'benefit' . $i . '_desc',  'Beschreibung' );
						?>
					<?php endfor; ?>
				</tbody></table>

				<h2>Abschnitt „Deine Bewerbung" (Call-Out unten)</h2>
				<table class="form-table"><tbody>
					<?php
					self::input( 'cta_eyebrow',      'Eyebrow' );
					self::input( 'cta_title',        'Hauptüberschrift' );
					self::input( 'cta_subtitle',     'Untertitel' );
					self::input( 'cta_button_label', 'Button-Text' );
					self::input( 'cta_recipient',    'Empfänger-E-Mail' );

					?>
				</tbody></table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
ESC_Karriere_Settings::init();
