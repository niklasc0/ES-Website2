<?php
/**
 * Kontaktformular – Shortcode [es_kontakt_form] + Submit-Handler + Settings.
 *
 * Backend-konfigurierbar: Empfänger, Subject, Erfolgs-/Fehlertext, Pflichtfelder,
 * Themen-Optionen. Anti-Spam: Honeypot + Nonce + simples Time-Trap.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Contact_Form {

	const OPT      = 'esc_contact';
	const NONCE    = 'esc_contact_form';
	const FORM_ID  = 'es-kontakt-form';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'init',       array( __CLASS__, 'maybe_handle_submit' ) );
		add_shortcode( 'es_kontakt_form', array( __CLASS__, 'render' ) );
	}

	public static function defaults() {
		return array(
			'recipient'    => get_option( 'admin_email' ),
			'subject'      => 'Neue Anfrage über das Kontaktformular: [thema]',
			'success_msg'  => 'Vielen Dank – Ihre Nachricht ist bei uns eingegangen. Wir melden uns bei Ihnen.',
			'error_msg'    => 'Beim Senden ist etwas schief gelaufen. Bitte versuchen Sie es erneut oder schreiben Sie direkt an info@energiesozietaet.de.',
			'consent_text' => 'Mit dem Absenden stimmen Sie unserer <a href="/datenschutzerklaerung/">Datenschutzerklärung</a> zu.',
			'topics'       => "Rechtsberatung\nSteuerberatung\nUnternehmensberatung\nKarriere\nPresse\nSonstiges",
			'show_company' => 1,
			'show_topic'   => 1,
		);
	}

	public static function get( $key = null ) {
		$opts = array_merge( self::defaults(), (array) get_option( self::OPT, array() ) );
		return $key ? ( $opts[ $key ] ?? '' ) : $opts;
	}

	public static function register() {
		register_setting( 'esc_contact_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		$d = self::defaults();
		return array(
			'recipient'    => sanitize_email( $input['recipient']    ?? $d['recipient'] ),
			'subject'      => sanitize_text_field( $input['subject'] ?? $d['subject'] ),
			'success_msg'  => wp_kses_post( $input['success_msg']    ?? $d['success_msg'] ),
			'error_msg'    => wp_kses_post( $input['error_msg']      ?? $d['error_msg'] ),
			'consent_text' => wp_kses_post( $input['consent_text']   ?? $d['consent_text'] ),
			'topics'       => sanitize_textarea_field( $input['topics'] ?? $d['topics'] ),
			'show_company' => empty( $input['show_company'] ) ? 0 : 1,
			'show_topic'   => empty( $input['show_topic'] )   ? 0 : 1,
		);
	}

	public static function menu() {
		add_submenu_page(
			'es-theme-options',
			'Kontaktformular',
			'Kontaktformular',
			'manage_options',
			'esc-contact',
			array( __CLASS__, 'render_settings' ),
			50
		);
	}

	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>Kontaktformular</h1>
			<p>Konfiguration für das Formular auf der Kontakt-Seite. Das Formular wird über den Shortcode <code>[es_kontakt_form]</code> eingebunden.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_contact_group' ); ?>
				<table class="form-table"><tbody>
					<tr><th scope="row"><label>Empfänger-E-Mail</label></th><td>
						<input type="email" name="<?php echo esc_attr( self::OPT . '[recipient]' ); ?>" value="<?php echo esc_attr( self::get( 'recipient' ) ); ?>" style="width:100%;max-width:480px;" />
						<p class="description">Wohin Anfragen geschickt werden.</p>
					</td></tr>
					<tr><th scope="row"><label>Betreff-Template</label></th><td>
						<input type="text" name="<?php echo esc_attr( self::OPT . '[subject]' ); ?>" value="<?php echo esc_attr( self::get( 'subject' ) ); ?>" style="width:100%;max-width:480px;" />
						<p class="description">Platzhalter: <code>[thema]</code>, <code>[name]</code></p>
					</td></tr>
					<tr><th scope="row"><label>Erfolgsmeldung</label></th><td>
						<textarea name="<?php echo esc_attr( self::OPT . '[success_msg]' ); ?>" rows="2" style="width:100%;max-width:640px;"><?php echo esc_textarea( self::get( 'success_msg' ) ); ?></textarea>
					</td></tr>
					<tr><th scope="row"><label>Fehlermeldung</label></th><td>
						<textarea name="<?php echo esc_attr( self::OPT . '[error_msg]' ); ?>" rows="2" style="width:100%;max-width:640px;"><?php echo esc_textarea( self::get( 'error_msg' ) ); ?></textarea>
					</td></tr>
					<tr><th scope="row"><label>Datenschutz-Hinweis</label></th><td>
						<textarea name="<?php echo esc_attr( self::OPT . '[consent_text]' ); ?>" rows="2" style="width:100%;max-width:640px;"><?php echo esc_textarea( self::get( 'consent_text' ) ); ?></textarea>
						<p class="description">HTML erlaubt (z.B. Link auf <code>/datenschutzerklaerung/</code>).</p>
					</td></tr>
					<tr><th scope="row"><label>Themen-Optionen</label></th><td>
						<textarea name="<?php echo esc_attr( self::OPT . '[topics]' ); ?>" rows="6" style="width:100%;max-width:480px;"><?php echo esc_textarea( self::get( 'topics' ) ); ?></textarea>
						<p class="description">Eine Option pro Zeile – wird als Pill-Auswahl gerendert.</p>
					</td></tr>
					<tr><th scope="row">Optionale Felder</th><td>
						<label><input type="checkbox" name="<?php echo esc_attr( self::OPT . '[show_company]' ); ?>" value="1" <?php checked( self::get( 'show_company' ), 1 ); ?>> Feld „Unternehmen / Organisation" anzeigen</label><br>
						<label><input type="checkbox" name="<?php echo esc_attr( self::OPT . '[show_topic]' ); ?>" value="1" <?php checked( self::get( 'show_topic' ), 1 ); ?>> Themen-Auswahl anzeigen</label>
					</td></tr>
				</tbody></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/** Submit-Handler: prüft Nonce, Honeypot, sendet Mail, redirect mit Flash. */
	public static function maybe_handle_submit() {
		if ( empty( $_POST['esc_contact_submit'] ) ) { return; }
		if ( ! isset( $_POST['esc_contact_nonce'] ) || ! wp_verify_nonce( $_POST['esc_contact_nonce'], self::NONCE ) ) { return; }

		// Honeypot: das Feld 'website' muss leer sein
		if ( ! empty( $_POST['website'] ) ) {
			self::flash( 'error', self::get( 'error_msg' ) );
			self::redirect_back();
		}
		// Time-Trap: zu schnell abgesendet → Bot
		$started = (int) ( $_POST['esc_started'] ?? 0 );
		if ( $started && ( time() - $started ) < 2 ) {
			self::flash( 'error', self::get( 'error_msg' ) );
			self::redirect_back();
		}

		$opts    = self::get();
		$vorname = sanitize_text_field( wp_unslash( $_POST['vorname']   ?? '' ) );
		$nachn   = sanitize_text_field( wp_unslash( $_POST['nachname']  ?? '' ) );
		$email   = sanitize_email(      wp_unslash( $_POST['email']     ?? '' ) );
		$org     = sanitize_text_field( wp_unslash( $_POST['org']       ?? '' ) );
		$thema   = sanitize_text_field( wp_unslash( $_POST['thema']     ?? '' ) );
		$msg     = sanitize_textarea_field( wp_unslash( $_POST['nachricht'] ?? '' ) );
		$consent = ! empty( $_POST['consent'] );

		$errors = array();
		if ( ! $vorname ) { $errors[] = 'Vorname'; }
		if ( ! $nachn )   { $errors[] = 'Nachname'; }
		if ( ! is_email( $email ) ) { $errors[] = 'E-Mail'; }
		if ( ! $msg )     { $errors[] = 'Nachricht'; }
		if ( ! $consent ) { $errors[] = 'Datenschutz-Zustimmung'; }
		if ( $errors ) {
			self::flash( 'error', 'Bitte ausfüllen: ' . implode( ', ', $errors ) . '.' );
			self::redirect_back();
		}

		$subject = strtr( (string) $opts['subject'], array(
			'[thema]' => $thema ?: 'Sonstiges',
			'[name]'  => trim( $vorname . ' ' . $nachn ),
		) );

		$body  = "Neue Nachricht über das Kontaktformular:\n\n";
		$body .= "Name:        $vorname $nachn\n";
		$body .= "E-Mail:      $email\n";
		if ( $org )   { $body .= "Unternehmen: $org\n"; }
		if ( $thema ) { $body .= "Thema:       $thema\n"; }
		$body .= "\nNachricht:\n$msg\n";

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . sprintf( '%s %s <%s>', $vorname, $nachn, $email ),
		);
		$ok = wp_mail( $opts['recipient'], $subject, $body, $headers );
		if ( $ok ) {
			self::flash( 'success', $opts['success_msg'] );
		} else {
			self::flash( 'error', $opts['error_msg'] );
		}
		self::redirect_back();
	}

	protected static function flash( $type, $msg ) {
		$key = 'esc_contact_flash_' . wp_get_session_token();
		set_transient( $key, array( 'type' => $type, 'msg' => $msg ), 60 );
		setcookie( 'esc_contact_flash', $key, time() + 60, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
	}

	protected static function consume_flash() {
		if ( empty( $_COOKIE['esc_contact_flash'] ) ) { return null; }
		$key = sanitize_text_field( wp_unslash( $_COOKIE['esc_contact_flash'] ) );
		$flash = get_transient( $key );
		delete_transient( $key );
		setcookie( 'esc_contact_flash', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
		return is_array( $flash ) ? $flash : null;
	}

	protected static function redirect_back() {
		$ref = wp_get_referer();
		if ( ! $ref ) { $ref = home_url( '/kontakt/' ); }
		// Anchor zum Formular-Anker
		$ref = remove_query_arg( array( 'esc_status' ), $ref );
		wp_safe_redirect( $ref . '#' . self::FORM_ID );
		exit;
	}

	/** [es_kontakt_form] – Frontend-Markup. */
	public static function render() {
		$opts   = self::get();
		$flash  = self::consume_flash();
		$topics = array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) $opts['topics'] ) ) );
		ob_start(); ?>
		<form id="<?php echo esc_attr( self::FORM_ID ); ?>" class="es-kontakt-form" method="post" action="<?php echo esc_url( home_url() ); ?>#<?php echo esc_attr( self::FORM_ID ); ?>" novalidate>
			<?php if ( $flash ) : ?>
				<div class="es-kontakt-form__flash es-kontakt-form__flash--<?php echo esc_attr( $flash['type'] ); ?>">
					<?php echo wp_kses_post( $flash['msg'] ); ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="esc_contact_submit" value="1">
			<input type="hidden" name="esc_started" value="<?php echo (int) time(); ?>">
			<?php wp_nonce_field( self::NONCE, 'esc_contact_nonce' ); ?>
			<input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;height:0;width:0;opacity:0;" aria-hidden="true">

			<div class="es-kontakt-form__row">
				<label><span>Vorname *</span><input type="text" name="vorname" required></label>
				<label><span>Nachname *</span><input type="text" name="nachname" required></label>
			</div>
			<label><span>E-Mail *</span><input type="email" name="email" required></label>
			<?php if ( $opts['show_company'] ) : ?>
				<label><span>Unternehmen / Organisation</span><input type="text" name="org"></label>
			<?php endif; ?>
			<?php if ( $opts['show_topic'] && $topics ) : ?>
				<div class="es-kontakt-form__radio-group">
					<span class="es-kontakt-form__group-label">Betreff</span>
					<div class="es-kontakt-form__pills">
						<?php foreach ( $topics as $i => $t ) : ?>
							<label><input type="radio" name="thema" value="<?php echo esc_attr( $t ); ?>"<?php checked( 0, $i ); ?>> <?php echo esc_html( $t ); ?></label>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
			<label><span>Ihre Nachricht *</span><textarea name="nachricht" rows="5" required></textarea></label>
			<label class="es-kontakt-form__consent">
				<input type="checkbox" name="consent" value="1" required>
				<span><?php echo wp_kses_post( $opts['consent_text'] ); ?></span>
			</label>
			<div class="es-kontakt-form__submit">
				<button class="es-btn es-btn--primary" type="submit">Nachricht senden →</button>
			</div>
		</form>
		<?php
		return ob_get_clean();
	}
}
ESC_Contact_Form::init();
