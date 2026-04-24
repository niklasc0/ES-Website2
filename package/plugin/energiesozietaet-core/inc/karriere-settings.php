<?php
/**
 * Karriere-Einstellungen — globale Textbausteine für Stellen-Detailseiten.
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
			'benefit4_desc'    => 'Im Herzen Düsseldorfs — mit flexiblen Arbeitsmodellen.',

			'cta_eyebrow'      => 'Deine Bewerbung',
			'cta_title'        => 'Bereit, gemeinsam durchzustarten?',
			'cta_subtitle'     => 'Schicke uns Deine Unterlagen — wir melden uns binnen eines Werktags.',
			'cta_button_label' => 'Jetzt bewerben',
			'cta_recipient'    => 'karriere@energiesozietaet.de',
		);
	}

	public static function get( $key = null ) {
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
		if ( empty( $out['cta_recipient'] ) || ! is_email( $out['cta_recipient'] ) ) {
			$out['cta_recipient'] = $defaults['cta_recipient'];
		}
		return $out;
	}

	public static function menu() {
		add_options_page(
			'Karriere-Einstellungen',
			'Karriere (Energiesozietät)',
			'manage_options',
			'esc-karriere',
			array( __CLASS__, 'render' )
		);
	}

	protected static function input( $name, $label ) {
		$val = self::get( $name );
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		echo '<input type="text" name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" value="' . esc_attr( $val ) . '" style="width:100%;max-width:640px;" />';
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Karriere-Einstellungen</h1>
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
