<?php
/**
 * Single Einzelleistung — schmaler Artikel-Layout, Breadcrumb zur Bereichs-Seite.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$terms  = wp_get_post_terms( get_the_ID(), 'es_beratungsfeld' );
	$bf     = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$sub    = es_meta( 'es_subtitle' );
	$bullets = es_meta( 'es_bullets' );
	$closing = es_meta( 'es_closing' );
	?>

	<section class="es-article">
		<header class="es-article__header">
			<div class="es-article__crumb">
				<a href="<?php echo esc_url( home_url( '/leistungen/' ) ); ?>" style="color:inherit;">Leistungen</a>
				<?php if ( $bf ) : ?>
					  /  <a href="<?php echo esc_url( home_url( '/' . $bf->slug . '/' ) ); ?>" style="color:inherit;"><?php echo esc_html( $bf->name ); ?></a>
				<?php endif; ?>
				  /  <?php echo esc_html( get_the_title() ); ?>
			</div>
			<?php if ( $bf ) : ?><div class="es-article__eyebrow"><?php echo esc_html( $bf->name ); ?></div><?php endif; ?>
			<h1 class="es-article__title"><?php the_title(); ?></h1>
			<?php if ( $sub ) : ?><p class="es-article__lede"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
		</header>

		<div class="es-article__body">
			<?php the_content(); ?>

			<?php if ( is_array( $bullets ) && ! empty( $bullets ) ) : ?>
				<ul>
					<?php foreach ( $bullets as $b ) : ?><li><?php echo wp_kses_post( $b ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $closing ) : ?>
				<blockquote><?php echo wp_kses_post( wpautop( $closing ) ); ?></blockquote>
			<?php endif; ?>
		</div>

		<div class="es-wrap es-wrap--narrow" style="padding-top:56px;">
			<?php if ( $bf ) : ?>
				<a class="es-team-single__back" href="<?php echo esc_url( home_url( '/' . $bf->slug . '/' ) ); ?>">← Zurück zu <?php echo esc_html( $bf->name ); ?></a>
			<?php else : ?>
				<a class="es-team-single__back" href="<?php echo esc_url( home_url( '/leistungen/' ) ); ?>">← Zurück zu Leistungen</a>
			<?php endif; ?>
		</div>
	</section>

<?php endwhile;
get_footer();
