<?php
/**
 * Farben — globale Marken-Farben als CSS-Variablen-Override. Liegt unter
 * Theme Options -> Farben. Leere Felder = Theme-Default.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Colors_Settings {

	const OPT = 'esc_colors';

	/** Hook-Suffix der eigenen Settings-Seite (fuer gezieltes Asset-Laden). */
	protected static $hook = '';

	/** Feld-Key => [ Label, [ CSS-Variablen ], Default-Hex ]. */
	protected static function fields() {
		return array(
			'accent'     => array( 'Akzentfarbe',                       array( '--es-accent' ),               '#95D708' ),
			'ink'        => array( 'Primaerfarbe (Text & Ueberschriften)', array( '--es-ink', '--es-text' ),  '#122023' ),
			'paper_warm' => array( 'Dunkle Sektionen',                  array( '--es-paper-warm' ),           '#1D2D2D' ),
			'paper'      => array( 'Seiten-Hintergrund',                array( '--es-paper' ),                '#FFFFFF' ),
		);
	}

	public static function init() {
		add_action( 'admin_init',            array( __CLASS__, 'register' ) );
		add_action( 'admin_menu',            array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_head',               array( __CLASS__, 'print_vars' ), 20 );
	}

	public static function get( $key = null ) {
		$opts = (array) get_option( self::OPT, array() );
		return null === $key ? $opts : ( $opts[ $key ] ?? '' );
	}

	public static function register() {
		register_setting( 'esc_colors_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$out = array();
		foreach ( self::fields() as $key => $f ) {
			$val = isset( $input[ $key ] ) ? sanitize_hex_color( $input[ $key ] ) : '';
			if ( $val ) { $out[ $key ] = $val; }
		}
		return $out;
	}

	public static function menu() {
		self::$hook = add_submenu_page(
			ESC_Theme_Options::SLUG,
			'Farben',
			'Farben',
			'manage_options',
			'esc-colors',
			array( __CLASS__, 'render' ),
			20
		);
	}

	public static function assets( $hook ) {
		if ( $hook !== self::$hook ) { return; }
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script( 'wp-color-picker', 'jQuery(function($){$(".es-color-field").wpColorPicker();});' );
	}

	/** :root-Override im Head — nur gesetzte Werte, Theme-Default bleibt sonst. */
	public static function print_vars() {
		$opts  = self::get();
		$decls = array();
		foreach ( self::fields() as $key => $f ) {
			if ( empty( $opts[ $key ] ) ) { continue; }
			foreach ( $f[1] as $var ) {
				$decls[] = $var . ':' . $opts[ $key ];
			}
		}
		if ( ! $decls ) { return; }
		echo '<style id="esc-colors-vars">:root{' . esc_html( implode( ';', $decls ) ) . ';}</style>' . "\n";
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		if ( isset( $_POST['esc_colors_reset'] ) && check_admin_referer( 'esc_colors_reset' ) ) {
			delete_option( self::OPT );
			echo '<div class="notice notice-success is-dismissible"><p>Farben zurueckgesetzt — Theme-Defaults sind wieder aktiv.</p></div>';
		}
		?>
		<div class="wrap">
			<h1>Farben</h1>
			<p>Globale Marken-Farben. Leere Felder verwenden den Theme-Standard. Wirkt sofort auf der ganzen Seite (inkl. Elementor).</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_colors_group' ); ?>
				<table class="form-table"><tbody>
					<?php foreach ( self::fields() as $key => $f ) :
						$val = self::get( $key ); ?>
						<tr>
							<th scope="row"><label><?php echo esc_html( $f[0] ); ?></label></th>
							<td>
								<input type="text" class="es-color-field" name="<?php echo esc_attr( self::OPT . '[' . $key . ']' ); ?>" value="<?php echo esc_attr( $val ); ?>" data-default-color="<?php echo esc_attr( $f[2] ); ?>" />
								<p class="description">Standard: <code><?php echo esc_html( $f[2] ); ?></code><?php if ( count( $f[1] ) > 1 ) : ?> &middot; setzt <?php echo esc_html( implode( ' + ', $f[1] ) ); ?><?php endif; ?></p>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody></table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<form method="post" onsubmit="return confirm('Wirklich alle Farben auf Theme-Standard zuruecksetzen?');">
				<?php wp_nonce_field( 'esc_colors_reset' ); ?>
				<p>
					<button type="submit" name="esc_colors_reset" value="1" class="button">Auf Standard zuruecksetzen</button>
					<span style="color:#646970;margin-left:10px;font-size:13px;">Loescht alle Farb-Overrides; Theme-Defaults werden wieder angewendet.</span>
				</p>
			</form>
		</div>
		<?php
	}
}
ESC_Colors_Settings::init();
