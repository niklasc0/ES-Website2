<?php
/**
 * Grid shortcodes (also usable as Elementor widgets).
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Shortcodes {

	public static function init() {
		add_shortcode( 'es_einzelleistungen', array( __CLASS__, 'einzelleistungen' ) );
		add_shortcode( 'es_team',            array( __CLASS__, 'team' ) );
		add_shortcode( 'es_team_photo',      array( __CLASS__, 'team_photo' ) );
		add_shortcode( 'es_karriere',        array( __CLASS__, 'karriere' ) );
		add_shortcode( 'es_veranstaltungen', array( __CLASS__, 'veranstaltungen' ) );
		add_shortcode( 'es_news',            array( __CLASS__, 'news' ) );
		add_shortcode( 'es_publikationen',   array( __CLASS__, 'publikationen' ) );
		add_shortcode( 'es_news_featured',   array( __CLASS__, 'news_featured' ) );
		add_shortcode( 'es_pub_teaser',      array( __CLASS__, 'pub_teaser' ) );
		add_shortcode( 'es_ansprechpartner', array( __CLASS__, 'ansprechpartner' ) );
		add_shortcode( 'es_mandanten',       array( __CLASS__, 'mandanten' ) );
		add_shortcode( 'es_field_image',     array( __CLASS__, 'field_image' ) );

		// Nur [es_field_image] in Elementor-HTML-Widgets ausfuehren
		// (HTML-Widgets verarbeiten Shortcodes sonst nicht).
		add_filter( 'elementor/widget/render_content', array( __CLASS__, 'run_field_image_in_html' ), 10, 2 );
	}

	public static function run_field_image_in_html( $content, $widget ) {
		if ( is_object( $widget ) && method_exists( $widget, 'get_name' )
			&& 'html' === $widget->get_name() && false !== strpos( $content, '[es_field_image' ) ) {
			return do_shortcode( $content );
		}
		return $content;
	}

	/**
	 * [es_field_image field="steuerberatung"]
	 * Beitragsbild der Beratungsfeld-Seite (sonst Platzhalter mit grünem Schimmer).
	 */
	public static function field_image( $atts ) {
		$atts  = shortcode_atts( array( 'field' => '' ), $atts, 'es_field_image' );
		$slug  = sanitize_title( $atts['field'] );
		$page  = $slug ? get_page_by_path( $slug ) : null;
		if ( ! $page && $slug ) {
			// Seite per Slug finden, auch wenn verschachtelt (z. B. /leistungen/rechtsberatung/)
			$found = get_posts( array( 'name' => $slug, 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1 ) );
			if ( $found ) { $page = $found[0]; }
		}
		$thumb = $page ? get_post_thumbnail_id( $page->ID ) : 0;
		$title = $page ? $page->post_title : ucwords( str_replace( '-', ' ', $slug ) );
		if ( $thumb ) {
			return '<div class="es-bereich__img">'
				. wp_get_attachment_image( $thumb, 'es-wide', false, array(
					'style' => 'width:100%;height:100%;object-fit:cover;display:block;',
					'alt'   => esc_attr( $title ),
				) )
				. '</div>';
		}
		return '<div class="es-bereich__img"><div class="es-ph-cat"><span>' . esc_html( $title ) . '</span></div></div>';
	}

	/**
	 * [es_mandanten items="A|B|C|..."]
	 * Rendert eine 2-Spalten-Liste mit nummerierten Einträgen. Pipe-getrennt.
	 */
	public static function mandanten( $atts ) {
		$atts  = shortcode_atts( array( 'items' => '' ), $atts, 'es_mandanten' );
		$items = array_values( array_filter( array_map( 'trim', explode( '|', html_entity_decode( (string) $atts['items'], ENT_QUOTES, 'UTF-8' ) ) ) ) );
		if ( empty( $items ) ) { return ''; }
		ob_start(); ?>
		<ul class="es-mandanten">
			<?php foreach ( $items as $i => $it ) : ?>
				<li><span class="es-mandanten__num"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span> <?php echo esc_html( $it ); ?></li>
			<?php endforeach; ?>
		</ul>
		<?php
		return ob_get_clean();
	}

	/**
	 * [es_ansprechpartner members="slug1,slug2" cta_url="/kontakt/" cta_label="Termin anfragen"]
	 * Rendert die Ansprechpartner-Bar (Foto + Name + Rolle) + CTA-Button.
	 * Member-Slugs werden in der Reihenfolge der Argumente gerendert.
	 */
	public static function ansprechpartner( $atts ) {
		$atts = shortcode_atts( array(
			'members'   => '',
			'cta_url'   => '/kontakt/',
			'cta_label' => 'Termin anfragen',
			'eyebrow'   => 'Ihre Ansprechpartner',
		), $atts, 'es_ansprechpartner' );
		$slugs = array_filter( array_map( 'trim', explode( ',', (string) $atts['members'] ) ) );
		if ( empty( $slugs ) ) { return ''; }
		ob_start(); ?>
		<div class="es-ansprech">
			<div class="es-eyebrow" style="margin:0 40px 0 0;flex-shrink:0;"><?php echo esc_html( $atts['eyebrow'] ); ?></div>
			<div class="es-ansprech__people">
				<?php foreach ( $slugs as $slug ) :
					$p = get_page_by_path( $slug, OBJECT, 'es_team' );
					if ( ! $p ) { continue; }
					$role = (string) get_post_meta( $p->ID, 'es_role', true ); ?>
					<a href="<?php echo esc_url( get_permalink( $p ) ); ?>">
						<div class="es-ansprech__photo"><?php echo do_shortcode( '[es_team_photo slug="' . esc_attr( $slug ) . '" size=52]' ); ?></div>
						<div>
							<div class="es-ansprech__name"><?php echo esc_html( $p->post_title ); ?></div>
							<div class="es-ansprech__role"><?php echo esc_html( $role ); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
			<a class="es-btn es-btn--primary" href="<?php echo esc_url( $atts['cta_url'] ); ?>"><?php echo esc_html( $atts['cta_label'] ); ?> →</a>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Single Team-Member photo by slug.
	 * [es_team_photo slug="prof-dr-sven-joachim-otto" size=120]
	 */
	public static function team_photo( $atts ) {
		$atts = shortcode_atts( array( 'slug' => '', 'size' => 120 ), $atts, 'es_team_photo' );
		if ( ! $atts['slug'] ) { return ''; }
		$p = get_page_by_path( $atts['slug'], OBJECT, 'es_team' );
		if ( ! $p ) { return ''; }
		$thumb = get_the_post_thumbnail( $p->ID, 'es-team', array( 'style' => 'width:100%;height:100%;object-fit:cover;display:block;', 'loading' => 'lazy' ) );
		return $thumb ? $thumb : '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#8591A3;font-size:48px;">' . esc_html( mb_substr( $p->post_title, 0, 1 ) ) . '</div>';
	}

	/** News archive: Featured-Post + paginiertes Grid. Unterstützt Per-Page
	 *  Auswahl via ?pp=8|16|32 und Seiten via ?npage=N. */
	public static function news_featured( $atts ) {
		$atts = shortcode_atts( array( 'limit' => 9 ), $atts, 'es_news_featured' );
		$allowed_pp = array( 8, 16, 32 );
		$pp = isset( $_GET['pp'] ) ? (int) $_GET['pp'] : 8;
		if ( ! in_array( $pp, $allowed_pp, true ) ) { $pp = 8; }
		$page = isset( $_GET['npage'] ) ? max( 1, (int) $_GET['npage'] ) : 1;

		// Featured-Artikel = neuester
		$fq = new WP_Query( array( 'post_type' => 'es_news', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC' ) );
		if ( ! $fq->have_posts() ) { return ''; }
		$fq->the_post();
		$f_id    = get_the_ID();
		$f_thumb = get_post_thumbnail_id();
		$f_img   = $f_thumb ? wp_get_attachment_image( $f_thumb, 'es-wide', false, array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;display:block;' ) ) : '<div class="es-ph-cat" style="height:100%;"><span>' . esc_html( get_the_title() ) . '</span></div>';
		$f_title   = get_the_title();
		$f_link    = get_permalink();
		$f_date    = get_the_date();
		$f_excerpt = self::excerpt( get_post(), 36 );
		wp_reset_postdata();

		// Rest paginiert, ohne Featured
		$rq = new WP_Query( array(
			'post_type'      => 'es_news',
			'posts_per_page' => $pp,
			'paged'          => $page,
			'post__not_in'   => array( $f_id ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		$total_pages = (int) $rq->max_num_pages;
		$base = remove_query_arg( array( 'pp', 'npage' ) );

		ob_start(); ?>
		<div class="es-news-archive">
			<a class="es-news-archive__featured" href="<?php echo esc_url( $f_link ); ?>" style="display:grid;grid-template-columns:1.3fr 1fr;gap:56px;margin-bottom:56px;color:#0E1A2B;">
				<div style="aspect-ratio:16/10;overflow:hidden;background:#303030;"><?php echo $f_img; ?></div>
				<div style="align-self:center;">
					<div style="font-size:11px;color:#95D708;letter-spacing:0.14em;text-transform:uppercase;margin-bottom:20px;font-family:var(--es-font-mono);">Featured &middot; <?php echo esc_html( $f_date ); ?></div>
					<h2 style="font-size:clamp(28px,3.4vw,48px);line-height:1.1;font-weight:400;letter-spacing:-0.03em;margin:0 0 24px;"><?php echo esc_html( $f_title ); ?></h2>
					<p style="font-size:16px;color:#5A6577;line-height:1.6;margin:0 0 32px;"><?php echo esc_html( $f_excerpt ); ?></p>
					<span class="es-link">Weiterlesen &rarr;</span>
				</div>
			</a>

			<?php if ( $rq->have_posts() ) : ?>
			<div class="es-news-toolbar">
				<div class="es-news-toolbar__pp">
					<span class="es-news-toolbar__label">Pro Seite</span>
					<?php foreach ( $allowed_pp as $opt ) :
						$url = esc_url( add_query_arg( array( 'pp' => $opt, 'npage' => 1 ), $base ) ); ?>
						<a class="es-news-toolbar__pill<?php echo $opt === $pp ? ' is-active' : ''; ?>" href="<?php echo $url; ?>"><?php echo (int) $opt; ?></a>
					<?php endforeach; ?>
				</div>
				<?php if ( $total_pages > 1 ) :
					$prev = $page > 1 ? $page - 1 : 0;
					$next = $page < $total_pages ? $page + 1 : 0; ?>
				<div class="es-news-toolbar__pager">
					<a class="es-news-toolbar__pill<?php echo $prev ? '' : ' is-disabled'; ?>" href="<?php echo $prev ? esc_url( add_query_arg( array( 'pp' => $pp, 'npage' => $prev ), $base ) ) : '#'; ?>"<?php echo $prev ? '' : ' aria-disabled="true" onclick="return false;"'; ?>>&larr; Vorherige</a>
					<span class="es-news-toolbar__status">Seite <?php echo (int) $page; ?> / <?php echo (int) $total_pages; ?></span>
					<a class="es-news-toolbar__pill<?php echo $next ? '' : ' is-disabled'; ?>" href="<?php echo $next ? esc_url( add_query_arg( array( 'pp' => $pp, 'npage' => $next ), $base ) ) : '#'; ?>"<?php echo $next ? '' : ' aria-disabled="true" onclick="return false;"'; ?>>Nächste &rarr;</a>
				</div>
				<?php endif; ?>
			</div>

			<div class="es-news-archive__grid">
				<?php while ( $rq->have_posts() ) : $rq->the_post();
					$thumb_id = get_post_thumbnail_id();
					$img = $thumb_id ? wp_get_attachment_image( $thumb_id, 'es-card', false, array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ) : '<div class="es-ph-cat" style="height:100%;"><span>' . esc_html( get_the_title() ) . '</span></div>'; ?>
					<a href="<?php the_permalink(); ?>" style="display:grid;grid-template-columns:200px 1fr;gap:28px;color:#0E1A2B;">
						<div style="width:200px;height:160px;overflow:hidden;background:#303030;"><?php echo $img; ?></div>
						<div>
							<div style="font-size:11px;color:#95D708;letter-spacing:0.14em;text-transform:uppercase;margin-bottom:10px;font-family:var(--es-font-mono);"><?php echo esc_html( get_the_date() ); ?></div>
							<h3 style="font-size:20px;font-weight:500;line-height:1.25;letter-spacing:-0.015em;margin:0 0 10px;"><?php the_title(); ?></h3>
							<p style="font-size:14px;color:#5A6577;line-height:1.5;margin:0;"><?php echo esc_html( self::excerpt( get_post(), 22 ) ); ?></p>
						</div>
					</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>

			<?php if ( $total_pages > 1 ) :
				$prev = $page > 1 ? $page - 1 : 0;
				$next = $page < $total_pages ? $page + 1 : 0; ?>
			<div class="es-news-toolbar es-news-toolbar--bottom">
				<div class="es-news-toolbar__pager">
					<a class="es-news-toolbar__pill<?php echo $prev ? '' : ' is-disabled'; ?>" href="<?php echo $prev ? esc_url( add_query_arg( array( 'pp' => $pp, 'npage' => $prev ), $base ) ) : '#'; ?>"<?php echo $prev ? '' : ' aria-disabled="true" onclick="return false;"'; ?>>&larr; Vorherige</a>
					<span class="es-news-toolbar__status">Seite <?php echo (int) $page; ?> / <?php echo (int) $total_pages; ?></span>
					<a class="es-news-toolbar__pill<?php echo $next ? '' : ' is-disabled'; ?>" href="<?php echo $next ? esc_url( add_query_arg( array( 'pp' => $pp, 'npage' => $next ), $base ) ) : '#'; ?>"<?php echo $next ? '' : ' aria-disabled="true" onclick="return false;"'; ?>>Nächste &rarr;</a>
				</div>
			</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	protected static function excerpt( $post, $words = 22 ) {
		if ( ! empty( $post->post_excerpt ) ) { return wp_strip_all_tags( $post->post_excerpt ); }
		return wp_trim_words( wp_strip_all_tags( $post->post_content ), $words, '…' );
	}

	/**
	 * [es_einzelleistungen beratungsfeld="rechtsberatung" columns="3" limit="-1"]
	 * Automatically renders all Einzelleistungen that are tagged with the given Beratungsfeld.
	 */
	public static function einzelleistungen( $atts ) {
		$atts = shortcode_atts( array(
			'beratungsfeld' => '',
			'columns'       => 3,
			'limit'         => -1,
			'orderby'       => 'menu_order title',
			'order'         => 'ASC',
			'eyebrow'       => '',
		), $atts, 'es_einzelleistungen' );

		$args = array(
			'post_type'      => 'es_einzelleistung',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
		);
		if ( $atts['beratungsfeld'] ) {
			$args['tax_query'] = array( array(
				'taxonomy' => 'es_beratungsfeld',
				'field'    => 'slug',
				'terms'    => array_map( 'trim', explode( ',', $atts['beratungsfeld'] ) ),
			) );
		}
		$q = new WP_Query( $args );
		if ( ! $q->have_posts() ) {
			return '<div class="es-bereich__topics-empty" style="padding:24px 0;color:#5A6577;font-size:14px;">In diesem Bereich sind noch keine Einzelleistungen angelegt.</div>';
		}

		$cols = max( 1, min( 3, (int) ( $atts['columns'] ?? 3 ) ) );
		ob_start();
		if ( ! empty( $atts['eyebrow'] ) ) {
			echo '<p class="es-eyebrow">' . esc_html( $atts['eyebrow'] ) . '</p>';
		}
		?>
		<div class="es-bereich__topics" style="grid-template-columns:repeat(<?php echo (int) $cols; ?>,1fr);">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$sub_raw  = trim( wp_strip_all_tags( (string) get_post_meta( get_the_ID(), 'es_subtitle', true ) ) );
				$body_raw = $sub_raw ? $sub_raw : trim( wp_strip_all_tags( get_the_content() ) );
				// Trunc nach dem ersten Satzpunkt — maximal 160 Zeichen Fallback
				if ( preg_match( '/^(.{20,160}?[\.!\?])\s/u', $body_raw, $m ) ) {
					$short = $m[1];
				} else {
					$short = wp_trim_words( $body_raw, 20, '…' );
				} ?>
				<a class="es-bereich__topic" href="<?php the_permalink(); ?>">
					<div class="es-bereich__topic-name">
						<span></span>
						<h3><?php the_title(); ?></h3>
					</div>
					<p class="es-bereich__topic-desc"><?php echo esc_html( $short ); ?></p>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function team( $atts ) {
		$atts = shortcode_atts( array(
			'columns' => 4,
			'limit'   => -1,
			'field'   => '', // rechtsberatung|steuerberatung|unternehmensberatung|management
			'filter'  => '0', // "1" → rendert Filter-Pills oben
			'orderby' => 'menu_order title',
			'order'   => 'ASC',
		), $atts, 'es_team' );
		$active_field = isset( $_GET['feld'] ) ? sanitize_text_field( wp_unslash( $_GET['feld'] ) ) : (string) $atts['field'];
		$q_args = array(
			'post_type'      => 'es_team',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
		);
		if ( $active_field ) {
			$q_args['meta_query'] = array( array( 'key' => 'es_field', 'value' => $active_field ) );
		}
		$q = new WP_Query( $q_args );
		$cols = max( 2, min( 4, (int) $atts['columns'] ) );

		$filter_html = '';
		if ( '1' === (string) $atts['filter'] ) {
			$fields = array(
				''                     => 'Alle',
				'rechtsberatung'       => 'Rechtsberatung',
				'steuerberatung'       => 'Steuerberatung',
				'unternehmensberatung' => 'Unternehmensberatung',
				'management'           => 'Büroleitung',
			);
			$base = remove_query_arg( 'feld' );
			$filter_html = '<div class="es-team-filter">';
			$filter_html .= '<div class="es-eyebrow" style="margin:0 28px 0 0;">Filter</div><div class="es-team-filter__pills">';
			foreach ( $fields as $slug => $label ) {
				$url = $slug ? esc_url( add_query_arg( 'feld', $slug ) ) : esc_url( $base );
				$active = ( (string) $active_field === (string) $slug ) ? ' is-active' : '';
				$filter_html .= '<a class="es-team-filter__pill' . $active . '" href="' . $url . '">' . esc_html( $label ) . '</a>';
			}
			$filter_html .= '</div><div class="es-team-filter__count">' . (int) $q->found_posts . ' Teammitglieder</div></div>';
		}

		if ( ! $q->have_posts() ) {
			$empty = '<p style="color:#5A6577;font-size:15px;">Aktuell keine Teammitglieder in diesem Bereich.</p>';
			return $filter_html . $empty;
		}

		$labels = array(
			'rechtsberatung' => 'Recht',
			'steuerberatung' => 'Steuern',
			'unternehmensberatung' => 'Unternehmensberatung',
			'management' => 'Büroleitung',
		);

		ob_start();
		echo $filter_html;
		?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $cols; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$role     = get_post_meta( get_the_ID(), 'es_role', true );
				$thumb_id = get_post_thumbnail_id();
				$field    = (string) get_post_meta( get_the_ID(), 'es_field', true );
				$feld_lbl = $labels[ $field ] ?? ( $field ? ucfirst( $field ) : '' ); ?>
				<a class="esc-team-card es-reveal" href="<?php the_permalink(); ?>">
					<div class="esc-team-card__photo">
						<?php if ( $thumb_id ) { echo wp_get_attachment_image( $thumb_id, 'es-team', false, array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); }
						else { echo '<span class="esc-team-card__initial">' . esc_html( mb_substr( get_the_title(), 0, 1 ) ) . '</span>'; } ?>
					</div>
					<div class="esc-team-card__body">
						<?php if ( $feld_lbl ) : ?><div class="esc-team-card__feld"><?php echo esc_html( $feld_lbl ); ?></div><?php endif; ?>
						<h3 class="esc-team-card__name"><?php the_title(); ?></h3>
						<p class="esc-team-card__role"><?php echo esc_html( $role ); ?></p>
					</div>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}

	public static function karriere( $atts ) {
		$atts = shortcode_atts( array( 'columns' => 3, 'limit' => -1 ), $atts, 'es_karriere' );
		$q = new WP_Query( array(
			'post_type'      => 'es_karriere',
			'posts_per_page' => (int) $atts['limit'],
			// Eindeutige, steuerbare Reihenfolge: zuerst das "Reihenfolge"-Feld
			// (Seiteneigenschaften), dann neueste zuerst, ID als eindeutiger
			// Tiebreaker -> in jedem Browser/Cache identisch.
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' ),
		) );
		if ( ! $q->have_posts() ) { return ''; }
		$field_map = array(
			'rechtsberatung'       => 'Rechtsberatung',
			'steuerberatung'       => 'Steuerberatung',
			'unternehmensberatung' => 'Unternehmensberatung',
			'management'           => 'Büroleitung',
		);
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $atts['columns']; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$thumb_id   = get_post_thumbnail_id();
				$department = (string) get_post_meta( get_the_ID(), 'es_department', true );
				$field_slug = (string) get_post_meta( get_the_ID(), 'es_field', true );
				$field_label = ( $field_slug && isset( $field_map[ $field_slug ] ) ) ? $field_map[ $field_slug ] : '';
				if ( ! $field_label ) {
					$lower = strtolower( $department );
					if ( strpos( $lower, 'recht' ) !== false )      { $field_label = 'Rechtsberatung'; }
					elseif ( strpos( $lower, 'steuer' ) !== false ) { $field_label = 'Steuerberatung'; }
					elseif ( strpos( $lower, 'consulting' ) !== false || strpos( $lower, 'unternehmen' ) !== false ) { $field_label = 'Unternehmensberatung'; }
					else { $field_label = 'Kanzlei'; }
				}
				$location = (string) get_post_meta( get_the_ID(), 'es_location', true );
				if ( ! $location ) { $location = 'Düsseldorf'; }
				$emp_type = (string) get_post_meta( get_the_ID(), 'es_employment_type', true );
				if ( ! $emp_type ) { $emp_type = 'Vollzeit'; }
				$start_date = (string) get_post_meta( get_the_ID(), 'es_start_date', true );
				$entry_str  = $start_date ? date_i18n( 'j. F Y', strtotime( $start_date ) ) : 'ab sofort';
				?>
				<a class="esc-card es-reveal" href="<?php the_permalink(); ?>">
					<div class="esc-card__media">
						<?php if ( $thumb_id ) {
							echo wp_get_attachment_image( $thumb_id, 'es-card', false, array( 'loading' => 'lazy' ) );
						} else { ?>
							<div class="es-ph-cat"><span><?php echo esc_html( $field_label ); ?></span></div>
						<?php } ?>
					</div>
					<div class="esc-card__body">
						<h3 class="esc-card__title"><?php the_title(); ?></h3>
						<dl class="esc-card__facts">
							<div><dt>Bereich</dt><dd><?php echo esc_html( $field_label ); ?></dd></div>
							<div><dt>Standort</dt><dd><?php echo esc_html( $location ); ?></dd></div>
							<div><dt>Anstellung</dt><dd><?php echo esc_html( $emp_type ); ?></dd></div>
							<div><dt>Eintritt</dt><dd><?php echo esc_html( $entry_str ); ?></dd></div>
						</dl>
						<span class="esc-card__link">Zur Stellenbeschreibung →</span>
					</div>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}

	public static function veranstaltungen( $atts ) {
		$atts = shortcode_atts( array( 'layout' => 'row', 'columns' => 2, 'limit' => -1 ), $atts, 'es_veranstaltungen' );
		$q = new WP_Query( array(
			'post_type' => 'es_veranstaltung',
			'posts_per_page' => (int) $atts['limit'],
			'meta_key'  => 'es_start_date',
			'orderby'   => 'meta_value',
			'order'     => 'ASC',
		) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start();
		if ( 'row' === $atts['layout'] ) : ?>
			<div class="esc-event-list" style="border-top:1px solid #E4E7EC;">
				<?php while ( $q->have_posts() ) : $q->the_post();
					$start = get_post_meta( get_the_ID(), 'es_start_date', true );
					$ts    = $start ? strtotime( $start ) : false;
					$loc   = get_post_meta( get_the_ID(), 'es_location', true );
					$kind  = get_post_meta( get_the_ID(), 'es_kind', true ); ?>
					<a class="esc-event-row" href="<?php the_permalink(); ?>">
						<div>
							<div class="esc-event-row__day"><?php echo esc_html( $ts ? date_i18n( 'd', $ts ) : '—' ); ?></div>
							<div class="esc-event-row__month"><?php echo esc_html( $ts ? date_i18n( 'M Y', $ts ) : '' ); ?></div>
						</div>
						<h3 class="esc-event-row__title"><?php the_title(); ?></h3>
						<div class="esc-event-row__kind"><?php echo esc_html( $kind ? $kind : 'Veranstaltung' ); ?></div>
						<div class="esc-event-row__loc"><?php echo esc_html( $loc ? $loc : 'Düsseldorf' ); ?></div>
						<div class="esc-event-row__arrow">→</div>
					</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php else :
			// Card layout (2 per row, wider than tall)
			$cols = max( 1, min( 3, (int) $atts['columns'] ) ); ?>
			<div class="esc-grid esc-grid--cols-<?php echo (int) $cols; ?>">
				<?php while ( $q->have_posts() ) : $q->the_post();
					$start = get_post_meta( get_the_ID(), 'es_start_date', true );
					$ts    = $start ? strtotime( $start ) : false;
					$thumb_id = get_post_thumbnail_id();
					$loc   = get_post_meta( get_the_ID(), 'es_location', true ); ?>
					<a class="esc-card es-reveal" href="<?php the_permalink(); ?>">
						<div class="esc-card__media" style="aspect-ratio:3/2;">
							<?php if ( $thumb_id ) {
								echo wp_get_attachment_image( $thumb_id, 'es-wide', false, array( 'loading' => 'lazy' ) );
							} else { ?>
								<div class="es-ph-cat" style="aspect-ratio:auto;height:100%;"><span>Veranstaltung</span></div>
							<?php } ?>
						</div>
						<div class="esc-card__body">
							<div class="esc-card__meta"><?php echo esc_html( $ts ? date_i18n( 'd. M Y', $ts ) : '' ); ?><?php if ( $loc ) { echo ' · ' . esc_html( $loc ); } ?></div>
							<h3 class="esc-card__title"><?php the_title(); ?></h3>
							<p class="esc-card__text"><?php echo esc_html( self::excerpt( get_post(), 22 ) ); ?></p>
							<span class="esc-card__link">Details →</span>
						</div>
					</a>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		<?php endif;
		return ob_get_clean();
	}

	public static function news( $atts ) {
		$atts = shortcode_atts( array( 'columns' => 3, 'limit' => 6 ), $atts, 'es_news' );
		$q = new WP_Query( array(
			'post_type' => 'es_news',
			'posts_per_page' => (int) $atts['limit'],
		) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $atts['columns']; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$thumb_id = get_post_thumbnail_id();
				$felder   = get_the_terms( get_the_ID(), 'es_beratungsfeld' );
				$cats     = get_the_terms( get_the_ID(), 'es_news_kategorie' );
				$cat_name = ( $felder && ! is_wp_error( $felder ) ) ? $felder[0]->name : ( ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'Aktuelles' ); ?>
				<a class="esc-card es-reveal" href="<?php the_permalink(); ?>">
					<div class="esc-card__media">
						<?php if ( $thumb_id ) {
							echo wp_get_attachment_image( $thumb_id, 'es-card', false, array( 'loading' => 'lazy' ) );
						} else { ?>
							<div class="es-ph-cat"><span><?php echo esc_html( $cat_name ); ?></span></div>
						<?php } ?>
					</div>
					<div class="esc-card__body">
						<div class="esc-card__meta"><?php echo esc_html( $cat_name ); ?> · <?php echo esc_html( get_the_date() ); ?></div>
						<h3 class="esc-card__title"><?php the_title(); ?></h3>
						<p class="esc-card__text"><?php echo esc_html( self::excerpt( get_post(), 22 ) ); ?></p>
						<span class="esc-card__link">Weiterlesen →</span>
					</div>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}

	public static function publikationen( $atts ) {
		// Jahres-gruppierte Liste nach Datum sortiert. Kein Detail — externer Link führt direkt zur Quelle.
		$atts = shortcode_atts( array( 'layout' => 'years', 'limit' => -1, 'field' => '' ), $atts, 'es_publikationen' );
		$q_args = array( 'post_type' => 'es_publikation', 'posts_per_page' => (int) $atts['limit'], 'orderby' => 'date', 'order' => 'DESC' );
		if ( $atts['field'] ) {
			$q_args['meta_query'] = array( array( 'key' => 'es_fields', 'value' => sanitize_text_field( $atts['field'] ), 'compare' => 'LIKE' ) );
		}
		$q = new WP_Query( $q_args );
		if ( ! $q->have_posts() ) { return '<p style="color:#5A6577;">Keine Publikationen gefunden.</p>'; }

		// Gruppierung nach Jahr
		$years = array();
		while ( $q->have_posts() ) { $q->the_post();
			$years[ get_the_date( 'Y' ) ][] = get_the_ID();
		}
		wp_reset_postdata();
		krsort( $years );

		ob_start();
		?>
		<div class="esc-pub-years">
			<?php foreach ( $years as $year => $ids ) : ?>
				<div class="esc-pub-year">
					<aside class="esc-pub-year__label">
						<div class="esc-pub-year__num"><?php echo esc_html( $year ); ?></div>
						<div class="esc-pub-year__count"><?php echo (int) count( $ids ); ?> Einträge</div>
					</aside>
					<div class="esc-pub-year__items">
						<?php foreach ( $ids as $pid ) :
							$link = (string) get_post_meta( $pid, 'es_link', true );
							$src  = (string) get_post_meta( $pid, 'es_source', true );
							$auth = (string) get_post_meta( $pid, 'es_author', true );
							$cat  = (string) get_post_meta( $pid, 'es_cat', true );
							if ( ! $cat ) { $cat = 'Fachbeitrag'; }
							if ( $link ) : ?>
								<a class="esc-pub-row" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">
							<?php else : ?>
								<div class="esc-pub-row esc-pub-row--nolink">
							<?php endif; ?>
								<div class="esc-pub-row__cat"><?php echo esc_html( $cat ); ?></div>
								<div>
									<h3 class="esc-pub-row__title"><?php echo esc_html( get_the_title( $pid ) ); ?></h3>
									<?php if ( $auth || $src ) : ?>
										<p class="esc-pub-row__author">
											<?php echo esc_html( $auth ); ?><?php if ( $auth && $src ) echo ' · '; ?><?php echo esc_html( $src ); ?>
										</p>
									<?php endif; ?>
								</div>
								<?php if ( $link ) : ?>
									<span class="esc-pub-row__cta">Zur Publikation <span class="esc-pub-row__arrow">→</span></span>
								<?php else : ?>
									<span></span>
								<?php endif; ?>
							<?php echo $link ? '</a>' : '</div>'; ?>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php return ob_get_clean();
	}

	/** Fachbeiträge im Beratungsfeld — 3 neueste mit Card-Layout. */
	public static function pub_teaser( $atts ) {
		$atts = shortcode_atts( array( 'field' => '', 'limit' => 3 ), $atts, 'es_pub_teaser' );
		$q_args = array( 'post_type' => 'es_publikation', 'posts_per_page' => (int) $atts['limit'], 'orderby' => 'date', 'order' => 'DESC' );
		if ( $atts['field'] ) {
			$q_args['meta_query'] = array( array( 'key' => 'es_fields', 'value' => sanitize_text_field( $atts['field'] ), 'compare' => 'LIKE' ) );
		}
		$q = new WP_Query( $q_args );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-3">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$link = (string) get_post_meta( get_the_ID(), 'es_link', true );
				$cat  = (string) get_post_meta( get_the_ID(), 'es_cat', true ); if ( ! $cat ) { $cat = 'Fachbeitrag'; }
				$src  = (string) get_post_meta( get_the_ID(), 'es_source', true );
				$tag  = $link ? 'a' : 'article';
				$href = $link ? ' href="' . esc_url( $link ) . '" target="_blank" rel="noopener"' : ''; ?>
				<<?php echo $tag; ?> class="esc-card"<?php echo $href; ?> style="padding:32px;">
					<div class="esc-card__meta"><?php echo esc_html( $cat ); ?></div>
					<h3 style="font-size:19px;font-weight:500;line-height:1.3;letter-spacing:-0.01em;margin:16px 0 20px;"><?php the_title(); ?></h3>
					<?php if ( $src ) : ?><div style="font-size:12px;color:#8591A3;font-family:var(--es-font-mono);margin-bottom:20px;"><?php echo esc_html( $src ); ?></div><?php endif; ?>
					<span class="esc-card__link"><?php echo $link ? 'Zur Publikation ↗︎' : 'Lesen'; ?></span>
				</<?php echo $tag; ?>>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}
}
ESC_Shortcodes::init();
