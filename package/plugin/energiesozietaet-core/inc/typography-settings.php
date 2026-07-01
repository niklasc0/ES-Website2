<?php
/**
 * Typografie-Einstellungen — globale Font-Family + optionaler Font-Upload
 * (woff/woff2). Generiert @font-face + CSS-Variable-Override im Front-End.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Typography_Settings {

	const OPT = 'esc_typography';

	public static function init() {
		add_action( 'admin_init',  array( __CLASS__, 'register' ) );
		add_action( 'admin_menu',  array( __CLASS__, 'menu' ) );
		// @font-face früh in den Head, damit der Font sofort vorgeladen wird
		add_action( 'wp_head',     array( __CLASS__, 'print_font_face' ), 5 );
		// Globale Schriftgrößen-Overrides (drei Standardgrößen) — nach dem Theme-CSS
		add_action( 'wp_head',     array( __CLASS__, 'print_size_vars' ), 100 );
		// Overrides im FOOTER nach Elementor — so gewinnen sie in der CSS-
		// Cascade immer, egal wie spät Elementor nachlädt
		add_action( 'wp_footer',   array( __CLASS__, 'print_overrides' ), 99999 );
		// Uploads: woff/woff2 erlauben
		add_filter( 'upload_mimes', array( __CLASS__, 'allow_font_mimes' ) );
		add_filter( 'wp_check_filetype_and_ext', array( __CLASS__, 'check_font_ext' ), 10, 4 );
	}

	public static function defaults() {
		return array(
			'font_family'  => '', // leer → System-Default (Inter fallback)
			'font_woff2'   => 0,  // Attachment-ID
			'font_woff'    => 0,
			'font_weight'  => '400',
			'font_style'   => 'normal',
			// Die Standard-Schriftgrößen (global). Werte in px.
			'fs_heading_feature' => 30, // Feature-/Pillar-Karten (Home-Beratungsfelder, Benefits)
			'fs_heading'   => 20, // Karten-/Unterüberschriften
			'fs_body'      => 16, // Inhaltstext
			'fs_meta'      => 13, // Hairline / Datum / Meta
			'fs_eyebrow'   => 11, // Eyebrow / Label (uppercase)
		);
	}

	public static function get( $key = null ) {
		$opts = array_merge( self::defaults(), (array) get_option( self::OPT, array() ) );
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	public static function allow_font_mimes( $mimes ) {
		$mimes['woff']  = 'application/font-woff';
		$mimes['woff2'] = 'application/font-woff2';
		return $mimes;
	}

	public static function check_font_ext( $data, $file, $filename, $mimes ) {
		$ext = pathinfo( $filename, PATHINFO_EXTENSION );
		if ( in_array( strtolower( $ext ), array( 'woff', 'woff2' ), true ) ) {
			$data['ext']             = $ext;
			$data['type']            = 'woff2' === strtolower( $ext ) ? 'application/font-woff2' : 'application/font-woff';
			$data['proper_filename'] = $filename;
		}
		return $data;
	}

	public static function register() {
		register_setting( 'esc_typo_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$d = self::defaults();
		return array(
			'font_family' => sanitize_text_field( $input['font_family'] ?? $d['font_family'] ),
			'font_woff2'  => (int) ( $input['font_woff2'] ?? 0 ),
			'font_woff'   => (int) ( $input['font_woff'] ?? 0 ),
			'font_weight' => sanitize_text_field( $input['font_weight'] ?? $d['font_weight'] ),
			'font_style'  => in_array( $input['font_style'] ?? '', array( 'normal', 'italic' ), true ) ? $input['font_style'] : 'normal',
			'fs_heading_feature' => max( 16, min( 72, (int) ( $input['fs_heading_feature'] ?? $d['fs_heading_feature'] ) ) ),
			'fs_heading'  => max( 12, min( 60, (int) ( $input['fs_heading'] ?? $d['fs_heading'] ) ) ),
			'fs_body'     => max( 10, min( 32, (int) ( $input['fs_body']    ?? $d['fs_body'] ) ) ),
			'fs_meta'     => max( 8,  min( 24, (int) ( $input['fs_meta']    ?? $d['fs_meta'] ) ) ),
			'fs_eyebrow'  => max( 8,  min( 20, (int) ( $input['fs_eyebrow'] ?? $d['fs_eyebrow'] ) ) ),
		);
	}

	/** Gibt die globalen Schriftgrößen als :root-Override aus (nur was vom
	 *  Theme-Default abweicht). Läuft im wp_head nach dem Theme-Stylesheet;
	 *  überschreibt nur die CSS-Variablen — pro Widget in Elementor gesetzte
	 *  Größen (explizite px am Element) gewinnen weiterhin. */
	public static function print_size_vars() {
		$o    = self::get();
		$map  = array(
			'--es-fs-heading-feature' => array( 'fs_heading_feature', 30 ),
			'--es-fs-heading-sub' => array( 'fs_heading', 20 ),
			'--es-fs-body'        => array( 'fs_body',    16 ),
			'--es-fs-meta'        => array( 'fs_meta',    13 ),
			'--es-fs-eyebrow'     => array( 'fs_eyebrow', 11 ),
		);
		$decl = '';
		foreach ( $map as $var => $info ) {
			$val = (int) $o[ $info[0] ];
			if ( $val && $val !== (int) $info[1] ) {
				$decl .= $var . ':' . $val . 'px;';
			}
		}
		if ( $decl ) {
			echo '<style id="esc-typo-sizes">:root{' . $decl . '}</style>';
		}
	}

	public static function menu() {
		add_submenu_page(
			'es-theme-options',
			'Typografie',
			'Typografie',
			'manage_options',
			'esc-typography',
			array( __CLASS__, 'render' ),
			10
		);
	}

	/** Interne Helfer: Font-Stack bauen + @font-face + Override-CSS. */
	protected static function build_stack( $family ) {
		return '"' . esc_html( $family ) . '",-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif';
	}

	/** Wird im wp_head bei Priorität 5 ausgegeben — Font-Face möglichst früh. */
	public static function print_font_face() {
		$opts = self::get();
		if ( empty( $opts['font_family'] ) ) { return; }
		$family = $opts['font_family'];
		$src = array();
		if ( $opts['font_woff2'] ) {
			$url = wp_get_attachment_url( (int) $opts['font_woff2'] );
			if ( $url ) { $src[] = 'url(' . esc_url( $url ) . ') format("woff2")'; }
		}
		if ( $opts['font_woff'] ) {
			$url = wp_get_attachment_url( (int) $opts['font_woff'] );
			if ( $url ) { $src[] = 'url(' . esc_url( $url ) . ') format("woff")'; }
		}
		echo '<style id="esc-typography-face">';
		if ( $src ) {
			echo '@font-face{font-family:"' . esc_html( $family ) . '";font-style:' . esc_html( $opts['font_style'] ) . ';font-weight:' . esc_html( $opts['font_weight'] ) . ';font-display:swap;src:' . implode( ',', $src ) . ';}';
			// Preload-Hint für WOFF2 — lädt Font parallel zum ersten Paint
			$p_url = wp_get_attachment_url( (int) $opts['font_woff2'] );
			if ( $p_url ) {
				echo '</style>';
				echo '<link rel="preload" href="' . esc_url( $p_url ) . '" as="font" type="font/woff2" crossorigin="anonymous">';
				echo '<style>';
			}
		}
		// Die Variablen-Defaults für --es-font-sans setzen wir auch hier, damit
		// ohne FOUT-Flash direkt die richtige Schrift angewendet wird.
		$stack = self::build_stack( $family );
		echo 'html,:root{--es-font-sans:' . $stack . ' !important;--es-font-display:' . $stack . ' !important;}';
		echo 'html{--e-global-typography-primary-font-family:"' . esc_html( $family ) . '" !important;--e-global-typography-secondary-font-family:"' . esc_html( $family ) . '" !important;--e-global-typography-text-font-family:"' . esc_html( $family ) . '" !important;--e-global-typography-accent-font-family:"' . esc_html( $family ) . '" !important;}';
		echo 'body,h1,h2,h3,h4,h5,h6,p,a,button,input,select,textarea,blockquote,.elementor-heading-title,.elementor-widget-container,.elementor-widget-text-editor,.elementor-widget-text-editor p,.elementor-button,.es-stage,.es-stage *,.es-brand,.es-nav,.es-footer,.es-footer *{font-family:' . $stack . ' !important;}';
		echo '</style>';
	}

	/** Wird im wp_footer mit Priorität 99999 ausgegeben — nach Elementor's
	 *  Post-CSS-Ladephase und allen per JS nachgeladenen Stylesheets. */
	public static function print_overrides() {
		$opts = self::get();
		if ( empty( $opts['font_family'] ) ) { return; }
		$family = $opts['font_family'];
		$stack  = self::build_stack( $family );
		echo '<style id="esc-typography-override">';
		echo 'html,:root{--es-font-sans:' . $stack . ' !important;--es-font-display:' . $stack . ' !important;}';
		echo 'html{--e-global-typography-primary-font-family:"' . esc_html( $family ) . '" !important;--e-global-typography-secondary-font-family:"' . esc_html( $family ) . '" !important;--e-global-typography-text-font-family:"' . esc_html( $family ) . '" !important;--e-global-typography-accent-font-family:"' . esc_html( $family ) . '" !important;}';
		echo 'body,h1,h2,h3,h4,h5,h6,p,a,button,input,select,textarea,blockquote,.elementor-heading-title,.elementor-widget-container,.elementor-widget-text-editor,.elementor-widget-text-editor p,.elementor-button,.es-stage,.es-stage *,.es-brand,.es-nav,.es-footer,.es-footer *{font-family:' . $stack . ' !important;}';
		echo '</style>';
	}

	protected static function media_upload_field( $name, $label, $accept = '.woff2,.woff' ) {
		$val = (int) self::get( $name );
		$url = $val ? wp_get_attachment_url( $val ) : '';
		$fn  = $val ? basename( wp_get_attachment_url( $val ) ) : '';
		echo '<tr><th scope="row"><label>' . esc_html( $label ) . '</label></th><td>';
		echo '<input type="hidden" id="esc_typo_' . esc_attr( $name ) . '" name="' . esc_attr( self::OPT . '[' . $name . ']' ) . '" value="' . esc_attr( $val ) . '" />';
		echo '<span id="esc_typo_' . esc_attr( $name ) . '_preview" style="font-family:monospace;font-size:13px;color:#5A6577;margin-right:12px;">' . esc_html( $fn ? $fn : '— noch keine Datei —' ) . '</span>';
		echo '<button type="button" class="button esc-typo-upload" data-target="esc_typo_' . esc_attr( $name ) . '" data-preview="esc_typo_' . esc_attr( $name ) . '_preview" data-accept="' . esc_attr( $accept ) . '">Datei wählen</button> ';
		echo '<button type="button" class="button esc-typo-clear" data-target="esc_typo_' . esc_attr( $name ) . '" data-preview="esc_typo_' . esc_attr( $name ) . '_preview">Entfernen</button>';
		echo '</td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		wp_enqueue_media();
		// Reset-Action
		if ( isset( $_POST['esc_typo_reset'] ) && check_admin_referer( 'esc_typo_reset' ) ) {
			delete_option( self::OPT );
			echo '<div class="notice notice-success is-dismissible"><p>Typografie zurückgesetzt — Theme-Default (Inter) ist wieder aktiv.</p></div>';
		}
		?>
		<div class="wrap">
			<h1>Typografie</h1>
			<p>Setzt die globale Schrift der Website. Wenn kein Font-Name eingetragen ist, greift der Theme-Default (Inter / System-Stack). Optional kannst Du eigene Font-Dateien (.woff2 / .woff) hochladen.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_typo_group' ); ?>
				<table class="form-table"><tbody>
					<tr><th scope="row"><label>Font-Name</label></th><td>
						<input type="text" name="<?php echo esc_attr( self::OPT . '[font_family]' ); ?>" value="<?php echo esc_attr( self::get( 'font_family' ) ); ?>" placeholder="z.B. „Söhne" oder „Inter"" style="width:100%;max-width:420px;" />
						<p class="description">Name wie Du ihn in CSS als <code>font-family</code> verwendest. Wenn der Name zu einer Web-Safe-Schrift (<em>Arial</em>, <em>Georgia</em>) passt, reicht das. Für eigene Fonts bitte zusätzlich die Dateien unten hochladen.</p>
					</td></tr>
					<?php self::media_upload_field( 'font_woff2', 'Font-Datei (.woff2) — empfohlen' ); ?>
					<?php self::media_upload_field( 'font_woff',  'Font-Datei (.woff) — Fallback' ); ?>
					<tr><th scope="row"><label>Schriftstärke</label></th><td>
						<input type="text" name="<?php echo esc_attr( self::OPT . '[font_weight]' ); ?>" value="<?php echo esc_attr( self::get( 'font_weight' ) ); ?>" style="max-width:180px;" placeholder="400 / 500 / 700" />
						<p class="description">Bei Variable-Fonts <code>100 900</code>, bei einzelnen Schnitten die Zahl (400 = Regular, 500 = Medium, 700 = Bold).</p>
					</td></tr>
					<tr><th scope="row"><label>Schriftstil</label></th><td>
						<select name="<?php echo esc_attr( self::OPT . '[font_style]' ); ?>">
							<option value="normal" <?php selected( self::get( 'font_style' ), 'normal' ); ?>>Normal</option>
							<option value="italic" <?php selected( self::get( 'font_style' ), 'italic' ); ?>>Kursiv</option>
						</select>
					</td></tr>
				</tbody></table>

				<h2 style="margin-top:32px;">Standard-Schriftgrößen</h2>
				<p>Die Standardgrößen der Website. Änderungen hier werden überall automatisch übernommen (Inhaltstexte, Metazeilen, Karten-/Unterüberschriften). Große Hero-/Sektions-Titel skalieren eigenständig und lassen sich pro Widget in Elementor anpassen.</p>
				<table class="form-table"><tbody>
					<tr><th scope="row"><label>Feature-Überschrift <span style="color:#787c82;font-weight:400;">(große Feld-/Pillar-Karten)</span></label></th><td>
						<input type="number" name="<?php echo esc_attr( self::OPT . '[fs_heading_feature]' ); ?>" value="<?php echo esc_attr( self::get( 'fs_heading_feature' ) ); ?>" min="16" max="72" step="1" style="width:90px;"> px
						<p class="description">Standard: 30 px. Home-Beratungsfeld-Karten, „Warum wir"-Benefits, Philosophie-Pillars.</p>
					</td></tr>
					<tr><th scope="row"><label>Überschrift <span style="color:#787c82;font-weight:400;">(Karten &amp; Unterüberschriften)</span></label></th><td>
						<input type="number" name="<?php echo esc_attr( self::OPT . '[fs_heading]' ); ?>" value="<?php echo esc_attr( self::get( 'fs_heading' ) ); ?>" min="12" max="60" step="1" style="width:90px;"> px
						<p class="description">Standard: 20 px.</p>
					</td></tr>
					<tr><th scope="row"><label>Inhaltstext</label></th><td>
						<input type="number" name="<?php echo esc_attr( self::OPT . '[fs_body]' ); ?>" value="<?php echo esc_attr( self::get( 'fs_body' ) ); ?>" min="10" max="32" step="1" style="width:90px;"> px
						<p class="description">Standard: 16 px. Fließtext, Listen, Beschreibungen, Kontaktangaben.</p>
					</td></tr>
					<tr><th scope="row"><label>Hairline / Meta <span style="color:#787c82;font-weight:400;">(Datum, Breadcrumb …)</span></label></th><td>
						<input type="number" name="<?php echo esc_attr( self::OPT . '[fs_meta]' ); ?>" value="<?php echo esc_attr( self::get( 'fs_meta' ) ); ?>" min="8" max="24" step="1" style="width:90px;"> px
						<p class="description">Standard: 13 px.</p>
					</td></tr>
					<tr><th scope="row"><label>Eyebrow / Label <span style="color:#787c82;font-weight:400;">(kleine Großbuchstaben-Labels)</span></label></th><td>
						<input type="number" name="<?php echo esc_attr( self::OPT . '[fs_eyebrow]' ); ?>" value="<?php echo esc_attr( self::get( 'fs_eyebrow' ) ); ?>" min="8" max="20" step="1" style="width:90px;"> px
						<p class="description">Standard: 11 px.</p>
					</td></tr>
				</tbody></table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<form method="post" onsubmit="return confirm('Wirklich auf Theme-Standard zurücksetzen? Alle Font-Einstellungen oben werden gelöscht.');">
				<?php wp_nonce_field( 'esc_typo_reset' ); ?>
				<p>
					<button type="submit" name="esc_typo_reset" value="1" class="button">Auf Standard zurücksetzen</button>
					<span style="color:#5A6577;margin-left:10px;font-size:13px;">Löscht alle Typo-Overrides. Theme-Default (Inter) wird wieder angewendet.</span>
				</p>
			</form>
		</div>
		<script>
		(function(){
			var fr = null;
			document.querySelectorAll('.esc-typo-upload').forEach(function(btn){
				btn.addEventListener('click', function(e){
					e.preventDefault();
					var targetId = btn.getAttribute('data-target');
					var previewId = btn.getAttribute('data-preview');
					fr = wp.media({ title:'Font-Datei wählen', multiple:false, library:{ type:['application/font-woff','application/font-woff2'] } });
					fr.on('select', function(){
						var att = fr.state().get('selection').first().toJSON();
						document.getElementById(targetId).value = att.id;
						document.getElementById(previewId).textContent = att.filename || att.url;
					});
					fr.open();
				});
			});
			document.querySelectorAll('.esc-typo-clear').forEach(function(btn){
				btn.addEventListener('click', function(e){
					e.preventDefault();
					document.getElementById(btn.getAttribute('data-target')).value = '0';
					document.getElementById(btn.getAttribute('data-preview')).textContent = '— noch keine Datei —';
				});
			});
		})();
		</script>
		</div>
		<?php
	}
}
ESC_Typography_Settings::init();
