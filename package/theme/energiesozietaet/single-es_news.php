<?php
/**
 * Single news article (CPT).
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	es_page_head( array(
		'eyebrow' => get_the_date(),
		'title'   => get_the_title(),
		'crumbs'  => array( array( 'News', home_url( '/news/' ) ), array( get_the_title() ) ),
	) );
	?>
	<article class="es-section">
		<div class="es-wrap">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure style="margin:0 0 40px; border-radius:var(--es-radius-lg); overflow:hidden;"><?php the_post_thumbnail( 'es-wide' ); ?></figure>
			<?php endif; ?>
			<div class="es-prose"><?php the_content(); ?></div>
			<hr class="es-divider" />
			<?php es_back_link( home_url( '/news/' ), 'Alle News' ); ?>
		</div>
	</article>
<?php endwhile;
get_footer();
