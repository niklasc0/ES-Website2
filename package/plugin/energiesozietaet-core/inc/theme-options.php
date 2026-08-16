<?php
/**
 * Theme Options – zentraler Top-Level-Menuepunkt, der die verstreuten
 * Design-Einstellungen (Typografie, Farben, Layout, Footer, Kontaktformular)
 * unter einem Dach buendelt. Die Unterseiten registrieren sich selbst mit
 * dem Slug 'es-theme-options' als Parent.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Theme_Options {

	const SLUG = 'es-theme-options';

	public static function init() {
		// Prioritaet 9 -> Parent wird vor den Unterseiten (Default 10) registriert.
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 9 );
	}

	public static function menu() {
		add_menu_page(
			'Theme Options',
			'Theme Options',
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' ),
			'dashicons-admin-customizer',
			59
		);
		// Erstes Untermenue umbenennen (sonst doppelt "Theme Options").
		add_submenu_page(
			self::SLUG,
			'Theme Options – Uebersicht',
			'Uebersicht',
			'manage_options',
			self::SLUG,
			array( __CLASS__, 'render' ),
			0
		);
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$cards = array(
			array( 'Typografie',     'admin.php?page=esc-typography', 'Globale Schriftart (Name oder eigener Font-Upload), Gewicht & Stil.' ),
			array( 'Farben',         'admin.php?page=esc-colors',     'Akzent, Primaerfarbe, dunkle Sektionen und Seiten-Hintergrund.' ),
			array( 'Layout',         'admin.php?page=esc-layout',     'Sticky-Header, Back-to-Top-Button, Scroll-Indikator im Hero.' ),
			array( 'Footer',         'admin.php?page=esc-footer',     'Marke, Spalten, Kontaktdaten und Rechtliches im Footer.' ),
			array( 'Kontaktformular','admin.php?page=esc-contact',    'Felder, Themen (Betreff) und Empfaenger des Kontaktformulars.' ),
			array( 'Logo',           'customize.php?autofocus[section]=es_logos', 'Logo hell/dunkel im Customizer (mit Live-Vorschau).' ),
		);
		?>
		<div class="wrap">
			<h1>Theme Options</h1>
			<p>Zentrale Stelle fuer das Erscheinungsbild der Website. Waehle einen Bereich:</p>
			<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px;margin-top:24px;max-width:1000px;">
				<?php foreach ( $cards as $c ) : ?>
					<a href="<?php echo esc_url( admin_url( $c[1] ) ); ?>" style="display:block;padding:20px 22px;background:#fff;border:1px solid #dcdcde;border-radius:6px;text-decoration:none;color:#1d2327;">
						<strong style="font-size:15px;display:block;margin-bottom:6px;"><?php echo esc_html( $c[0] ); ?> &rarr;</strong>
						<span style="color:#646970;font-size:13px;line-height:1.5;"><?php echo esc_html( $c[2] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}
ESC_Theme_Options::init();
