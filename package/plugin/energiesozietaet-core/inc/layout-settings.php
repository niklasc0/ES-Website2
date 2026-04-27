<?php
/**
 * Layout-Einstellungen — globale UI-Toggles:
 *   • Sticky-Header (an/aus)
 *   • Back-To-Top-Button (an/aus, Schwellenwert)
 *   • Hero Scroll-Down-CTA (an/aus)
 * Liegt unter Design → Layout.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Layout_Settings {

	const OPT = 'esc_layout';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'wp_head',    array( __CLASS__, 'inline_flags' ), 6 );
		add_action( 'wp_footer',  array( __CLASS__, 'render_back_to_top' ) );
	}

	public static function defaults() {
		return array(
			'header_sticky' => 1,
			'back_to_top'   => 1,
			'btt_threshold' => 400,
			'hero_scroll'   => 1,
		);
	}

	public static function get( $key = null ) {
		$opts = array_merge( self::defaults(), (array) get_option( self::OPT, array() ) );
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	public static function register() {
		register_setting( 'esc_layout_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		return array(
			'header_sticky' => empty( $input['header_sticky'] ) ? 0 : 1,
			'back_to_top'   => empty( $input['back_to_top']   ) ? 0 : 1,
			'btt_threshold' => max( 50, min( 5000, (int) ( $input['btt_threshold'] ?? 400 ) ) ),
			'hero_scroll'   => empty( $input['hero_scroll']   ) ? 0 : 1,
		);
	}

	public static function menu() {
		add_submenu_page(
			'themes.php',
			'Layout',
			'Layout',
			'manage_options',
			'esc-layout',
			array( __CLASS__, 'render' )
		);
	}

	/** Klassen für Body-Tag und CSS-Variablen für JS-Schwellenwert. */
	public static function inline_flags() {
		$o = self::get();
		$flags = array();
		if ( $o['header_sticky'] ) { $flags[] = 'es-header-sticky'; }
		if ( $o['back_to_top']   ) { $flags[] = 'es-btt-on'; }
		if ( $o['hero_scroll']   ) { $flags[] = 'es-hero-scroll-on'; }
		// classList.add() ohne Argumente wirft TypeError → leere Argumentliste
		// vermeiden, indem wir gar nichts adden, wenn $flags leer ist.
		$add = $flags
			? 'document.documentElement.classList.add(' . implode( ',', array_map( function ( $c ) { return '"' . esc_js( $c ) . '"'; }, $flags ) ) . ');'
			: '';
		echo '<script>' . $add . 'document.documentElement.dataset.escBtt=' . (int) $o['btt_threshold'] . ';</script>';
	}

	/** Back-To-Top-Button ins Footer-Markup hängen, wenn aktiviert. */
	public static function render_back_to_top() {
		if ( ! self::get( 'back_to_top' ) ) { return; }
		echo '<button type="button" class="es-btt" aria-label="Nach oben scrollen" hidden>'
			. '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>'
			. '</button>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Layout</h1>
			<p>Globale UI-Toggles für die Website.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_layout_group' ); ?>
				<table class="form-table"><tbody>
					<tr><th scope="row">Sticky-Menüleiste</th><td>
						<label><input type="checkbox" name="<?php echo esc_attr( self::OPT . '[header_sticky]' ); ?>" value="1" <?php checked( self::get( 'header_sticky' ), 1 ); ?>> Header bleibt beim Scrollen oben kleben.</label>
					</td></tr>
					<tr><th scope="row">Back-To-Top-Button</th><td>
						<label><input type="checkbox" name="<?php echo esc_attr( self::OPT . '[back_to_top]' ); ?>" value="1" <?php checked( self::get( 'back_to_top' ), 1 ); ?>> Pfeil-Button rechts unten anzeigen, wenn der Nutzer ein Stück gescrollt hat.</label>
						<p class="description">Schwellenwert in Pixeln, ab dem der Button erscheint:</p>
						<input type="number" name="<?php echo esc_attr( self::OPT . '[btt_threshold]' ); ?>" value="<?php echo esc_attr( self::get( 'btt_threshold' ) ); ?>" min="50" max="5000" step="50" style="width:120px;">
					</td></tr>
					<tr><th scope="row">Scroll-Down-Indikator im Hero (Startseite)</th><td>
						<label><input type="checkbox" name="<?php echo esc_attr( self::OPT . '[hero_scroll]' ); ?>" value="1" <?php checked( self::get( 'hero_scroll' ), 1 ); ?>> Kleiner Hinweis rechts unten im Hero, der zum Weiterscrollen einlädt.</label>
					</td></tr>
				</tbody></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
ESC_Layout_Settings::init();
