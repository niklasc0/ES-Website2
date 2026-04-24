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
		add_shortcode( 'es_karriere',        array( __CLASS__, 'karriere' ) );
		add_shortcode( 'es_veranstaltungen', array( __CLASS__, 'veranstaltungen' ) );
		add_shortcode( 'es_news',            array( __CLASS__, 'news' ) );
		add_shortcode( 'es_publikationen',   array( __CLASS__, 'publikationen' ) );
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

		$cols = max( 1, min( 4, (int) $atts['columns'] ) );
		ob_start();
		?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $cols; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$terms    = wp_get_post_terms( get_the_ID(), 'es_beratungsfeld' );
				$sub      = get_post_meta( get_the_ID(), 'es_subtitle', true );
				$thumb_id = get_post_thumbnail_id(); ?>
				<a class="esc-card es-reveal" href="<?php the_permalink(); ?>">
					<?php if ( $thumb_id ) : ?>
						<div class="esc-card__media"><?php echo wp_get_attachment_image( $thumb_id, 'es-card', false, array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="esc-card__body">
						<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
							<div class="esc-card__meta"><?php echo esc_html( $terms[0]->name ); ?></div>
						<?php endif; ?>
						<h3 class="esc-card__title"><?php the_title(); ?></h3>
						<p class="esc-card__text"><?php echo esc_html( $sub ? wp_trim_words( wp_strip_all_tags( $sub ), 20, '…' ) : self::excerpt( get_post(), 20 ) ); ?></p>
						<span class="esc-card__link">Mehr erfahren →</span>
					</div>
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
				$role = get_post_meta( get_the_ID(), 'es_role', true );
				$thumb_id = get_post_thumbnail_id(); ?>
				<a class="esc-team-card es-reveal" href="<?php the_permalink(); ?>">
					<div class="esc-team-card__photo">
						<?php if ( $thumb_id ) { echo wp_get_attachment_image( $thumb_id, 'es-team', false, array( 'loading' => 'lazy' ) ); }
						else { echo '<span class="esc-team-card__initial">' . esc_html( mb_substr( get_the_title(), 0, 1 ) ) . '</span>'; } ?>
					</div>
					<div class="esc-team-card__body">
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
		$atts = shortcode_atts( array( 'columns' => 3, 'limit' => -1 ), $atts, 'es_veranstaltungen' );
		$q = new WP_Query( array(
			'post_type' => 'es_veranstaltung',
			'posts_per_page' => (int) $atts['limit'],
			'meta_key'  => 'es_start_date',
			'orderby'   => 'meta_value',
			'order'     => 'ASC',
		) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $atts['columns']; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$start = get_post_meta( get_the_ID(), 'es_start_date', true );
				$ts = $start ? strtotime( $start ) : false; ?>
				<a class="esc-card esc-event-card es-reveal" href="<?php the_permalink(); ?>">
					<?php if ( $ts ) : ?>
						<div class="esc-event-date">
							<span class="esc-event-date__day"><?php echo esc_html( date_i18n( 'j', $ts ) ); ?></span>
							<span class="esc-event-date__month"><?php echo esc_html( date_i18n( 'M Y', $ts ) ); ?></span>
						</div>
					<?php endif; ?>
					<div class="esc-card__body">
						<h3 class="esc-card__title"><?php the_title(); ?></h3>
						<p class="esc-card__text"><?php echo esc_html( self::excerpt( get_post(), 22 ) ); ?></p>
						<span class="esc-card__link">Details →</span>
					</div>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
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
				$thumb_id = get_post_thumbnail_id(); ?>
				<a class="esc-card es-reveal" href="<?php the_permalink(); ?>">
					<?php if ( $thumb_id ) : ?>
						<div class="esc-card__media"><?php echo wp_get_attachment_image( $thumb_id, 'es-card', false, array( 'loading' => 'lazy' ) ); ?></div>
					<?php endif; ?>
					<div class="esc-card__body">
						<div class="esc-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
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
		$atts = shortcode_atts( array( 'columns' => 2, 'limit' => -1 ), $atts, 'es_publikationen' );
		$q = new WP_Query( array( 'post_type' => 'es_publikation', 'posts_per_page' => (int) $atts['limit'] ) );
		if ( ! $q->have_posts() ) { return ''; }
		ob_start(); ?>
		<div class="esc-grid esc-grid--cols-<?php echo (int) $atts['columns']; ?>">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$link = get_post_meta( get_the_ID(), 'es_link', true ); ?>
				<article class="esc-card es-reveal">
					<div class="esc-card__body">
						<div class="esc-card__meta">Publikation</div>
						<h3 class="esc-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="esc-card__text"><?php echo esc_html( self::excerpt( get_post(), 32 ) ); ?></p>
						<div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;">
							<a class="esc-card__link" href="<?php the_permalink(); ?>">Details →</a>
							<?php if ( $link ) : ?><a class="esc-card__link" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener">Externer Link ↗</a><?php endif; ?>
						</div>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}
}
ESC_Shortcodes::init();
