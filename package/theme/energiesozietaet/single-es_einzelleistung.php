<?php
/**
 * Single Einzelleistung — redaktionelles Leistungs-Layout:
 * Header (Breadcrumb, Eyebrow, Titel, optionale Lede) + zweispaltig
 * (Artikeltext mit klarer Typo-Hierarchie + Sticky-Kontakt-Aside).
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$terms   = wp_get_post_terms( get_the_ID(), 'es_beratungsfeld' );
	$bf      = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	$sub     = es_meta( 'es_subtitle' );
	$bullets = es_meta( 'es_bullets' );
	$closing = es_meta( 'es_closing' );
	$bf_url  = $bf ? home_url( '/' . $bf->slug . '/' ) : home_url( '/leistungen/' );
	$bf_name = $bf ? $bf->name : 'Leistungen';
	$ap_id   = (int) es_meta( 'es_ansprechpartner' );
	$ap      = $ap_id ? get_post( $ap_id ) : null;
	$ap_ok   = ( $ap && 'es_team' === $ap->post_type && 'publish' === $ap->post_status );
	?>

	<section class="es-leistung">
		<div class="es-wrap">
			<header class="es-leistung__header">
				<nav class="es-leistung__crumb" aria-label="Brotkrumen">
					<a href="<?php echo esc_url( home_url( '/leistungen/' ) ); ?>">Leistungen</a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $bf_url ); ?>"><?php echo esc_html( $bf_name ); ?></a>
					<span aria-hidden="true">/</span>
					<span class="es-leistung__crumb-current"><?php echo esc_html( get_the_title() ); ?></span>
				</nav>
				<?php if ( $bf ) : ?>
					<div class="es-eyebrow es-eyebrow--accent"><?php echo esc_html( $bf->name ); ?></div>
				<?php endif; ?>
				<h1 class="es-leistung__title"><?php the_title(); ?></h1>
				<?php if ( $sub ) : ?>
					<p class="es-leistung__lede"><?php echo esc_html( $sub ); ?></p>
				<?php endif; ?>
			</header>

			<div class="es-leistung__grid">
				<div class="es-leistung__main">
					<div class="es-article__body es-leistung__body">
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
				</div>

				<aside class="es-leistung__aside">
					<div class="es-leistung__card">
						<?php if ( $ap_ok ) :
							$ap_role  = get_post_meta( $ap->ID, 'es_role', true );
							$ap_photo = get_the_post_thumbnail( $ap->ID, 'es-team', array( 'style' => 'width:100%;height:100%;object-fit:cover;', 'alt' => esc_attr( $ap->post_title ) ) );
							?>
							<div class="es-eyebrow">Ihr Ansprechpartner</div>
							<div class="es-leistung__card-person">
								<div class="es-leistung__card-photo"><?php echo $ap_photo; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
								<div>
									<div class="es-leistung__card-name"><?php echo esc_html( $ap->post_title ); ?></div>
									<?php if ( $ap_role ) : ?><div class="es-leistung__card-role"><?php echo esc_html( $ap_role ); ?></div><?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="es-eyebrow">Kontakt</div>
							<p class="es-leistung__card-title">Persönliche Beratung.</p>
						<?php endif; ?>
						<p class="es-leistung__card-text">Sie haben ein konkretes Vorhaben zum Thema <?php echo esc_html( get_the_title() ); ?>? Wir beraten Sie gern!</p>
						<a class="es-btn es-btn--paper" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">Termin anfragen →</a>
					</div>
					<a class="es-leistung__back" href="<?php echo esc_url( $bf_url ); ?>">← Alle Leistungen: <?php echo esc_html( $bf_name ); ?></a>
				</aside>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
