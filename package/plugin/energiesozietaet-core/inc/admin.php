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
	}

	public static function menu() {
		add_submenu_page(
			'tools.php',
			'Energiesozietät Importer',
			'Energiesozietät-Demo',
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
			<h1>Energiesozietät – Demo-Inhalte importieren</h1>
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

	public static function notices() {
		$msg = get_transient( 'esc_import_msg' );
		if ( ! $msg ) { return; }
		delete_transient( 'esc_import_msg' );
		printf( '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $msg['type'] ), esc_html( $msg['text'] ) );
	}
}
ESC_Admin::init();
