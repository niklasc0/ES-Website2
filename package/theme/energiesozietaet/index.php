<?php
/**
 * Fallback template – used when no more specific template matches.
 *
 * @package Energiesozietaet
 */
get_header();
?>

<?php if ( is_home() && ! is_front_page() ) {
	es_page_head( array(
		'eyebrow' => __( 'News', 'energiesozietaet' ),
		'title'   => __( 'Aktuelles aus der Kanzlei', 'energiesozietaet' ),
		'lead'    => __( 'Relevante Branchenentwicklungen und Einblicke in unsere Arbeit für den Energiesektor.', 'energiesozietaet' ),
	) );
} ?>

<section class="es-section">
	<div class="es-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="es-grid es-grid--3">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'es-card es-reveal' ); ?>>
						<?php if ( has_post_thumbnail() ) : ?>
							<div class="es-card__media"><?php the_post_thumbnail( 'es-card' ); ?></div>
						<?php endif; ?>
						<div class="es-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
						<h3 class="es-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p class="es-card__text"><?php echo esc_html( es_excerpt( get_post(), 26 ) ); ?></p>
						<a class="es-card__link" href="<?php the_permalink(); ?>">Weiterlesen</a>
					</article>
				<?php endwhile; ?>
			</div>

			<div style="margin-top:40px; text-align:center;">
				<?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
			</div>

		<?php else : ?>
			<p><?php esc_html_e( 'Keine Beiträge gefunden.', 'energiesozietaet' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer();
