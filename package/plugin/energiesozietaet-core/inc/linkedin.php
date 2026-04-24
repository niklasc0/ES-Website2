<?php
/**
 * LinkedIn-Feed — Auto-Fetch via RSS-Bridge + Fallback auf CPT.
 *
 * LinkedIn selbst liefert keinen öffentlichen Feed; ein RSS-Bridge-Dienst
 * (rss.app, fetchrss.com, rss-bridge) bringt das Company-Profil aber auf
 * eine RSS-URL, die wir stündlich per fetch_feed() abholen (Transient-
 * Cache). Der Render-Pfad:
 *   1. RSS-URL in den Settings → fetch_rss_items() → Card-Grid
 *   2. Wenn leer/nicht konfiguriert → Fallback auf den CPT es_linkedin
 *   3. Wenn auch der leer ist → Empty-State mit Anleitung
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
			'rss_url'     => esc_url_raw( $input['rss_url']     ?? '' ),
			'cache_min'   => max( 5, min( 1440, (int) ( $input['cache_min'] ?? 60 ) ) ),
		);
	}

	public static function get_opt( $key, $default = '' ) {
		$defaults = array( 'profile_url' => '', 'rss_url' => '', 'cache_min' => 60 );
		$opts = array_merge( $defaults, (array) get_option( self::OPT, array() ) );
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
		// Cache-Leer-Action
		if ( isset( $_POST['esc_li_clear_cache'] ) && check_admin_referer( 'esc_li_clear' ) ) {
			delete_transient( 'esc_li_rss' );
			echo '<div class="notice notice-success"><p>RSS-Cache geleert.</p></div>';
		}
		?>
		<div class="wrap">
			<h1>LinkedIn-Feed</h1>
			<p>Zieh automatisch die neuesten Posts aus einem LinkedIn-Profil oder einer Unternehmensseite. Technisch nötig ist eine <strong>RSS-URL</strong>, weil LinkedIn selbst keinen öffentlichen Feed ausliefert. Einmal eintragen — danach aktualisiert sich der Home-Feed von selbst.</p>

			<form method="post" action="options.php">
				<?php settings_fields( 'esc_li_group' ); ?>
				<table class="form-table"><tbody>
					<tr><th scope="row"><label>LinkedIn-Profil-URL</label></th><td>
						<input type="url" name="<?php echo esc_attr( self::OPT ); ?>[profile_url]" value="<?php echo esc_attr( self::get_opt( 'profile_url' ) ); ?>" style="width:100%;max-width:560px;" placeholder="https://www.linkedin.com/company/energiesozietaet/" />
						<p class="description">Ziel des „Alle Posts ansehen"-Links unter dem Feed. Wird im Home-Blueprint als Default verwendet.</p>
					</td></tr>
					<tr><th scope="row"><label>RSS-Feed-URL <span style="color:#95D708;">(für Auto-Fetch)</span></label></th><td>
						<input type="url" name="<?php echo esc_attr( self::OPT ); ?>[rss_url]" value="<?php echo esc_attr( self::get_opt( 'rss_url' ) ); ?>" style="width:100%;max-width:560px;" placeholder="https://rss.app/feeds/..." />
						<p class="description">
							<strong>So erstellst Du die RSS-URL in 2 Minuten:</strong><br>
							1. Bei <a href="https://rss.app/new-rss-feed/linkedin-rss-feed" target="_blank" rel="noopener">rss.app</a> (kostenloser Plan reicht) registrieren.<br>
							2. „New Feed" → LinkedIn-Profil- oder Company-URL einfügen → RSS-URL kopieren.<br>
							3. Hier einfügen und speichern. Fertig — das Plugin zieht ab sofort die neuesten Posts automatisch.<br>
							Alternative Dienste: <a href="https://fetchrss.com/" target="_blank" rel="noopener">fetchrss.com</a>, <a href="https://rss-bridge.org/" target="_blank" rel="noopener">rss-bridge.org</a> (selbst-gehostet).<br>
							<em>Wenn leer: das Plugin greift automatisch auf die LinkedIn-Post-Einträge im CPT zurück (Demo-Inhalte).</em>
						</p>
					</td></tr>
					<tr><th scope="row"><label>Cache-Dauer</label></th><td>
						<input type="number" min="5" max="1440" step="5" name="<?php echo esc_attr( self::OPT ); ?>[cache_min]" value="<?php echo esc_attr( (int) self::get_opt( 'cache_min', 60 ) ); ?>" style="width:120px;" /> <span style="color:#5A6577;">Minuten</span>
						<p class="description">Wie lange der RSS-Feed zwischengespeichert wird (empfohlen: 60 min). Reduziert Latenz und entlastet den RSS-Dienst.</p>
					</td></tr>
				</tbody></table>
				<?php submit_button(); ?>
			</form>

			<hr>
			<form method="post">
				<?php wp_nonce_field( 'esc_li_clear' ); ?>
				<p>
					<button type="submit" name="esc_li_clear_cache" value="1" class="button">Cache jetzt leeren</button>
					<span style="color:#5A6577;margin-left:10px;font-size:13px;">Zwingt den Feed beim nächsten Seitenaufruf, neu zu laden.</span>
				</p>
			</form>
		</div>
		<?php
	}

	/**
	 * Holt Feed-Items per SimplePie/fetch_feed(), Cache via Transient.
	 * Rückgabe: Array mit ['title','link','date','excerpt','image'] oder [] bei Fehler.
	 */
	protected static function fetch_rss_items( $limit ) {
		$url = self::get_opt( 'rss_url', '' );
		if ( ! $url ) { return array(); }
		$cache_min = (int) self::get_opt( 'cache_min', 60 );
		$ttl_sec   = max( 300, $cache_min * 60 );

		$cache_key = 'esc_li_rss';
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		if ( ! function_exists( 'fetch_feed' ) ) {
			require_once ABSPATH . WPINC . '/feed.php';
		}
		// Adjust SimplePie's default cache lifetime ad-hoc
		add_filter( 'wp_feed_cache_transient_lifetime', function () use ( $ttl_sec ) { return $ttl_sec; } );
		$feed = fetch_feed( $url );
		if ( is_wp_error( $feed ) ) { return array(); }
		$max = $feed->get_item_quantity( min( 20, max( 1, $limit * 2 ) ) );
		$items = $feed->get_items( 0, $max );
		$out = array();
		foreach ( $items as $item ) {
			$title   = (string) $item->get_title();
			$link    = (string) $item->get_permalink();
			$date    = (string) $item->get_date( 'Y-m-d' );
			$content = (string) $item->get_description();
			$excerpt = wp_trim_words( wp_strip_all_tags( $content ), 28, '…' );
			// Bild-Kandidaten: enclosure, media:content, erstes <img>
			$image = '';
			$enc = $item->get_enclosure();
			if ( $enc && method_exists( $enc, 'get_link' ) && $enc->get_link() && preg_match( '/\.(jpe?g|png|webp|gif)$/i', $enc->get_link() ) ) {
				$image = $enc->get_link();
			}
			if ( ! $image && preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m ) ) {
				$image = $m[1];
			}
			$out[] = array(
				'title'   => $title,
				'link'    => $link,
				'date'    => $date ?: current_time( 'Y-m-d' ),
				'excerpt' => $excerpt,
				'image'   => $image,
			);
		}
		set_transient( $cache_key, $out, $ttl_sec );
		return array_slice( $out, 0, $limit );
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
		$atts  = shortcode_atts( $defaults, $atts, 'es_linkedin_posts' );
		$limit = max( 1, min( 12, (int) $atts['limit'] ) );

		// 1) RSS-Feed versuchen (primärer Modus)
		$items = self::fetch_rss_items( $limit );

		// 2) Wenn kein Feed konfiguriert oder leer: Fallback auf CPT-Posts
		if ( empty( $items ) ) {
			$q = new WP_Query( array(
				'post_type'      => 'es_linkedin',
				'posts_per_page' => $limit,
				'orderby'        => 'meta_value',
				'meta_key'       => 'es_li_date',
				'order'          => 'DESC',
			) );
			while ( $q->have_posts() ) { $q->the_post();
				$thumb_id = get_post_thumbnail_id();
				$items[] = array(
					'title'   => get_the_title(),
					'link'    => (string) get_post_meta( get_the_ID(), 'es_li_url', true ),
					'date'    => (string) get_post_meta( get_the_ID(), 'es_li_date', true ),
					'excerpt' => wp_trim_words( wp_strip_all_tags( get_the_content() ), 22, '…' ),
					'image'   => $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'es-card' ) : '',
				);
			}
			wp_reset_postdata();
		}

		ob_start(); ?>
		<section class="es-linkedin-feed">
			<div class="es-linkedin-feed__head">
				<div>
					<div class="es-eyebrow"><?php echo esc_html( $atts['eyebrow'] ); ?></div>
					<h2 class="es-linkedin-feed__title"><?php echo esc_html( $atts['title'] ); ?></h2>
				</div>
				<?php if ( $atts['profile_url'] ) : ?>
					<a class="es-link" href="<?php echo esc_url( $atts['profile_url'] ); ?>" target="_blank" rel="noopener">Alle Posts ansehen &uarr;</a>
				<?php endif; ?>
			</div>
			<?php if ( empty( $items ) ) : ?>
				<p class="es-linkedin-feed__empty" style="color:#5A6577;">Noch kein LinkedIn-Feed hinterlegt. Unter <em>LinkedIn &rarr; Einstellungen</em> die RSS-URL eintragen (Anleitung dort).</p>
			<?php else : ?>
				<div class="esc-grid esc-grid--cols-3">
					<?php foreach ( $items as $it ) :
						$href     = $it['link'] ? $it['link'] : '#';
						$target   = $it['link'] ? ' target="_blank" rel="noopener"' : '';
						$date_fmt = $it['date'] ? date_i18n( 'j. F Y', strtotime( $it['date'] ) ) : ''; ?>
						<a class="esc-card es-linkedin-card" href="<?php echo esc_url( $href ); ?>"<?php echo $target; ?>>
							<?php if ( ! empty( $it['image'] ) ) : ?>
								<div class="esc-card__media"><img src="<?php echo esc_url( $it['image'] ); ?>" alt="" loading="lazy" style="width:100%;height:100%;object-fit:cover;" /></div>
							<?php else : ?>
								<div class="esc-card__media es-linkedin-card__icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" width="40" height="40" fill="currentColor"><path d="M20.447 20.452h-3.554V14.84c0-1.338-.027-3.06-1.866-3.06-1.866 0-2.152 1.458-2.152 2.963v5.708H9.32V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.6 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.06 2.06 0 1 1 0-4.122 2.06 2.06 0 0 1 0 4.122zM7.119 20.452H3.558V9h3.56v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.454C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.225 0z"/></svg>
								</div>
							<?php endif; ?>
							<div class="esc-card__body">
								<div class="esc-card__meta">LinkedIn<?php if ( $date_fmt ) { echo ' &middot; ' . esc_html( $date_fmt ); } ?></div>
								<h3 class="esc-card__title"><?php echo esc_html( $it['title'] ); ?></h3>
								<p class="esc-card__text"><?php echo esc_html( $it['excerpt'] ); ?></p>
								<span class="esc-card__link">Zum Post &uarr;</span>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
		<?php
		return ob_get_clean();
	}
}
ESC_LinkedIn::init();
