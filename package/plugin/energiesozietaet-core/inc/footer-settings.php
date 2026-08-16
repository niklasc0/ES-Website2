<?php
/**
 * Footer-Einstellungen – generische 3 Spalten + Brand + CTA + Copyright.
 * Spalten werden im Frontend nur gerendert, wenn ihre Überschrift gesetzt
 * ist – leere Spalten werden ausgeblendet und die übrigen rücken zusammen.
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
			'cta_title'       => 'Haben wir Ihr Interesse geweckt?',
			'cta_subtitle'    => 'Möchten Sie uns kennenlernen?',
			'cta_btn1_label'  => 'Kontakt aufnehmen',
			'cta_btn1_url'    => home_url( '/kontakt/' ),
			'cta_btn2_label'  => 'info@energiesozietaet.de',
			'cta_btn2_url'    => 'mailto:info@energiesozietaet.de',

			'brand_name'    => 'Energiesozietät GmbH',
			'brand_sub'     => 'Recht · Steuern · Beratung',
			'brand_claim'   => 'Ergebnisse, die weitertragen.',
			'badges'        => "BVÖD\nForum Contracting\nVKU",

			// Spalte 1 (Adresse) + Spalte 2 (Navigation) erscheinen im Grid;
			// Spalte 3 (Rechtliches) speist nur die zentrierte Copyright-Leiste.
			'col1_heading' => 'Energiesozietät GmbH',
			'col1_lines'   => "Recht Steuern Beratung\nRoßstraße 92 | Kennedyhaus\n40476 Düsseldorf\ninfo@energiesozietaet.de\nCaffamacherreihe 8 | 20355 Hamburg\nJungbuschstraße 6 | 68159 Mannheim",
			'col2_heading' => 'Navigation',
			'col2_lines'   => "Home | /\nPhilosophie | /philosophie/\nLeistungen | /leistungen/\nTeam | /team/\nPublikationen | /publikationen/\nKarriere | /karriere/\nNews | /news/\nVeranstaltungen | /veranstaltungen/\nKontakt | /kontakt/",
			'col3_heading' => 'Rechtliches',
			'col3_lines'   => "Impressum | /impressum/\nDatenschutz | /datenschutzerklaerung/",

			'copyright' => '© {year} Energiesozietät GmbH · Alle Rechte vorbehalten.',
		);
	}

	/** Keys mit englischer Fassung (Suffix _en). */
	public static function en_keys() {
		return array(
			'cta_eyebrow', 'cta_title', 'cta_subtitle', 'cta_btn1_label', 'cta_btn2_label',
			'brand_sub', 'brand_claim',
			'col1_heading', 'col1_lines', 'col2_heading', 'col2_lines', 'col3_heading', 'col3_lines',
			'copyright',
		);
	}

	public static function get( $key = null ) {
		$opts = (array) get_option( self::OPT, array() );
		// Backward-Compat: alte Keys (col_anschrift_*, col_kontakt_*, col_legal_*) auf neue mappen
		$legacy_map = array(
			'col_anschrift_heading' => 'col1_heading', 'col_anschrift_lines' => 'col1_lines',
			'col_kontakt_heading'   => 'col2_heading', 'col_kontakt_lines'   => 'col2_lines',
			'col_legal_heading'     => 'col3_heading', 'col_legal_lines'     => 'col3_lines',
		);
		foreach ( $legacy_map as $old => $new ) {
			if ( ! isset( $opts[ $new ] ) && isset( $opts[ $old ] ) ) {
				$opts[ $new ] = $opts[ $old ];
			}
		}
		$opts = array_merge( self::defaults(), $opts );
		if ( function_exists( 'es_is_en' ) && es_is_en() ) {
			foreach ( self::en_keys() as $k ) {
				if ( ! empty( $opts[ $k . '_en' ] ) ) { $opts[ $k ] = $opts[ $k . '_en' ]; }
			}
		}
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	public static function parse_links( $raw ) {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) $raw ) as $ln ) {
			$ln = trim( $ln );
			if ( '' === $ln ) { continue; }
			$parts = array_map( 'trim', explode( '|', $ln, 2 ) );
			$url   = $parts[1] ?? '';
			// Nur echte Ziele verlinken – bei Adresszeilen wie
			// "Caffamacherreihe 8 | 20355 Hamburg" ist die Pipe nur Texttrenner.
			if ( $url && ! preg_match( '#^(https?://|/|\#|mailto:|tel:)#i', $url ) ) {
				$out[] = array( 'label' => $parts[0] . ' · ' . $url, 'url' => '' );
				continue;
			}
			$out[] = array( 'label' => $parts[0], 'url' => $url );
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
			if ( in_array( $k, array( 'cta_btn1_url', 'cta_btn2_url' ), true ) ) {
				$out[ $k ] = esc_url_raw( $val );
			} elseif ( false !== strpos( $k, '_lines' ) || 'badges' === $k || in_array( $k, array( 'cta_subtitle', 'brand_claim' ), true ) ) {
				$out[ $k ] = wp_kses_post( $val );
			} else {
				$out[ $k ] = sanitize_text_field( $val );
			}
		}
		foreach ( self::en_keys() as $k ) {
			$key_en = $k . '_en';
			if ( ! isset( $input[ $key_en ] ) ) { $out[ $key_en ] = ''; continue; }
			$val = wp_unslash( $input[ $key_en ] );
			$out[ $key_en ] = ( false !== strpos( $k, '_lines' ) || in_array( $k, array( 'cta_subtitle', 'brand_claim' ), true ) )
				? wp_kses_post( $val )
				: sanitize_text_field( $val );
		}
		return $out;
	}

	public static function menu() {
		add_submenu_page( 'es-theme-options', 'Footer', 'Footer', 'manage_options', 'esc-footer', array( __CLASS__, 'render' ), 40 );
	}

	protected static function input( $name, $label, $hint = '' ) {
		$val = self::get( $name );
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		echo '<input type="text" name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" value="' . esc_attr( $val ) . '" style="width:100%;max-width:640px;" />';
		if ( $hint ) { echo '<p class="description" style="font-style:normal;">' . wp_kses_post( $hint ) . '</p>'; }
		echo '</td></tr>';
	}

	protected static function textarea( $name, $label, $hint = '', $rows = 4 ) {
		$val = self::get( $name );
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		echo '<textarea name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" rows="' . (int) $rows . '" style="width:100%;max-width:640px;">' . esc_textarea( $val ) . '</textarea>';
		if ( $hint ) { echo '<p class="description" style="font-style:normal;">' . wp_kses_post( $hint ) . '</p>'; }
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Footer</h1>
			<p>Alle Inhalte des Seiten-Footers zentral pflegen. <strong>Spalten ohne Überschrift werden ausgeblendet</strong> – die übrigen rücken automatisch zusammen.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_footer_group' ); ?>

				<h2>CTA-Bar (oberhalb des Footers)</h2>
				<table class="form-table"><tbody>
					<?php
					self::input( 'cta_eyebrow',    'Eyebrow' );
					self::input( 'cta_title',      'Überschrift' );
					self::input( 'cta_subtitle',   'Untertitel', 'HTML erlaubt' );
					self::input( 'cta_btn1_label', 'Button 1 · Text' );
					self::input( 'cta_btn1_url',   'Button 1 · URL' );
					self::input( 'cta_btn2_label', 'Button 2 · Text' );
					self::input( 'cta_btn2_url',   'Button 2 · URL' );
					?>
				</tbody></table>

				<h2>Brand-Spalte (links)</h2>
				<table class="form-table"><tbody>
					<?php
					self::input(    'brand_name',  'Name' );
					self::input(    'brand_sub',   'Untertitel / Tagline' );
					self::textarea( 'brand_claim', 'Claim', '1–2 Sätze.', 2 );
					self::textarea( 'badges',      'Badges / Mitgliedschaften', 'Eine Zeile pro Badge.', 4 );
					?>
				</tbody></table>

				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<h2>Spalte <?php echo (int) $i; ?></h2>
					<p style="color:#5A6577;">Lasse die Überschrift leer, um diese Spalte komplett auszublenden.</p>
					<table class="form-table"><tbody>
						<?php
						self::input(    "col{$i}_heading", 'Überschrift' );
						self::textarea( "col{$i}_lines",   'Zeilen · Format „Label | URL"', 'Eine Zeile pro Eintrag – Format <code>Text | URL</code>. URL optional.', 5 );
						?>
					</tbody></table>
				<?php endfor; ?>

				<h2>Copyright-Zeile</h2>
				<table class="form-table"><tbody>
					<?php self::input( 'copyright', 'Text', 'Platzhalter <code>{year}</code> wird automatisch ersetzt.' ); ?>
				</tbody></table>

				<h2>Englische Fassung (EN)</h2>
				<p class="description" style="font-style:normal;">Leere Felder fallen im englischen Bereich auf die deutsche Fassung zurück. In den Spalten-Zeilen bitte englische Beschriftungen und <code>/en/…</code>-Ziele verwenden (z. B. <code>Home | /en/</code>).</p>
				<table class="form-table"><tbody>
					<?php
					self::input(    'cta_eyebrow_en',    'CTA Eyebrow (EN)' );
					self::input(    'cta_title_en',      'CTA Überschrift (EN)' );
					self::input(    'cta_subtitle_en',   'CTA Untertitel (EN)' );
					self::input(    'cta_btn1_label_en', 'Button 1 · Text (EN)' );
					self::input(    'cta_btn2_label_en', 'Button 2 · Text (EN)' );
					self::input(    'brand_sub_en',      'Brand-Untertitel (EN)' );
					self::textarea( 'brand_claim_en',    'Brand-Claim (EN)', '', 2 );
					for ( $i = 1; $i <= 3; $i++ ) {
						self::input(    "col{$i}_heading_en", "Spalte {$i} · Überschrift (EN)" );
						self::textarea( "col{$i}_lines_en",   "Spalte {$i} · Zeilen (EN)", 'Format wie deutsch: <code>Text | URL</code>.', 5 );
					}
					self::input( 'copyright_en', 'Copyright-Zeile (EN)' );
					?>
				</tbody></table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
ESC_Footer_Settings::init();
