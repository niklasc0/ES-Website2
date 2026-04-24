<?php
/**
 * Single Einzelleistung.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$beratungsfeld_terms = wp_get_post_terms( get_the_ID(), 'es_beratungsfeld' );
	$beratungsfeld = $beratungsfeld_terms && ! is_wp_error( $beratungsfeld_terms ) ? $beratungsfeld_terms[0] : null;
	$subtitle = es_meta( 'es_subtitle' );
	$bullets  = es_meta( 'es_bullets' );
	$closing  = es_meta( 'es_closing' );

	$crumbs = array( array( 'Leistungen', home_url( '/leistungen/' ) ) );
	if ( $beratungsfeld ) { $crumbs[] = array( $beratungsfeld->name, get_term_link( $beratungsfeld ) ); }
	$crumbs[] = array( get_the_title() );

	es_page_head( array(
		'eyebrow' => $beratungsfeld ? $beratungsfeld->name : 'Leistungen',
		'title'   => get_the_title(),
		'lead'    => $subtitle,
		'crumbs'  => $crumbs,
	) );
	?>

	<section class="es-section">
		<div class="es-wrap">
			<div class="es-prose">
				<?php the_content(); ?>

				<?php if ( is_array( $bullets ) && ! empty( $bullets ) ) : ?>
					<ul>
						<?php foreach ( $bullets as $b ) : ?>
							<li><?php echo wp_kses_post( $b ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $closing ) : ?>
					<div class="es-prose" style="background:var(--es-soft); padding:28px 32px; border-radius:var(--es-radius); margin-top:32px; border-left:3px solid var(--es-accent);">
						<?php echo wp_kses_post( wpautop( $closing ) ); ?>
					</div>
				<?php endif; ?>
			</div>

			<hr class="es-divider" />
			<?php if ( $beratungsfeld ) {
				es_back_link( get_term_link( $beratungsfeld ), 'Zurück zu ' . $beratungsfeld->name );
			} else {
				es_back_link( home_url( '/leistungen/' ), 'Zurück zu Leistungen' );
			} ?>
		</div>
	</section>

<?php endwhile;
get_footer();
