<?php
/**
 * Admin UI – Importer trigger under Tools menu.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Admin {

	public static function init() {
		add_action( 'admin_menu',   array( __CLASS__, 'menu' ) );
		add_action( 'admin_init',   array( __CLASS__, 'maybe_run_import' ) );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
		add_filter( 'page_row_actions', array( __CLASS__, 'page_row_actions' ), 20, 2 );
		add_action( 'load-post.php', array( __CLASS__, 'redirect_page_to_elementor' ) );
		add_filter( 'manage_es_veranstaltung_posts_columns', array( __CLASS__, 'event_columns' ) );
		add_action( 'manage_es_veranstaltung_posts_custom_column', array( __CLASS__, 'event_column' ), 10, 2 );
	}

	public static function menu() {
		add_submenu_page(
			'tools.php',
			'Energiesozietät Importer',
			'Energiesozietät-Import',
			'manage_options',
			'esc-importer',
			array( __CLASS__, 'page' )
		);
	}

	public static function page() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$done  = get_option( ESC_Importer::OPT_DONE );
		$nonce = wp_create_nonce( 'esc_import' );
		?>
		<div class="wrap">
			<h1>Energiesozietät – Inhalte importieren</h1>
			<p>Erstellt alle Seiten, Team-Mitglieder, Einzelleistungen, Stellenangebote, Veranstaltungen, News-Artikel, Publikationen und das Hauptmenü aus den gebündelten Daten. Bestehende Inhalte mit passendem Slug werden aktualisiert.</p>

			<?php if ( $done ) : ?>
				<div class="notice notice-info inline">
					<p><strong>Letzter Import:</strong> <?php echo esc_html( $done ); ?></p>
				</div>
			<?php endif; ?>

			<form method="post" style="margin-top:24px;">
				<input type="hidden" name="esc_action" value="import" />
				<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>" />
				<p>
					<button type="submit" name="force" value="0" class="button button-primary button-large">
						<?php echo $done ? 'Erneut importieren (nur fehlende Inhalte)' : 'Inhalte jetzt importieren'; ?>
					</button>
					<?php if ( $done ) : ?>
						<button type="submit" name="force" value="1" class="button button-secondary" onclick="return confirm('Bestehende Inhalte (mit gleichen Slugs) werden überschrieben. Fortfahren?');">
							Import erzwingen (alles überschreiben)
						</button>
					<?php endif; ?>
				</p>
			</form>

			<h2 style="margin-top:40px;">Hinweise</h2>
			<ul style="list-style:disc; padding-left:24px;">
				<li>Für beste Ergebnisse vorher: Theme <em>Energiesozietät</em> aktivieren, Elementor-Plugin aktivieren, unter <em>Einstellungen → Permalinks</em> „Beitragsname" wählen.</li>
				<li>Der Import legt 14 Seiten, 26 Team-Mitglieder, 19 Einzelleistungen (mit Beratungsfeld-Taxonomie), 3 Stellenangebote, 4 Veranstaltungen, 13 News-Artikel, 12 Publikationen an.</li>
				<li>Homepage wird automatisch auf die angelegte <em>Home</em>-Seite gesetzt.</li>
			</ul>
		</div>
		<?php
	}

	public static function maybe_run_import() {
		if ( ! is_admin() ) { return; }
		if ( empty( $_POST['esc_action'] ) || 'import' !== $_POST['esc_action'] ) { return; }
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		check_admin_referer( 'esc_import' );

		$force = ! empty( $_POST['force'] );
		if ( $force ) { ESC_Importer::reset(); }
		$result = ESC_Importer::run( $force );

		$msg = is_wp_error( $result ) ? $result->get_error_message() : ( $result['message'] ?? 'OK' );
		set_transient( 'esc_import_msg', array(
			'type' => is_wp_error( $result ) || ( isset( $result['status'] ) && 'skipped' === $result['status'] ) ? 'warning' : 'success',
			'text' => $msg,
		), 60 );
		wp_safe_redirect( admin_url( 'tools.php?page=esc-importer' ) );
		exit;
	}

	/**
	 * Veranstaltungs-Übersicht: Termin-Spalte mit Kennzeichnung, ob die
	 * Veranstaltung noch bevorsteht. Vergangene bleiben im Backend erhalten,
	 * werden auf der Website aber automatisch ausgeblendet.
	 */
	public static function event_columns( $cols ) {
		$neu = array();
		foreach ( $cols as $k => $label ) {
			$neu[ $k ] = $label;
			if ( 'title' === $k ) { $neu['es_termin'] = 'Termin'; }
		}
		return $neu;
	}

	public static function event_column( $col, $post_id ) {
		if ( 'es_termin' !== $col ) { return; }
		$start = (string) get_post_meta( $post_id, 'es_start_date', true );
		if ( ! $start ) { echo '<span style="color:#a00;">kein Datum – wird nicht angezeigt</span>'; return; }
		$vergangen = $start < current_time( 'Y-m-d' );
		echo esc_html( date_i18n( 'd.m.Y', strtotime( $start ) ) ) . ' &middot; ';
		echo $vergangen
			? '<span style="color:#787c82;">Vergangen – auf der Website ausgeblendet</span>'
			: '<span style="color:#00730a;font-weight:600;">Kommend – sichtbar</span>';
	}

	/** Seite, die mit Elementor gebaut ist (nur die gehören in den Elementor-Editor). */
	protected static function elementor_managed( $post ) {
		return $post instanceof WP_Post
			&& 'page' === $post->post_type
			&& 'builder' === get_post_meta( $post->ID, '_elementor_edit_mode', true );
	}

	/**
	 * In der Seiten-Übersicht den einfachen „Bearbeiten"-Link bei
	 * Elementor-Seiten ausblenden, für alle Rollen unterhalb Administrator.
	 * Der normale Editor zeigt diese Seiten ohne Formatierung an und verleitet
	 * dazu, die Elementor-Fassung zu überschreiben; gepflegt werden sie über
	 * „Mit Elementor bearbeiten". Schnellbearbeitung (Titel, Slug, Status)
	 * bleibt verfügbar, Administratoren sehen weiterhin beide Wege.
	 */
	public static function page_row_actions( $actions, $post ) {
		if ( self::elementor_managed( $post ) && ! current_user_can( 'manage_options' ) ) {
			unset( $actions['edit'] );
		}
		return $actions;
	}

	/**
	 * Sicherheitsnetz für denselben Fall: Landet ein Nicht-Admin doch im
	 * normalen Editor einer Elementor-Seite (z. B. per Klick auf den
	 * Seitentitel oder über einen alten Link), geht es direkt in den
	 * Elementor-Editor weiter.
	 */
	public static function redirect_page_to_elementor() {
		if ( current_user_can( 'manage_options' ) || ! did_action( 'elementor/loaded' ) ) { return; }
		if ( 'edit' !== ( $_GET['action'] ?? '' ) ) { return; }
		$post = get_post( isset( $_GET['post'] ) ? (int) $_GET['post'] : 0 );
		if ( ! self::elementor_managed( $post ) || ! current_user_can( 'edit_post', $post->ID ) ) { return; }
		wp_safe_redirect( admin_url( 'post.php?post=' . $post->ID . '&action=elementor' ) );
		exit;
	}

	public static function notices() {
		self::editing_hint();
		$msg = get_transient( 'esc_import_msg' );
		if ( ! $msg ) { return; }
		delete_transient( 'esc_import_msg' );
		printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $msg['type'] ), esc_html( $msg['text'] ) );
	}

	/**
	 * Redaktions-Hinweis je Backend-Bereich: Inhalts-Datensätze (Einzelleistungen,
	 * Team, Stellen, News, Veranstaltungen, Publikationen) werden über den
	 * normalen Editor samt Eingabefeldern gepflegt; die festen Seiten dagegen
	 * mit Elementor. Wird auf den Übersichts- UND Bearbeiten-Screens gezeigt.
	 */
	protected static function editing_hint() {
		if ( ! function_exists( 'get_current_screen' ) ) { return; }
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->base, array( 'edit', 'post' ), true ) ) { return; }
		$cpts = array( 'es_einzelleistung', 'es_team', 'es_karriere', 'es_news', 'es_veranstaltung', 'es_publikation' );
		if ( in_array( $screen->post_type, $cpts, true ) ) {
			echo '<div class="notice notice-info"><p><strong>Hinweis zur Bearbeitung:</strong> '
				. 'Diese Einträge werden über <em>„Bearbeiten"</em> gepflegt, also den normalen Editor und die Eingabefelder darunter. '
				. '„Mit Elementor bearbeiten" ist hier bewusst deaktiviert: Elementor würde eine eigene Kopie über den Inhalt legen, '
				. 'wodurch Aufklapp-Rubriken, die englische Fassung und die Übersetzungsdatei von der Anzeige abgekoppelt würden.</p></div>';
			return;
		}
		if ( 'page' === $screen->post_type ) {
			echo '<div class="notice notice-info"><p><strong>Hinweis zur Bearbeitung:</strong> '
				. 'Die festen Seiten (Startseite, Philosophie, Leistungen usw.) werden mit <em>„Mit Elementor bearbeiten"</em> gepflegt. '
				. 'Die englischen Kopien (Untereinträge mit dem Zusatz EN) übernehmen Änderungen der deutschen Seite automatisch, '
				. 'solange dort noch keine Übersetzung eingespielt wurde.</p></div>';
		}
	}
}
ESC_Admin::init();
