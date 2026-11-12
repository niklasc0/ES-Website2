<?php
/**
 * Kontaktanfragen-Eingang: Jede Anfrage aus dem Kontaktformular wird VOR dem
 * Mailversand als privater Eintrag gespeichert (Backend „Kontaktanfragen"),
 * damit keine Anfrage verloren geht, auch wenn die Mail nicht ankommt.
 * Sichtbar nur für Administratoren und Redakteure. Als Spam aussortierte
 * Anfragen werden täglich um 7 Uhr gesammelt an den Formular-Empfänger
 * gemeldet und nach 30 Tagen gelöscht; alle übrigen Anfragen optional nach
 * einer einstellbaren Frist.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Contact_Inbox {

	const PT        = 'es_anfrage';
	const CRON      = 'esc_anfragen_cleanup';
	const DIGEST    = 'esc_anfragen_digest';
	const DIGEST_MAX = 50;
	const SPAM_DAYS = 30;
	const CAPS_OPT  = 'esc_anfrage_caps_v1';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );
		add_action( 'admin_init', array( __CLASS__, 'ensure_caps' ) );
		add_action( 'add_meta_boxes_' . self::PT, array( __CLASS__, 'meta_boxes' ) );
		add_action( 'admin_menu', array( __CLASS__, 'mark_read' ), 1 );
		add_filter( 'display_post_states', array( __CLASS__, 'post_states' ), 10, 2 );
		add_filter( 'manage_' . self::PT . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::PT . '_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'menu_badge' ), 99 );
		add_action( self::CRON, array( __CLASS__, 'cleanup' ) );
		add_action( self::DIGEST, array( __CLASS__, 'spam_digest' ) );
	}

	public static function register() {
		register_post_type( self::PT, array(
			'labels' => array(
				'name'               => 'Kontaktanfragen',
				'singular_name'      => 'Kontaktanfrage',
				'menu_name'          => 'Kontaktanfragen',
				'all_items'          => 'Alle Anfragen',
				'edit_item'          => 'Kontaktanfrage',
				'view_item'          => 'Kontaktanfrage ansehen',
				'search_items'       => 'Anfragen durchsuchen',
				'not_found'          => 'Noch keine Anfragen eingegangen.',
				'not_found_in_trash' => 'Keine Anfragen im Papierkorb.',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => false,
			'menu_position'       => 26,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => false,
			'rewrite'             => false,
			'query_var'           => false,
			'capability_type'     => array( 'es_anfrage', 'es_anfragen' ),
			'map_meta_cap'        => true,
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		) );
	}

	/** Leserechte für Administratoren und Redakteure (personenbezogene Daten). */
	public static function ensure_caps() {
		if ( get_option( self::CAPS_OPT ) ) { return; }
		$caps = array(
			'edit_es_anfragen', 'edit_others_es_anfragen', 'edit_private_es_anfragen',
			'edit_published_es_anfragen', 'read_private_es_anfragen', 'publish_es_anfragen',
			'delete_es_anfragen', 'delete_others_es_anfragen', 'delete_private_es_anfragen',
			'delete_published_es_anfragen',
		);
		foreach ( array( 'administrator', 'editor' ) as $role_name ) {
			$role = get_role( $role_name );
			if ( ! $role ) { continue; }
			foreach ( $caps as $cap ) { $role->add_cap( $cap ); }
		}
		update_option( self::CAPS_OPT, current_time( 'mysql' ) );
	}

	/**
	 * Speichert eine Anfrage. $status: 'neu' (wird gemailt) oder 'spam'
	 * (eindeutiger Bot, wird nicht gemailt). Gibt die Post-ID zurück oder 0.
	 */
	public static function store( array $d, $status, array $spam_signals = array() ) {
		$name = trim( ( $d['vorname'] ?? '' ) . ' ' . ( $d['nachname'] ?? '' ) );
		$id   = wp_insert_post( array(
			'post_type'    => self::PT,
			'post_status'  => 'private',
			'post_author'  => 0,
			'post_title'   => $name . ( ! empty( $d['thema'] ) ? ' – ' . $d['thema'] : '' ),
			'post_content' => (string) ( $d['nachricht'] ?? '' ),
		), true );
		if ( is_wp_error( $id ) || ! $id ) { return 0; }
		foreach ( array( 'vorname', 'nachname', 'email', 'org', 'thema', 'sprache' ) as $k ) {
			if ( isset( $d[ $k ] ) && '' !== $d[ $k ] ) { update_post_meta( $id, '_esc_' . $k, $d[ $k ] ); }
		}
		update_post_meta( $id, '_esc_status', 'spam' === $status ? 'spam' : 'offen' );
		if ( $spam_signals ) { update_post_meta( $id, '_esc_spam_grund', implode( ', ', $spam_signals ) ); }
		return (int) $id;
	}

	/** Ergebnis des Mailversands festhalten. */
	public static function set_mail_result( $id, $ok, $info = '' ) {
		if ( ! $id ) { return; }
		update_post_meta( $id, '_esc_status', $ok ? 'zugestellt' : 'fehler' );
		if ( $info ) { update_post_meta( $id, '_esc_mail_info', $info ); }
	}

	public static function meta_boxes( $post ) {
		remove_meta_box( 'submitdiv', self::PT, 'side' );
		add_meta_box( 'esc_anfrage', 'Kontaktanfrage', array( __CLASS__, 'box' ), self::PT, 'normal', 'high' );
	}

	public static function box( $post ) {
		$m = function ( $k ) use ( $post ) { return (string) get_post_meta( $post->ID, '_esc_' . $k, true ); };
		$email = $m( 'email' );
		$name  = trim( $m( 'vorname' ) . ' ' . $m( 'nachname' ) );
		$rows  = array(
			'Eingang'     => esc_html( get_the_date( 'd.m.Y, H:i', $post ) . ' Uhr' ),
			'Name'        => esc_html( $name ),
			'E-Mail'      => $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '–',
			'Unternehmen' => esc_html( $m( 'org' ) ?: '–' ),
			'Thema'       => esc_html( $m( 'thema' ) ?: '–' ),
			'Sprache'     => esc_html( 'en' === $m( 'sprache' ) ? 'Englisch' : 'Deutsch' ),
			'Mailversand' => self::status_html( $post->ID ),
		);
		echo '<table class="form-table" role="presentation"><tbody>';
		foreach ( $rows as $label => $html ) {
			echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . $html . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '<tr><th scope="row">Nachricht</th><td><div style="white-space:pre-wrap;max-width:760px;background:#f6f7f7;padding:12px 14px;border-left:3px solid #95D708;">'
			. esc_html( $post->post_content ) . '</div></td></tr>';
		echo '</tbody></table>';
		echo '<p>';
		if ( $email ) {
			$reply = 'mailto:' . rawurlencode( $email ) . '?subject=' . rawurlencode( 'Ihre Anfrage an die Energiesozietät' );
			echo '<a class="button button-primary" href="' . esc_attr( $reply ) . '">Per E-Mail antworten</a> ';
		}
		if ( current_user_can( 'delete_post', $post->ID ) ) {
			echo '<a class="button" href="' . esc_url( get_delete_post_link( $post->ID ) ) . '">In den Papierkorb</a> ';
		}
		echo '<a class="button" href="' . esc_url( admin_url( 'edit.php?post_type=' . self::PT ) ) . '">Zur Übersicht</a></p>';
	}

	protected static function status_html( $id ) {
		$status = (string) get_post_meta( $id, '_esc_status', true );
		$info   = (string) get_post_meta( $id, '_esc_mail_info', true );
		$grund  = (string) get_post_meta( $id, '_esc_spam_grund', true );
		$map    = array(
			'zugestellt' => array( '#00730a', 'An den Mailserver übergeben' ),
			'fehler'     => array( '#b32d2e', 'Mailversand fehlgeschlagen' ),
			'spam'       => array( '#787c82', 'Als Spam aussortiert (nicht einzeln gemailt)' ),
			'offen'      => array( '#996800', 'Mailversand nicht abgeschlossen' ),
		);
		list( $color, $label ) = $map[ $status ] ?? $map['offen'];
		$html = '<span style="color:' . esc_attr( $color ) . ';font-weight:600;">' . esc_html( $label ) . '</span>';
		if ( $grund ) { $html .= '<br><small>Spam-Merkmale: ' . esc_html( $grund ) . '</small>'; }
		$digest = (string) get_post_meta( $id, '_esc_digest', true );
		if ( $digest ) { $html .= '<br><small>Gemeldet in der Zusammenfassung vom ' . esc_html( mysql2date( 'd.m.Y', $digest ) ) . '</small>'; }
		if ( $info )  { $html .= '<br><small>' . esc_html( $info ) . '</small>'; }
		return $html;
	}

	/** Beim Öffnen gilt eine Anfrage als gelesen (vor dem Menü-Zähler, damit er sofort stimmt). */
	public static function mark_read() {
		global $pagenow;
		if ( 'post.php' !== $pagenow || 'edit' !== ( $_GET['action'] ?? '' ) ) { return; }
		$post = get_post( isset( $_GET['post'] ) ? (int) $_GET['post'] : 0 );
		if ( $post && self::PT === $post->post_type && current_user_can( 'edit_post', $post->ID ) && ! get_post_meta( $post->ID, '_esc_gelesen', true ) ) {
			update_post_meta( $post->ID, '_esc_gelesen', current_time( 'mysql' ) );
		}
	}

	/** Alle Anfragen sind privat; den Zusatz „Privat“ in der Liste weglassen. */
	public static function post_states( $states, $post ) {
		if ( self::PT === $post->post_type ) { unset( $states['private'] ); }
		return $states;
	}

	public static function columns( $cols ) {
		return array(
			'cb'          => $cols['cb'] ?? '',
			'title'       => 'Absender',
			'esc_email'   => 'E-Mail',
			'esc_thema'   => 'Thema',
			'esc_status'  => 'Mailversand',
			'esc_eingang' => 'Eingang',
		);
	}

	public static function column( $col, $id ) {
		switch ( $col ) {
			case 'esc_email':
				$e = (string) get_post_meta( $id, '_esc_email', true );
				echo $e ? '<a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a>' : '–';
				break;
			case 'esc_thema':
				echo esc_html( (string) get_post_meta( $id, '_esc_thema', true ) ?: '–' );
				break;
			case 'esc_status':
				echo self::status_html( $id ); // phpcs:ignore WordPress.Security.EscapeOutput
				if ( ! get_post_meta( $id, '_esc_gelesen', true ) && 'spam' !== get_post_meta( $id, '_esc_status', true ) ) {
					echo '<br><strong>Neu</strong>';
				}
				break;
			case 'esc_eingang':
				echo esc_html( get_the_date( 'd.m.Y, H:i', $id ) );
				break;
		}
	}

	public static function row_actions( $actions, $post ) {
		if ( self::PT !== $post->post_type ) { return $actions; }
		unset( $actions['inline hide-if-no-js'] );
		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">Öffnen</a>';
		}
		return $actions;
	}

	/** Zähler ungelesener Anfragen am Menüpunkt. */
	public static function menu_badge() {
		global $menu;
		if ( ! current_user_can( 'edit_es_anfragen' ) || ! is_array( $menu ) ) { return; }
		$n = count( get_posts( array(
			'post_type'   => self::PT,
			'post_status' => 'private',
			'numberposts' => 100,
			'fields'      => 'ids',
			'meta_query'  => array(
				'relation' => 'AND',
				array( 'key' => '_esc_gelesen', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_esc_status', 'value' => 'spam', 'compare' => '!=' ),
			),
		) ) );
		if ( ! $n ) { return; }
		foreach ( $menu as $i => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=' . self::PT === $item[2] ) {
				$menu[ $i ][0] .= ' <span class="awaiting-mod count-' . $n . '"><span class="pending-count">' . $n . '</span></span>';
			}
		}
	}

	public static function schedule() {
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CRON );
		}
		if ( ! wp_next_scheduled( self::DIGEST ) ) {
			$next = new DateTimeImmutable( 'today 07:00', wp_timezone() );
			if ( $next->getTimestamp() <= time() ) { $next = $next->modify( '+1 day' ); }
			wp_schedule_event( $next->getTimestamp(), 'daily', self::DIGEST );
		}
	}

	/**
	 * Tägliche Kontroll-Mail mit allen seit der letzten Meldung aussortierten
	 * Anfragen, damit eine fälschlich aussortierte echte Anfrage auffällt.
	 * Geht nur an den internen Formular-Empfänger; ohne neue Fälle keine Mail.
	 */
	public static function spam_digest() {
		$ids = get_posts( array(
			'post_type'   => self::PT,
			'post_status' => 'private',
			'numberposts' => self::DIGEST_MAX + 1,
			'fields'      => 'ids',
			'orderby'     => 'date',
			'order'       => 'ASC',
			'meta_query'  => array(
				'relation' => 'AND',
				array( 'key' => '_esc_status', 'value' => 'spam' ),
				array( 'key' => '_esc_digest', 'compare' => 'NOT EXISTS' ),
			),
		) );
		if ( ! $ids || ! class_exists( 'ESC_Contact_Form' ) ) { return; }
		$more = count( $ids ) > self::DIGEST_MAX;
		$ids  = array_slice( $ids, 0, self::DIGEST_MAX );
		$n    = count( $ids );

		$body  = 'Das Kontaktformular der Website hat seit der letzten Zusammenfassung ' . $n . ( 1 === $n ? ' Anfrage' : ' Anfragen' ) . " automatisch als Spam aussortiert. Diese wurden nicht einzeln per E-Mail zugestellt.\n\n";
		$body .= "Bitte kurz prüfen, ob eine echte Anfrage dabei ist. In dem Fall einfach direkt an die angegebene E-Mail-Adresse schreiben.\n\n";
		$body .= "Aussortiert wird nur, wenn zwei technische Merkmale zusammenkommen: Das für Besucher unsichtbare Prüffeld wurde ausgefüllt, und das Formular wurde ohne JavaScript abgeschickt. Beides ist typisch für automatisierte Programme, bei Menschen im normalen Browser kommt es praktisch nicht vor.\n";
		foreach ( $ids as $i => $id ) {
			$p   = get_post( $id );
			$msg = (string) $p->post_content;
			if ( mb_strlen( $msg ) > 600 ) { $msg = mb_substr( $msg, 0, 600 ) . ' […]'; }
			$m    = function ( $k ) use ( $id ) { return (string) get_post_meta( $id, '_esc_' . $k, true ); };
			$body .= "\n----------------------------------------\n";
			$body .= ( $i + 1 ) . ') ' . get_the_date( 'd.m.Y, H:i', $p ) . " Uhr\n";
			$body .= 'Name:        ' . trim( $m( 'vorname' ) . ' ' . $m( 'nachname' ) ) . "\n";
			$body .= 'E-Mail:      ' . $m( 'email' ) . "\n";
			if ( $m( 'org' ) )   { $body .= 'Unternehmen: ' . $m( 'org' ) . "\n"; }
			if ( $m( 'thema' ) ) { $body .= 'Thema:       ' . $m( 'thema' ) . "\n"; }
			$body .= "Nachricht:\n" . $msg . "\n";
		}
		$body .= "\n----------------------------------------\n";
		if ( $more ) { $body .= "Weitere aussortierte Anfragen folgen in der nächsten Zusammenfassung.\n"; }
		$body .= "Alle gespeicherten Anfragen im Website-Backend: " . admin_url( 'edit.php?post_type=' . self::PT ) . "\n";
		$body .= 'Aussortierte Anfragen werden nach ' . self::SPAM_DAYS . " Tagen automatisch gelöscht.\n";

		$subject = 'Kontaktformular: ' . $n . ( 1 === $n ? ' aussortierte Anfrage' : ' aussortierte Anfragen' ) . ' zur Kontrolle';
		$ok = wp_mail( ESC_Contact_Form::get( 'recipient' ), $subject, $body, array( 'Content-Type: text/plain; charset=UTF-8' ) );
		if ( ! $ok ) { return; }
		$now = current_time( 'mysql' );
		foreach ( $ids as $id ) { update_post_meta( $id, '_esc_digest', $now ); }
	}

	/** Spam nach 30 Tagen löschen, übrige Anfragen nach der eingestellten Frist (0 = nie). */
	public static function cleanup() {
		$jobs = array( array( 'spam', self::SPAM_DAYS ) );
		$days = class_exists( 'ESC_Contact_Form' ) ? (int) ESC_Contact_Form::get( 'retention_days' ) : 0;
		if ( $days > 0 ) { $jobs[] = array( 'nicht-spam', $days ); }
		foreach ( $jobs as $job ) {
			list( $kind, $age ) = $job;
			$ids = get_posts( array(
				'post_type'   => self::PT,
				'post_status' => array( 'private', 'trash' ),
				'numberposts' => 200,
				'fields'      => 'ids',
				'date_query'  => array( array( 'before' => $age . ' days ago' ) ),
				'meta_query'  => array( array( 'key' => '_esc_status', 'value' => 'spam', 'compare' => 'spam' === $kind ? '=' : '!=' ) ),
			) );
			foreach ( $ids as $id ) { wp_delete_post( $id, true ); }
		}
	}
}
ESC_Contact_Inbox::init();
