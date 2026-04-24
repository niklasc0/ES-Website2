<?php
/**
 * Single Publikation.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$external = es_meta( 'es_link' );
	$authors  = es_meta( 'es_authors' );
	$pub_date = es_meta( 'es_publication_date' );

	es_page_head( array(
		'eyebrow' => 'Publikation',
		'title'   => get_the_title(),
		'crumbs'  => array( array( 'Publikationen', home_url( '/publikationen/' ) ), array( get_the_title() ) ),
	) );
	?>

	<section class="es-section">
		<div class="es-wrap">
			<div class="es-prose"><?php the_content(); ?></div>

			<div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:32px;">
				<?php if ( $external ) : ?>
					<a class="es-btn es-btn--primary" href="<?php echo esc_url( $external ); ?>" target="_blank" rel="noopener">Publikation öffnen</a>
				<?php endif; ?>
			</div>

			<hr class="es-divider" />
			<?php es_back_link( home_url( '/publikationen/' ), 'Alle Publikationen' ); ?>
		</div>
	</section>

<?php endwhile;
get_footer();
