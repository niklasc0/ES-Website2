<?php
/**
 * Footer-Einstellungen — Settings-API-Page, damit der Footer im Backend
 * frei konfigurierbar ist ohne Code-Änderungen.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Footer_Settings {

	const OPT = 'esc_footer';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
	}

	public static function defaults() {
		return array(
			'cta_eyebrow'     => 'Kontakt',
			'cta_title'       => 'Sprechen Sie mit uns.',
			'cta_subtitle'    => 'Unaufgeregt, direkt, fachlich.',
			'cta_btn1_label'  => 'Termin vereinbaren',
			'cta_btn1_url'    => home_url( '/kontakt/' ),
			'cta_btn2_label'  => 'info@energiesozietaet.de',
			'cta_btn2_url'    => 'mailto:info@energiesozietaet.de',

			'brand_name'    => 'Energiesozietät GmbH',
			'brand_sub'     => 'Recht · Steuern · Beratung',
			'brand_claim'   => 'Beratung mit Leidenschaft — Ergebnisse, die weitertragen.',
			'badges'        => "BVÖD\nForum Contracting\nVKU",

			'col_anschrift_heading' => 'Anschrift',
			'col_anschrift_lines'   => "Energiesozietät GmbH\nRoßstraße 92 / Kennedyhaus\n40476 Düsseldorf",

			'col_kontakt_heading' => 'Kontakt',
			'col_kontakt_lines'   => "+49 211 159232-0 | tel:+492111592320\ninfo@energiesozietaet.de | mailto:info@energiesozietaet.de\nLinkedIn ↗ | https://www.linkedin.com/company/energiesozietaet/",

			'col_legal_heading' => 'Rechtliches',
			'col_legal_lines'   => "Impressum | /impressum/\nDatenschutz | /datenschutzerklaerung/",

			'copyright' => '© {year} Energiesozietät GmbH · Alle Rechte vorbehalten.',
		);
	}

	public static function get( $key = null ) {
		$opts = get_option( self::OPT, array() );
		$opts = array_merge( self::defaults(), (array) $opts );
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	/**
	 * Parst "Label | URL"-Zeilen. URL optional.
	 * @return array[] [ ['label' => '...', 'url' => '...'], ... ]
	 */
	public static function parse_links( $raw ) {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) $raw ) as $ln ) {
			$ln = trim( $ln );
			if ( '' === $ln ) { continue; }
			$parts = array_map( 'trim', explode( '|', $ln, 2 ) );
			$out[] = array( 'label' => $parts[0], 'url' => $parts[1] ?? '' );
		}
		return $out;
	}

	public static function parse_lines( $raw ) {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) $raw ) as $ln ) {
			$ln = trim( $ln );
			if ( '' !== $ln ) { $out[] = $ln; }
		}
		return $out;
	}

	public static function register() {
		register_setting( 'esc_footer_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$out = array();
		foreach ( $defaults as $k => $v ) {
			if ( ! isset( $input[ $k ] ) ) { $out[ $k ] = $v; continue; }
			$val = wp_unslash( $input[ $k ] );
			if ( 'cta_btn1_url' === $k || 'cta_btn2_url' === $k ) {
				$out[ $k ] = esc_url_raw( $val );
			} elseif ( false !== strpos( $k, '_lines' ) || 'badges' === $k || false !== strpos( $k, 'cta_subtitle' ) || 'brand_claim' === $k ) {
				$out[ $k ] = wp_kses_post( $val );
			} else {
				$out[ $k ] = sanitize_text_field( $val );
			}
		}
		return $out;
	}

	public static function menu() {
		add_submenu_page(
			'themes.php',
			'Footer',
			'Footer',
			'manage_options',
			'esc-footer',
			array( __CLASS__, 'render' )
		);
	}

	protected static function textarea( $name, $label, $hint = '', $rows = 4 ) {
		$val = self::get( $name );
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		echo '<textarea name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" rows="' . (int) $rows . '" style="width:100%;max-width:640px;">' . esc_textarea( $val ) . '</textarea>';
		if ( $hint ) { echo '<p class="description">' . wp_kses_post( $hint ) . '</p>'; }
		echo '</td></tr>';
	}

	protected static function input( $name, $label, $hint = '' ) {
		$val = self::get( $name );
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		echo '<input type="text" name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" value="' . esc_attr( $val ) . '" style="width:100%;max-width:640px;" />';
		if ( $hint ) { echo '<p class="description">' . wp_kses_post( $hint ) . '</p>'; }
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Footer-Einstellungen</h1>
			<p>Alle Inhalte des Seiten-Footers — inkl. CTA-Bar, Brand-Spalte, Standort-Info und Rechtliches — kannst Du hier zentral pflegen. Änderungen werden sofort auf jeder Seite übernommen.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_footer_group' ); ?>

				<h2>CTA-Bar (oberhalb des Footers)</h2>
				<table class="form-table"><tbody>
					<?php
					self::input( 'cta_eyebrow',    'Eyebrow',        'Kleine Uppercase-Label (z.B. „Kontakt")' );
					self::input( 'cta_title',      'Überschrift',     'Hauptüberschrift' );
					self::input( 'cta_subtitle',   'Untertitel',      'Kleiner Text unter der Überschrift (HTML erlaubt)' );
					self::input( 'cta_btn1_label', 'Button 1 · Text', '' );
					self::input( 'cta_btn1_url',   'Button 1 · URL',  '' );
					self::input( 'cta_btn2_label', 'Button 2 · Text', '' );
					self::input( 'cta_btn2_url',   'Button 2 · URL',  '' );
					?>
				</tbody></table>

				<h2>Brand-Spalte (links)</h2>
				<table class="form-table"><tbody>
					<?php
					self::input(    'brand_name',   'Name' );
					self::input(    'brand_sub',    'Untertitel / Tagline' );
					self::textarea( 'brand_claim', 'Claim', '1–2 Sätze.', 2 );
					self::textarea( 'badges',      'Badges / Mitgliedschaften', 'Eine Zeile pro Badge (reiner Text).', 4 );
					?>
				</tbody></table>

				<h2>Spalte „Anschrift"</h2>
				<table class="form-table"><tbody>
					<?php
					self::input(    'col_anschrift_heading', 'Überschrift' );
					self::textarea( 'col_anschrift_lines',   'Zeilen', 'Eine Zeile pro Anschriftenteil.', 4 );
					?>
				</tbody></table>

				<h2>Spalte „Kontakt"</h2>
				<table class="form-table"><tbody>
					<?php
					self::input(    'col_kontakt_heading', 'Überschrift' );
					self::textarea( 'col_kontakt_lines', 'Links · Format „Label | URL"', 'Eine Zeile pro Eintrag, Format <code>Text | URL</code>. Die URL ist optional — ohne URL wird nur der Text angezeigt.', 5 );
					?>
				</tbody></table>

				<h2>Spalte „Rechtliches"</h2>
				<table class="form-table"><tbody>
					<?php
					self::input(    'col_legal_heading', 'Überschrift' );
					self::textarea( 'col_legal_lines', 'Links · Format „Label | URL"', 'Eine Zeile pro Eintrag.', 5 );
					?>
				</tbody></table>

				<h2>Copyright-Zeile</h2>
				<table class="form-table"><tbody>
					<?php self::input( 'copyright', 'Text', 'Platzhalter <code>{year}</code> wird automatisch durchs aktuelle Jahr ersetzt.' ); ?>
				</tbody></table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
ESC_Footer_Settings::init();
