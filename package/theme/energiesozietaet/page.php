<?php
/**
 * Page template — Elementor controls layout, we keep chrome minimal.
 *
 * @package Energiesozietaet
 */
get_header();

while ( have_posts() ) :
	the_post();
	// Elementor renders the entire page when the page was built with it.
	if ( ! class_exists( 'Elementor\\Plugin' ) || ! \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor() ) {
		es_page_head( array(
			'eyebrow' => '',
			'title'   => get_the_title(),
		) );
		?>
		<section class="es-section">
			<div class="es-wrap">
				<div class="es-prose"><?php the_content(); ?></div>
			</div>
		</section>
		<?php
	} else {
		the_content();
	}
endwhile;

get_footer();
