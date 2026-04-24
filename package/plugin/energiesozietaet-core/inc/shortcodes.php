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

	/** News archive with featured (first) + grid — L3 block. */
	public static function news_featured( $atts ) {
		$atts = shortcode_atts( array( 'limit' => 6 ), $atts, 'es_news_featured' );
		$q = new WP_Query( array( 'post_type' => 'es_news', 'posts_per_page' => (int) $atts['limit'] ) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start();
		$i = 0;
		?>
		<div class="es-news-archive">
			<?php while ( $q->have_posts() ) : $q->the_post(); $i++;
				$thumb_id = get_post_thumbnail_id();
				$img = $thumb_id ? wp_get_attachment_image( $thumb_id, 'es-wide', false, array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;display:block;' ) ) : '<div class="es-ph-cat" style="height:100%;"><span>' . esc_html( get_the_title() ) . '</span></div>';
				if ( 1 === $i ) : ?>
					<a href="<?php the_permalink(); ?>" style="display:grid;grid-template-columns:1.3fr 1fr;gap:56px;margin-bottom:80px;color:#0E1A2B;">
						<div style="aspect-ratio:16/10;overflow:hidden;background:#F6F4EF;"><?php echo $img; ?></div>
						<div style="align-self:center;">
							<div style="font-size:11px;color:#95D708;letter-spacing:0.14em;text-transform:uppercase;margin-bottom:20px;font-family:var(--es-font-mono);">Featured · <?php echo esc_html( get_the_date() ); ?></div>
							<h2 style="font-size:clamp(28px,3.4vw,48px);line-height:1.1;font-weight:400;letter-spacing:-0.03em;margin:0 0 24px;"><?php the_title(); ?></h2>
							<p style="font-size:16px;color:#5A6577;line-height:1.6;margin:0 0 32px;"><?php echo esc_html( self::excerpt( get_post(), 36 ) ); ?></p>
							<span class="es-link">Weiterlesen →</span>
						</div>
					</a>
					<div style="border-top:1px solid #E4E7EC;padding-top:56px;">
						<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:56px;">
				<?php else : ?>
							<a href="<?php the_permalink(); ?>" style="display:grid;grid-template-columns:200px 1fr;gap:28px;color:#0E1A2B;">
								<div style="width:200px;height:160px;overflow:hidden;background:#F6F4EF;"><?php echo $img; ?></div>
								<div>
									<div style="font-size:11px;color:#95D708;letter-spacing:0.14em;text-transform:uppercase;margin-bottom:10px;font-family:var(--es-font-mono);"><?php echo esc_html( get_the_date() ); ?></div>
									<h3 style="font-size:20px;font-weight:500;line-height:1.25;letter-spacing:-0.015em;margin:0 0 10px;"><?php the_title(); ?></h3>
									<p style="font-size:14px;color:#5A6577;line-height:1.5;margin:0;"><?php echo esc_html( self::excerpt( get_post(), 22 ) ); ?></p>
								</div>
							</a>
				<?php endif;
			endwhile; wp_reset_postdata(); ?>
			</div></div>
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
		if ( ! $q->have_posts() ) { return ''; }

		ob_start(); ?>
		<div class="es-bereich__topics" style="grid-template-columns:repeat(3,1fr);">
			<?php $j = 0; while ( $q->have_posts() ) : $q->the_post();
				$sub = get_post_meta( get_the_ID(), 'es_subtitle', true );
				$num = str_pad( (string) ( $j + 1 ), 2, '0', STR_PAD_LEFT ); ?>
				<a class="es-bereich__topic" href="<?php the_permalink(); ?>">
					<div class="es-bereich__topic-name">
						<span></span>
						<h3><?php the_title(); ?></h3>
					</div>
					<p class="es-bereich__topic-desc"><?php echo esc_html( $sub ? wp_trim_words( wp_strip_all_tags( $sub ), 18, '…' ) : self::excerpt( get_post(), 18 ) ); ?></p>
				</a>
			<?php $j++; endwhile; wp_reset_postdata(); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function team( $atts ) {
		$atts = shortcode_atts( array(
			'columns' => 4,
			'limit'   => -1,
			'orderby' => 'menu_order title',
			'order'   => 'ASC',
		), $atts, 'es_team' );
		$q = new WP_Query( array(
			'post_type'      => 'es_team',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => $atts['orderby'],
			'order'          => $atts['order'],
		) );
		if ( ! $q->have_posts() ) { return ''; }
		$cols = max( 2, min( 4, (int) $atts['columns'] ) );
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $cols; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$role     = get_post_meta( get_the_ID(), 'es_role', true );
				$thumb_id = get_post_thumbnail_id();
				// Derive feld label from role (Rechtsanwalt → Recht, Steuerberater → Steuern, else Unternehmensberatung)
				$feld = '';
				if ( preg_match( '/Rechts?(anwalt|anwältin)/i', (string) $role ) || stripos( (string) $role, 'Rechtsberatung' ) !== false ) { $feld = 'Recht'; }
				elseif ( stripos( (string) $role, 'Steuer' ) !== false ) { $feld = 'Steuern'; }
				else { $feld = 'Unternehmensberatung'; }
				?>
				<a class="esc-team-card es-reveal" href="<?php the_permalink(); ?>">
					<div class="esc-team-card__photo">
						<?php if ( $thumb_id ) { echo wp_get_attachment_image( $thumb_id, 'es-team', false, array( 'loading' => 'lazy', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); }
						else { echo '<span class="esc-team-card__initial">' . esc_html( mb_substr( get_the_title(), 0, 1 ) ) . '</span>'; } ?>
					</div>
					<div class="esc-team-card__body">
						<div class="esc-team-card__feld"><?php echo esc_html( $feld ); ?></div>
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
		$q = new WP_Query( array( 'post_type' => 'es_karriere', 'posts_per_page' => (int) $atts['limit'] ) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $atts['columns']; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$dept = get_post_meta( get_the_ID(), 'es_department', true ); ?>
				<a class="esc-card es-reveal" href="<?php the_permalink(); ?>">
					<div class="esc-card__body">
						<div class="esc-card__meta"><?php echo esc_html( $dept ); ?></div>
						<h3 class="esc-card__title"><?php the_title(); ?></h3>
						<p class="esc-card__text"><?php echo esc_html( self::excerpt( get_post(), 22 ) ); ?></p>
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
						<div class="esc-event-row__loc">📍 <?php echo esc_html( $loc ? $loc : 'Düsseldorf' ); ?></div>
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
				$cats     = get_the_terms( get_the_ID(), 'es_news_kategorie' );
				$cat_name = ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : 'Kanzlei'; ?>
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
		// Keine Detailseiten — Publikationen führen direkt zur externen Quelle wenn vorhanden.
		$atts = shortcode_atts( array( 'layout' => 'list', 'limit' => -1 ), $atts, 'es_publikationen' );
		$q = new WP_Query( array( 'post_type' => 'es_publikation', 'posts_per_page' => (int) $atts['limit'], 'orderby' => 'date', 'order' => 'DESC' ) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start(); ?>
		<div class="esc-pub-list" style="border-top:1px solid #E4E7EC;">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$link = get_post_meta( get_the_ID(), 'es_link', true );
				$href = $link ? $link : '';
				$src  = get_post_meta( get_the_ID(), 'es_source', true );
				$auth = get_post_meta( get_the_ID(), 'es_author', true );
				$cat  = get_post_meta( get_the_ID(), 'es_cat', true );
				if ( ! $cat ) { $cat = 'Fachbeitrag'; } ?>
				<?php if ( $href ) : ?>
					<a class="esc-pub-row" href="<?php echo esc_url( $href ); ?>" target="_blank" rel="noopener">
				<?php else : ?>
					<div class="esc-pub-row" style="cursor:default;">
				<?php endif; ?>
					<div class="esc-pub-row__cat"><?php echo esc_html( $cat ); ?></div>
					<div>
						<h3 class="esc-pub-row__title"><?php the_title(); ?></h3>
						<?php if ( $auth ) : ?><p class="esc-pub-row__author"><?php echo esc_html( $auth ); ?></p><?php endif; ?>
					</div>
					<div class="esc-pub-row__src"><?php echo esc_html( $src ? $src : ( $href ? 'Externe Quelle ↗' : '' ) ); ?></div>
					<div class="esc-pub-row__arrow"><?php echo $href ? '↗' : ''; ?></div>
				<?php echo $href ? '</a>' : '</div>'; ?>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}
}
ESC_Shortcodes::init();
