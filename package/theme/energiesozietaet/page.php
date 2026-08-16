<?php
/**
 * Page template.
 *
 * Elementor-Seiten rendern sich selbst. Nicht-Elementor-Seiten (Impressum,
 * Datenschutz) bekommen einen einfachen schmalen Container mit Mockup-Hero.
 *
 * @package Energiesozietaet
 */
get_header();

while ( have_posts() ) :
	the_post();
	$built_with_elementor = class_exists( 'Elementor\\Plugin' ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() ) && \Elementor\Plugin::$instance->documents->get( get_the_ID() )->is_built_with_elementor();
	if ( $built_with_elementor ) {
		the_content();
	} else {
		?>
		<section style="padding:80px 0 40px;">
			<div class="es-wrap es-wrap--narrow">
				<div class="es-article__crumb"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color:inherit;">Home</a>  /  <?php echo esc_html( get_the_title() ); ?></div>
				<div class="es-eyebrow">Rechtliches</div>
				<h1 style="font-size:clamp(36px,4.5vw,56px);font-weight:400;letter-spacing:-0.035em;margin:0;"><?php the_title(); ?></h1>
			</div>
		</section>
		<section style="padding:40px 0 140px;">
			<div class="es-wrap es-wrap--narrow" style="font-size:var(--es-fs-body);line-height:1.7;">
				<?php the_content(); ?>
			</div>
		</section>
		<?php
	}
endwhile;

get_footer();
