<?php
/**
 * LinkedIn-Feed — Custom Post Type + Shortcode.
 *
 * Der User pflegt LinkedIn-Posts als CPT-Einträge (Titel, Inhalt, Datum,
 * Beitragsbild, externer Link). Im Frontend rendert [es_linkedin_posts
 * limit=3 profile_url="…"] eine Card-Reihe, die design-technisch mit den
 * News-Kacheln harmoniert. Der Profile-URL-Button führt auf das LinkedIn-
 * Profil der Kanzlei (konfigurierbar pro Section).
 *
 * Optional: globale LinkedIn-Profile-URL in den Options, damit der User
 * nicht pro Seite eingibt.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_LinkedIn {

	const OPT = 'esc_linkedin';

	public static function init() {
		add_action( 'init',           array( __CLASS__, 'register_cpt' ), 6 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'meta_box' ) );
		add_action( 'save_post',      array( __CLASS__, 'save' ), 10, 2 );
		add_shortcode( 'es_linkedin_posts', array( __CLASS__, 'shortcode' ) );
		add_action( 'admin_init',     array( __CLASS__, 'register_settings' ) );
		// Settings-Seite wird als Submenu unter dem CPT eingefügt
		add_action( 'admin_menu',     array( __CLASS__, 'settings_menu' ) );
	}

	public static function register_cpt() {
		register_post_type( 'es_linkedin', array(
			'labels' => array(
				'name'               => 'LinkedIn-Posts',
				'singular_name'      => 'LinkedIn-Post',
				'menu_name'          => 'LinkedIn',
				'add_new'            => 'Neuer Post',
				'add_new_item'       => 'Neuer LinkedIn-Post',
				'edit_item'          => 'LinkedIn-Post bearbeiten',
				'all_items'          => 'Alle Posts',
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'menu_icon'    => 'dashicons-linkedin',
			'menu_position'=> 27,
			'has_archive'  => false,
			'rewrite'      => false,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		) );
	}

	public static function meta_box() {
		add_meta_box( 'esc_linkedin_meta', 'LinkedIn-Post Details', array( __CLASS__, 'box' ), 'es_linkedin', 'normal', 'default' );
	}

	public static function box( $post ) {
		wp_nonce_field( 'esc_linkedin', 'esc_linkedin_nonce' );
		$url  = (string) get_post_meta( $post->ID, 'es_li_url', true );
		$date = (string) get_post_meta( $post->ID, 'es_li_date', true );
		echo '<p><label><strong>URL zum LinkedIn-Post</strong></label><br>';
		echo '<input type="url" name="es_li_url" value="' . esc_attr( $url ) . '" style="width:100%;" placeholder="https://www.linkedin.com/posts/..." /></p>';
		echo '<p><label><strong>Veröffentlichungsdatum</strong></label><br>';
		echo '<input type="date" name="es_li_date" value="' . esc_attr( $date ) . '" /></p>';
	}

	public static function save( $post_id, $post ) {
		if ( 'es_linkedin' !== $post->post_type ) { return; }
		if ( ! isset( $_POST['esc_linkedin_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['esc_linkedin_nonce'] ), 'esc_linkedin' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( isset( $_POST['es_li_url'] ) )  { update_post_meta( $post_id, 'es_li_url',  esc_url_raw( wp_unslash( $_POST['es_li_url'] ) ) ); }
		if ( isset( $_POST['es_li_date'] ) ) { update_post_meta( $post_id, 'es_li_date', sanitize_text_field( wp_unslash( $_POST['es_li_date'] ) ) ); }
	}

	public static function register_settings() {
		register_setting( 'esc_li_group', self::OPT, array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
	}

	public static function sanitize( $input ) {
		return array(
			'profile_url' => esc_url_raw( $input['profile_url'] ?? '' ),
		);
	}

	public static function get_opt( $key, $default = '' ) {
		$opts = array_merge( array( 'profile_url' => '' ), (array) get_option( self::OPT, array() ) );
		return $opts[ $key ] ?? $default;
	}

	public static function settings_menu() {
		add_submenu_page(
			'edit.php?post_type=es_linkedin',
			'LinkedIn-Einstellungen',
			'Einstellungen',
			'manage_options',
			'esc-linkedin-settings',
			array( __CLASS__, 'settings_render' )
		);
	}

	public static function settings_render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		?>
		<div class="wrap">
			<h1>LinkedIn-Einstellungen</h1>
			<p>Globale Einstellung für den LinkedIn-Feed. Die Einzel-Posts pflegst Du als LinkedIn-Post-CPT-Einträge im linken Menü.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'esc_li_group' ); ?>
				<table class="form-table"><tbody>
					<tr><th scope="row"><label>LinkedIn-Profil-URL</label></th><td>
						<input type="url" name="<?php echo esc_attr( self::OPT ); ?>[profile_url]" value="<?php echo esc_attr( self::get_opt( 'profile_url' ) ); ?>" style="width:100%;max-width:480px;" placeholder="https://www.linkedin.com/company/energiesozietaet/" />
						<p class="description">Ziel des "Alle Posts ansehen"-Links unter dem Feed. Wird auch im Home-Blueprint als Default verwendet.</p>
					</td></tr>
				</tbody></table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * [es_linkedin_posts limit=3 profile_url="..." title="Aus unserem LinkedIn"]
	 */
	public static function shortcode( $atts ) {
		$defaults = array(
			'limit'       => 3,
			'profile_url' => self::get_opt( 'profile_url', '' ),
			'title'       => 'Aus unserem LinkedIn',
			'eyebrow'     => 'LinkedIn',
		);
		$atts = shortcode_atts( $defaults, $atts, 'es_linkedin_posts' );
		$limit = max( 1, min( 12, (int) $atts['limit'] ) );

		$q = new WP_Query( array(
			'post_type'      => 'es_linkedin',
			'posts_per_page' => $limit,
			'orderby'        => 'meta_value',
			'meta_key'       => 'es_li_date',
			'order'          => 'DESC',
		) );

		ob_start(); ?>
		<section class="es-linkedin-feed">
			<div class="es-linkedin-feed__head">
				<div>
					<div class="es-eyebrow"><?php echo esc_html( $atts['eyebrow'] ); ?></div>
					<h2 class="es-linkedin-feed__title"><?php echo esc_html( $atts['title'] ); ?></h2>
				</div>
				<?php if ( $atts['profile_url'] ) : ?>
					<a class="es-link" href="<?php echo esc_url( $atts['profile_url'] ); ?>" target="_blank" rel="noopener">Alle Posts ansehen ↗</a>
				<?php endif; ?>
			</div>
			<?php if ( ! $q->have_posts() ) : ?>
				<p class="es-linkedin-feed__empty" style="color:#5A6577;">Noch keine LinkedIn-Posts gepflegt. Leg sie unter <em>LinkedIn → Neuer Post</em> im Backend an.</p>
			<?php else : ?>
				<div class="esc-grid esc-grid--cols-3">
					<?php while ( $q->have_posts() ) : $q->the_post();
						$li_url = (string) get_post_meta( get_the_ID(), 'es_li_url', true );
						$li_dt  = (string) get_post_meta( get_the_ID(), 'es_li_date', true );
						$href   = $li_url ? $li_url : '#';
						$thumb  = get_post_thumbnail_id();
						$date_fmt = $li_dt ? date_i18n( 'j. F Y', strtotime( $li_dt ) ) : get_the_date(); ?>
						<a class="esc-card es-linkedin-card" href="<?php echo esc_url( $href ); ?>"<?php echo $li_url ? ' target="_blank" rel="noopener"' : ''; ?>>
							<?php if ( $thumb ) : ?>
								<div class="esc-card__media"><?php echo wp_get_attachment_image( $thumb, 'es-card', false, array( 'loading' => 'lazy' ) ); ?></div>
							<?php else : ?>
								<div class="esc-card__media es-linkedin-card__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M20.447 20.452h-3.554V14.84c0-1.338-.027-3.06-1.866-3.06-1.866 0-2.152 1.458-2.152 2.963v5.708H9.32V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.6 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.06 2.06 0 1 1 0-4.122 2.06 2.06 0 0 1 0 4.122zM7.119 20.452H3.558V9h3.56v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.454C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>
								</div>
							<?php endif; ?>
							<div class="esc-card__body">
								<div class="esc-card__meta">LinkedIn · <?php echo esc_html( $date_fmt ); ?></div>
								<h3 class="esc-card__title"><?php the_title(); ?></h3>
								<p class="esc-card__text"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_content() ), 22, '…' ) ); ?></p>
								<span class="esc-card__link">Zum Post ↗</span>
							</div>
						</a>
					<?php endwhile; wp_reset_postdata(); ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}
}
ESC_LinkedIn::init();
