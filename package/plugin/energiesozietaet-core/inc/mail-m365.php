<?php
/**
 * Mailversand über Microsoft 365 (Microsoft Graph, App-Anmeldung).
 *
 * Alle Mails der Website (Kontaktformular, Tageszusammenfassung,
 * WordPress-Systemmails) gehen über ein echtes Postfach der Kanzlei statt
 * über das Mail-Relais des Hosters: Sie sind damit für SPF/DKIM/DMARC
 * echt und liegen zusätzlich unter „Gesendete Elemente“ dieses Postfachs.
 * Scheitert der Versand über Microsoft 365, nimmt WordPress den bisherigen
 * Weg; der Fehler erscheint unter Theme Options → Mailversand.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Mail_M365 {

	const OPT        = 'esc_mail_m365';
	const STATUS     = 'esc_mail_m365_status';
	const TOKEN      = 'esc_m365_token';
	const MAX_ATTACH = 3145728;

	/** Weg der zuletzt versendeten Mail: 'm365', 'fallback' oder '' (Microsoft 365 nicht eingerichtet). */
	public static $last_route = '';
	/** Fehlermeldung von Microsoft 365, falls der Ersatzweg genommen wurde. */
	public static $last_error = '';

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_esc_m365_test', array( __CLASS__, 'handle_test' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_filter( 'pre_wp_mail', array( __CLASS__, 'pre_wp_mail' ), 20, 2 );
	}

	public static function get( $key = null ) {
		$o = array_merge(
			array( 'enabled' => 0, 'tenant' => '', 'client_id' => '', 'client_secret' => '', 'sender' => '', 'secret_expiry' => '' ),
			(array) get_option( self::OPT, array() )
		);
		return $key ? ( $o[ $key ] ?? '' ) : $o;
	}

	public static function configured() {
		$o = self::get();
		return $o['enabled'] && $o['tenant'] && $o['client_id'] && $o['client_secret'] && is_email( $o['sender'] );
	}

	public static function register() {
		register_setting( 'esc_mail_m365_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $in ) {
		$old    = self::get();
		$secret = trim( (string) ( $in['client_secret'] ?? '' ) );
		delete_transient( self::TOKEN );
		$expiry = sanitize_text_field( (string) ( $in['secret_expiry'] ?? '' ) );
		return array(
			'enabled'       => empty( $in['enabled'] ) ? 0 : 1,
			'tenant'        => sanitize_text_field( (string) ( $in['tenant'] ?? '' ) ),
			'client_id'     => sanitize_text_field( (string) ( $in['client_id'] ?? '' ) ),
			// Leeres Feld = bisherigen Schlüssel behalten (wird nie wieder angezeigt).
			'client_secret' => '' === $secret ? $old['client_secret'] : $secret,
			'sender'        => sanitize_email( (string) ( $in['sender'] ?? '' ) ),
			'secret_expiry' => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $expiry ) ? $expiry : '',
		);
	}

	/* ------------------------------------------------------------------ */
	/* Versand                                                            */
	/* ------------------------------------------------------------------ */

	public static function pre_wp_mail( $null, $atts ) {
		if ( null !== $null ) { return $null; }
		self::$last_route = '';
		self::$last_error = '';
		if ( ! self::configured() ) { return null; }

		$result = self::send( $atts );
		if ( true === $result ) {
			self::$last_route = 'm365';
			self::remember( 'ok', '' );
			return true;
		}
		// Ersatzweg: WordPress versendet wie bisher über den Hoster.
		self::$last_route = 'fallback';
		self::$last_error = $result;
		self::remember( 'error', $result );
		return null;
	}

	/** Versendet eine wp_mail-Nachricht über Microsoft Graph. true oder Fehlermeldung. */
	public static function send( array $atts ) {
		$h = self::parse_headers( $atts['headers'] ?? array() );
		$to = self::recipients( is_array( $atts['to'] ) ? $atts['to'] : explode( ',', (string) $atts['to'] ) );
		if ( ! $to ) { return 'Keine gültige Empfängeradresse.'; }

		$message = array(
			'subject'      => (string) $atts['subject'],
			'body'         => array(
				'contentType' => false !== stripos( $h['content-type'], 'text/html' ) ? 'HTML' : 'Text',
				'content'     => (string) $atts['message'],
			),
			'toRecipients' => $to,
		);
		foreach ( array( 'reply-to' => 'replyTo', 'cc' => 'ccRecipients', 'bcc' => 'bccRecipients' ) as $hk => $gk ) {
			$list = self::recipients( $h[ $hk ] );
			if ( $list ) { $message[ $gk ] = $list; }
		}
		$files = array_filter( (array) ( $atts['attachments'] ?? array() ) );
		if ( $files ) {
			$size = 0;
			foreach ( $files as $f ) {
				if ( ! is_readable( $f ) ) { return 'Anhang nicht lesbar: ' . basename( $f ); }
				$size += filesize( $f );
				if ( $size > self::MAX_ATTACH ) { return 'Anhänge zu groß für den Versand über Microsoft 365.'; }
				$message['attachments'][] = array(
					'@odata.type'  => '#microsoft.graph.fileAttachment',
					'name'         => basename( $f ),
					'contentBytes' => base64_encode( (string) file_get_contents( $f ) ),
				);
			}
		}

		$payload = wp_json_encode( array( 'message' => $message, 'saveToSentItems' => true ) );
		for ( $try = 0; $try < 2; $try++ ) {
			$token = self::token( $try > 0 );
			if ( is_wp_error( $token ) ) { return $token->get_error_message(); }
			$res = wp_remote_post( self::graph_base() . '/v1.0/users/' . rawurlencode( self::get( 'sender' ) ) . '/sendMail', array(
				'timeout' => 20,
				'headers' => array( 'Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json' ),
				'body'    => $payload,
			) );
			if ( is_wp_error( $res ) ) { return 'Microsoft 365 nicht erreichbar: ' . $res->get_error_message(); }
			$code = (int) wp_remote_retrieve_response_code( $res );
			if ( 202 === $code ) { return true; }
			if ( 401 === $code && 0 === $try ) { continue; } // Token abgelaufen: einmal neu anmelden.
			return 'Microsoft 365 hat den Versand abgelehnt (HTTP ' . $code . '): ' . self::graph_error( $res );
		}
		return 'Microsoft 365 hat die Anmeldung abgelehnt.';
	}

	/** Zugriffstoken (App-Anmeldung), zwischengespeichert bis kurz vor Ablauf. */
	protected static function token( $fresh = false ) {
		if ( ! $fresh ) {
			$t = get_transient( self::TOKEN );
			if ( $t ) { return $t; }
		}
		$o   = self::get();
		$res = wp_remote_post( self::login_base() . '/' . rawurlencode( $o['tenant'] ) . '/oauth2/v2.0/token', array(
			'timeout' => 20,
			'body'    => array(
				'client_id'     => $o['client_id'],
				'client_secret' => $o['client_secret'],
				'scope'         => 'https://graph.microsoft.com/.default',
				'grant_type'    => 'client_credentials',
			),
		) );
		if ( is_wp_error( $res ) ) {
			return new WP_Error( 'esc_m365', 'Anmeldung bei Microsoft nicht erreichbar: ' . $res->get_error_message() );
		}
		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		if ( empty( $data['access_token'] ) ) {
			$msg = (string) ( $data['error_description'] ?? $data['error'] ?? ( 'HTTP ' . wp_remote_retrieve_response_code( $res ) ) );
			return new WP_Error( 'esc_m365', 'Anmeldung bei Microsoft fehlgeschlagen: ' . strtok( $msg, "\r\n" ) );
		}
		set_transient( self::TOKEN, $data['access_token'], max( 60, (int) ( $data['expires_in'] ?? 3600 ) - 120 ) );
		return $data['access_token'];
	}

	protected static function login_base() { return untrailingslashit( apply_filters( 'esc_m365_login_base', 'https://login.microsoftonline.com' ) ); }
	protected static function graph_base() { return untrailingslashit( apply_filters( 'esc_m365_graph_base', 'https://graph.microsoft.com' ) ); }

	protected static function graph_error( $res ) {
		$data = json_decode( (string) wp_remote_retrieve_body( $res ), true );
		return (string) ( $data['error']['message'] ?? $data['error']['code'] ?? 'ohne nähere Angabe' );
	}

	/** Header-Zeilen in eine Map (Kleinbuchstaben-Schlüssel) zerlegen. */
	protected static function parse_headers( $headers ) {
		$out   = array( 'content-type' => '', 'reply-to' => array(), 'cc' => array(), 'bcc' => array() );
		$lines = is_array( $headers ) ? $headers : preg_split( '/\r?\n/', (string) $headers );
		foreach ( $lines as $line ) {
			if ( false === strpos( (string) $line, ':' ) ) { continue; }
			list( $name, $value ) = array_map( 'trim', explode( ':', $line, 2 ) );
			$name = strtolower( $name );
			if ( 'content-type' === $name ) { $out['content-type'] = $value; }
			if ( isset( $out[ $name ] ) && is_array( $out[ $name ] ) ) {
				$out[ $name ] = array_merge( $out[ $name ], explode( ',', $value ) );
			}
		}
		return $out;
	}

	/** "Name <adresse>" bzw. "adresse" in Graph-Empfänger umwandeln. */
	protected static function recipients( array $list ) {
		$out = array();
		foreach ( $list as $item ) {
			$item = trim( (string) $item );
			if ( '' === $item ) { continue; }
			$name = '';
			$addr = $item;
			if ( preg_match( '/^(.*)<([^>]+)>$/', $item, $m ) ) {
				$name = trim( $m[1], " \"'" );
				$addr = trim( $m[2] );
			}
			if ( ! is_email( $addr ) ) { continue; }
			$e = array( 'address' => $addr );
			if ( '' !== $name ) { $e['name'] = $name; }
			$out[] = array( 'emailAddress' => $e );
		}
		return $out;
	}

	protected static function remember( $type, $msg ) {
		$s = (array) get_option( self::STATUS, array() );
		if ( 'ok' === $type ) {
			$s['last_ok'] = current_time( 'mysql' );
		} else {
			$s['last_error'] = current_time( 'mysql' );
			$s['last_error_msg'] = $msg;
		}
		update_option( self::STATUS, $s, false );
	}

	/* ------------------------------------------------------------------ */
	/* Backend                                                            */
	/* ------------------------------------------------------------------ */

	public static function menu() {
		add_submenu_page( 'es-theme-options', 'Mailversand', 'Mailversand', 'manage_options', 'esc-mail', array( __CLASS__, 'render' ), 55 );
	}

	/** Warnhinweise für Administratoren: aktueller Fehler, ablaufender Schlüssel. */
	public static function notices() {
		if ( ! current_user_can( 'manage_options' ) || ! self::get( 'enabled' ) ) { return; }
		$link = '<a href="' . esc_url( admin_url( 'admin.php?page=esc-mail' ) ) . '">Theme Options → Mailversand</a>';
		$s    = (array) get_option( self::STATUS, array() );
		if ( ! empty( $s['last_error'] ) && ( empty( $s['last_ok'] ) || $s['last_error'] > $s['last_ok'] ) ) {
			echo '<div class="notice notice-error"><p><strong>Mailversand über Microsoft 365 gestört:</strong> Die Website verschickt ersatzweise über den Hoster. Details unter ' . $link . '.</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		$exp = self::get( 'secret_expiry' );
		if ( $exp ) {
			$days = (int) floor( ( strtotime( $exp ) - current_time( 'timestamp' ) ) / DAY_IN_SECONDS );
			if ( $days <= 30 ) {
				$txt = $days < 0 ? 'ist abgelaufen' : 'läuft in ' . $days . ' Tagen ab';
				echo '<div class="notice notice-warning"><p><strong>Der geheime Schlüssel für den Mailversand ' . esc_html( $txt ) . '.</strong> Bitte bei der IT einen neuen anfordern und unter ' . $link . ' eintragen.</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}

	public static function handle_test() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Keine Berechtigung.' ); }
		check_admin_referer( 'esc_m365_test' );
		$to = sanitize_email( wp_unslash( $_POST['test_to'] ?? '' ) );
		if ( ! is_email( $to ) ) {
			$r = 'Bitte eine gültige Empfängeradresse eingeben.';
		} elseif ( ! self::configured() ) {
			$r = 'Microsoft 365 ist noch nicht vollständig eingerichtet oder nicht eingeschaltet.';
		} else {
			$r = self::send( array(
				'to'      => $to,
				'subject' => 'Testmail der Website (Mailversand über Microsoft 365)',
				'message' => "Diese Testmail wurde von der Website über Microsoft 365 verschickt.\nWenn sie ankommt, ist der Mailversand korrekt eingerichtet.",
				'headers' => array( 'Content-Type: text/plain; charset=UTF-8' ),
			) );
			self::remember( true === $r ? 'ok' : 'error', true === $r ? '' : $r );
		}
		set_transient( 'esc_m365_test_result_' . get_current_user_id(), true === $r ? 'ok' : $r, 120 );
		wp_safe_redirect( admin_url( 'admin.php?page=esc-mail' ) );
		exit;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$o      = self::get();
		$s      = (array) get_option( self::STATUS, array() );
		$result = get_transient( 'esc_m365_test_result_' . get_current_user_id() );
		delete_transient( 'esc_m365_test_result_' . get_current_user_id() );
		?>
		<div class="wrap">
			<h1>Mailversand</h1>
			<p>Hier wird eingestellt, dass die Website ihre Mails (Kontaktformular, Tageszusammenfassung, WordPress-Systemmails) über ein Microsoft-365-Postfach der Kanzlei verschickt statt über den Hoster. Die Werte stammen aus der App-Registrierung, die die IT in Microsoft Entra anlegt.</p>
			<?php if ( 'ok' === $result ) : ?>
				<div class="notice notice-success"><p><strong>Testmail erfolgreich an Microsoft 365 übergeben.</strong> Bitte im Postfach des Empfängers prüfen, ob sie angekommen ist.</p></div>
			<?php elseif ( $result ) : ?>
				<div class="notice notice-error"><p><strong>Testmail fehlgeschlagen:</strong> <?php echo esc_html( $result ); ?></p></div>
			<?php endif; ?>

			<h2>Status</h2>
			<table class="form-table" role="presentation"><tbody>
				<tr><th scope="row">Aktueller Weg</th><td><?php
					$gestoert = ! empty( $s['last_error'] ) && ( empty( $s['last_ok'] ) || $s['last_error'] > $s['last_ok'] );
					if ( ! self::configured() ) {
						echo '<strong style="color:#996800;">Mail-Relais des Hosters</strong> (Microsoft 365 nicht eingeschaltet oder unvollständig)';
					} elseif ( $gestoert ) {
						echo '<strong style="color:#b32d2e;">Microsoft 365 gestört</strong>, Mails gehen ersatzweise über den Hoster (siehe letzter Fehler)';
					} else {
						echo '<strong style="color:#00730a;">Microsoft 365</strong> (Absender ' . esc_html( $o['sender'] ) . ')';
					}
				?></td></tr>
				<tr><th scope="row">Letzter erfolgreicher Versand</th><td><?php echo esc_html( ! empty( $s['last_ok'] ) ? mysql2date( 'd.m.Y, H:i', $s['last_ok'] ) . ' Uhr' : '–' ); ?></td></tr>
				<tr><th scope="row">Letzter Fehler</th><td><?php echo ! empty( $s['last_error'] ) ? esc_html( mysql2date( 'd.m.Y, H:i', $s['last_error'] ) . ' Uhr: ' . ( $s['last_error_msg'] ?? '' ) ) : '–'; ?></td></tr>
			</tbody></table>

			<h2>Zugangsdaten</h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_mail_m365_group' ); ?>
				<table class="form-table" role="presentation"><tbody>
					<tr><th scope="row">Einschalten</th><td><label><input type="checkbox" name="<?php echo esc_attr( self::OPT ); ?>[enabled]" value="1" <?php checked( $o['enabled'], 1 ); ?>> Mails über Microsoft 365 verschicken</label></td></tr>
					<tr><th scope="row"><label for="esc-m365-tenant">Verzeichnis-ID (Mandanten-ID)</label></th><td><input id="esc-m365-tenant" type="text" class="regular-text code" name="<?php echo esc_attr( self::OPT ); ?>[tenant]" value="<?php echo esc_attr( $o['tenant'] ); ?>" autocomplete="off"></td></tr>
					<tr><th scope="row"><label for="esc-m365-client">Anwendungs-ID (Client-ID)</label></th><td><input id="esc-m365-client" type="text" class="regular-text code" name="<?php echo esc_attr( self::OPT ); ?>[client_id]" value="<?php echo esc_attr( $o['client_id'] ); ?>" autocomplete="off"></td></tr>
					<tr><th scope="row"><label for="esc-m365-secret">Geheimer Clientschlüssel (Wert)</label></th><td>
						<input id="esc-m365-secret" type="password" class="regular-text code" name="<?php echo esc_attr( self::OPT ); ?>[client_secret]" value="" autocomplete="new-password" placeholder="<?php echo esc_attr( $o['client_secret'] ? 'gespeichert, zum Ändern neu eingeben' : '' ); ?>">
						<p class="description">Wird aus Sicherheitsgründen nach dem Speichern nicht mehr angezeigt. Leer lassen, um den gespeicherten Schlüssel zu behalten.</p>
					</td></tr>
					<tr><th scope="row"><label for="esc-m365-expiry">Schlüssel gültig bis</label></th><td>
						<input id="esc-m365-expiry" type="date" name="<?php echo esc_attr( self::OPT ); ?>[secret_expiry]" value="<?php echo esc_attr( $o['secret_expiry'] ); ?>">
						<p class="description">Ablaufdatum aus der App-Registrierung. 30 Tage vorher erscheint im Backend ein Hinweis.</p>
					</td></tr>
					<tr><th scope="row"><label for="esc-m365-sender">Absender-Postfach</label></th><td><input id="esc-m365-sender" type="email" class="regular-text" name="<?php echo esc_attr( self::OPT ); ?>[sender]" value="<?php echo esc_attr( $o['sender'] ); ?>" placeholder="website@energiesozietaet.de"></td></tr>
				</tbody></table>
				<?php submit_button( 'Speichern' ); ?>
			</form>

			<h2>Testmail</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="esc_m365_test">
				<?php wp_nonce_field( 'esc_m365_test' ); ?>
				<input type="email" name="test_to" class="regular-text" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>">
				<?php submit_button( 'Testmail senden', 'secondary', 'submit', false ); ?>
				<p class="description">Verschickt direkt über Microsoft 365, ohne Ersatzweg. So zeigt sich ein Fehler in den Zugangsdaten sofort.</p>
			</form>
		</div>
		<?php
	}
}
ESC_Mail_M365::init();
