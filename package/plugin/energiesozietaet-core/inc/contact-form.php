<?php
/**
 * Kontaktformular – Shortcode [es_kontakt_form] + Submit-Handler + Settings.
 *
 * Backend-konfigurierbar: Empfänger, Subject, Erfolgs-/Fehlertext, Pflichtfelder,
 * Themen-Optionen, Aufbewahrungsfrist. Jede Anfrage wird vor dem Versand im
 * Backend gespeichert (ESC_Contact_Inbox). Bewusst ohne Nonce: das Host-CDN
 * cacht die Kontaktseite bis zu 31 Tage, ein Nonce im gecachten HTML ist nach
 * spätestens 24 Stunden ungültig. Spam-Erkennung über ein unsichtbares
 * Prüffeld und ein per JavaScript gesetztes Zeitfeld; nur Anfragen mit beiden
 * Signalen werden aussortiert (gespeichert, nicht einzeln gemailt, aber in
 * einer täglichen Zusammenfassung gemeldet). Die Anfrage-Mail enthält nur die
 * Angaben des Absenders, weil sie beim Antworten im Verlauf mitzitiert wird.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Contact_Form {

	const OPT      = 'esc_contact';
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
			'retention_days' => 0,
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
			'retention_days' => max( 0, min( 3650, (int) ( $input['retention_days'] ?? 0 ) ) ),
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
			<p>Jede Anfrage wird zusätzlich zur E-Mail im Backend gespeichert: <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=es_anfrage' ) ); ?>">Kontaktanfragen ansehen</a>. Als Spam aussortierte Anfragen werden nicht einzeln gemailt, sondern täglich um 7 Uhr gesammelt zur Kontrolle an die Empfänger-Adresse geschickt (nur an Tagen, an denen etwas aussortiert wurde).</p>
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
					<tr><th scope="row"><label>Aufbewahrung gespeicherter Anfragen</label></th><td>
						<input type="number" min="0" max="3650" name="<?php echo esc_attr( self::OPT . '[retention_days]' ); ?>" value="<?php echo esc_attr( (int) self::get( 'retention_days' ) ); ?>" style="width:100px;"> Tage
						<p class="description">Gespeicherte Anfragen werden nach dieser Frist automatisch gelöscht; 0 = nie automatisch löschen. Spam wird unabhängig davon nach 30 Tagen gelöscht.</p>
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

	/** Pflichtfelder: Schlüssel => Bezeichnung (auch für die Fehlermeldung). */
	const REQUIRED = array(
		'vorname'   => 'Vorname',
		'nachname'  => 'Nachname',
		'email'     => 'E-Mail',
		'nachricht' => 'Nachricht',
		'consent'   => 'Datenschutz-Zustimmung',
	);

	/** Submit-Handler: speichert die Anfrage, versendet die Mail, leitet mit Statusparameter zurück. */
	public static function maybe_handle_submit() {
		if ( empty( $_POST['esc_contact_submit'] ) ) { return; }

		$opts = self::get();
		$d    = array(
			'vorname'   => sanitize_text_field( wp_unslash( $_POST['vorname']   ?? '' ) ),
			'nachname'  => sanitize_text_field( wp_unslash( $_POST['nachname']  ?? '' ) ),
			'email'     => sanitize_email(      wp_unslash( $_POST['email']     ?? '' ) ),
			'org'       => sanitize_text_field( wp_unslash( $_POST['org']       ?? '' ) ),
			'thema'     => sanitize_text_field( wp_unslash( $_POST['thema']     ?? '' ) ),
			'nachricht' => sanitize_textarea_field( wp_unslash( $_POST['nachricht'] ?? '' ) ),
			'sprache'   => false !== strpos( (string) wp_get_referer(), '/en/' ) ? 'en' : 'de',
		);
		$consent = ! empty( $_POST['consent'] );

		$missing = array();
		foreach ( array_keys( self::REQUIRED ) as $k ) {
			if ( 'consent' === $k ) {
				if ( ! $consent ) { $missing[] = $k; }
			} elseif ( 'email' === $k ) {
				if ( ! is_email( $d['email'] ) ) { $missing[] = $k; }
			} elseif ( '' === $d[ $k ] ) {
				$missing[] = $k;
			}
		}
		if ( $missing ) { self::redirect_back( 'unvollstaendig', $missing ); }

		// Spam-Signale. Erst beide zusammen gelten als Bot; ein einzelnes
		// Signal (z. B. Browser-Autofill im Honeypot, JavaScript blockiert)
		// wird nur markiert, die Anfrage geht trotzdem normal raus.
		$signals = array();
		// Prüffeld ohne sprechenden Namen, damit kein Browser-Autofill es befüllt
		// ("website" nur noch für Seiten aus dem Cache vor 1.6.53).
		$hp = (string) ( $_POST['esc_feld'] ?? ( $_POST['website'] ?? '' ) );
		if ( '' !== trim( $hp ) ) { $signals[] = 'unsichtbares Prüffeld ausgefüllt'; }
		$human = trim( (string) ( $_POST['esc_human'] ?? '' ) );
		if ( '' === $human || ! ctype_digit( $human ) ) {
			$signals[] = 'ohne JavaScript abgesendet';
		} elseif ( (int) $human < 2 ) {
			$signals[] = 'in unter 2 Sekunden abgesendet';
		}
		$is_spam = count( $signals ) >= 2;

		$id = class_exists( 'ESC_Contact_Inbox' ) ? ESC_Contact_Inbox::store( $d, $is_spam ? 'spam' : 'neu', $signals ) : 0;
		if ( $is_spam ) { self::redirect_back( 'gesendet' ); }

		$subject = strtr( (string) $opts['subject'], array(
			'[thema]' => $d['thema'] ?: 'Sonstiges',
			'[name]'  => trim( $d['vorname'] . ' ' . $d['nachname'] ),
		) );

		$body  = "Neue Nachricht über das Kontaktformular:\n\n";
		$body .= 'Name:        ' . $d['vorname'] . ' ' . $d['nachname'] . "\n";
		$body .= 'E-Mail:      ' . $d['email'] . "\n";
		if ( $d['org'] )   { $body .= 'Unternehmen: ' . $d['org'] . "\n"; }
		if ( $d['thema'] ) { $body .= 'Thema:       ' . $d['thema'] . "\n"; }
		$body .= "\nNachricht:\n" . $d['nachricht'] . "\n";

		$headers = array(
			'Content-Type: text/plain; charset=UTF-8',
			'Reply-To: ' . sprintf( '%s %s <%s>', $d['vorname'], $d['nachname'], $d['email'] ),
		);
		$fail = '';
		$catch = function ( $err ) use ( &$fail ) { $fail = $err->get_error_message(); };
		add_action( 'wp_mail_failed', $catch );
		$ok = wp_mail( $opts['recipient'], $subject, $body, $headers );
		remove_action( 'wp_mail_failed', $catch );
		if ( $id && class_exists( 'ESC_Contact_Inbox' ) ) { ESC_Contact_Inbox::set_mail_result( $id, $ok, $fail ); }

		// Gespeichert gilt als eingegangen, auch wenn die Mail scheitert.
		self::redirect_back( ( $ok || $id ) ? 'gesendet' : 'fehler' );
	}

	/**
	 * Zurück zur Formularseite. Der Status steht als URL-Parameter in der
	 * Adresse und nicht in Cookie/Transient: so stimmt die Rückmeldung auch
	 * dann, wenn das CDN die Seite aus dem Cache ausliefert.
	 */
	protected static function redirect_back( $status, array $fields = array() ) {
		$ref = wp_get_referer();
		if ( ! $ref ) { $ref = home_url( '/kontakt/' ); }
		$ref  = remove_query_arg( array( 'anfrage', 'felder', 'esc_status' ), $ref );
		$args = array( 'anfrage' => $status );
		if ( $fields ) { $args['felder'] = implode( ',', $fields ); }
		wp_safe_redirect( add_query_arg( $args, $ref ) . '#' . self::FORM_ID, 303 );
		exit;
	}

	/** Rückmeldung aus dem URL-Parameter. */
	protected static function status_message( array $opts ) {
		$status = isset( $_GET['anfrage'] ) ? sanitize_key( wp_unslash( $_GET['anfrage'] ) ) : '';
		if ( 'gesendet' === $status ) { return array( 'type' => 'success', 'msg' => $opts['success_msg'] ); }
		if ( 'fehler' === $status )   { return array( 'type' => 'error', 'msg' => $opts['error_msg'] ); }
		if ( 'unvollstaendig' === $status ) {
			$keys   = array_filter( array_map( 'sanitize_key', explode( ',', (string) wp_unslash( $_GET['felder'] ?? '' ) ) ) );
			$labels = array_values( array_intersect_key( self::REQUIRED, array_flip( $keys ) ) );
			return array( 'type' => 'error', 'msg' => 'Bitte ausfüllen: ' . esc_html( implode( ', ', $labels ?: array_values( self::REQUIRED ) ) ) . '.' );
		}
		return null;
	}

	/** [es_kontakt_form] – Frontend-Markup. */
	public static function render() {
		$opts   = self::get();
		$flash  = self::status_message( $opts );
		$topics = array_filter( array_map( 'trim', preg_split( '/\r?\n/', (string) $opts['topics'] ) ) );
		ob_start(); ?>
		<form id="<?php echo esc_attr( self::FORM_ID ); ?>" class="es-kontakt-form" method="post" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( $flash ) : ?>
				<div class="es-kontakt-form__flash es-kontakt-form__flash--<?php echo esc_attr( $flash['type'] ); ?>">
					<?php echo wp_kses_post( $flash['msg'] ); ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="esc_contact_submit" value="1">
			<input type="hidden" name="esc_human" value="">
			<input type="text" name="esc_feld" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;height:0;width:0;opacity:0;" aria-hidden="true">

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
		<script>
		(function () {
			var f = document.getElementById('<?php echo esc_js( self::FORM_ID ); ?>');
			if (!f) { return; }
			var t0 = Date.now();
			f.addEventListener('submit', function () {
				var h = f.querySelector('input[name="esc_human"]');
				if (h) { h.value = String(Math.round((Date.now() - t0) / 1000)); }
			});
		})();
		</script>
		<?php
		return ob_get_clean();
	}
}
ESC_Contact_Form::init();
