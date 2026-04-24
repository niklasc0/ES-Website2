<?php
/**
 * Single Veranstaltung.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$start_date = es_meta( 'es_start_date' );
	$end_date   = es_meta( 'es_end_date' );
	$location   = es_meta( 'es_location' );
	$date_str   = '';
	if ( $start_date ) {
		$ts = strtotime( $start_date );
		$date_str = $ts ? date_i18n( 'j. F Y', $ts ) : $start_date;
		if ( $end_date && $end_date !== $start_date ) {
			$ts2 = strtotime( $end_date );
			if ( $ts2 ) { $date_str .= ' – ' . date_i18n( 'j. F Y', $ts2 ); }
		}
	}

	es_page_head( array(
		'eyebrow' => $date_str ? $date_str : 'Veranstaltung',
		'title'   => get_the_title(),
		'crumbs'  => array( array( 'Veranstaltungen', home_url( '/veranstaltungen/' ) ), array( get_the_title() ) ),
	) );
	?>

	<section class="es-section">
		<div class="es-wrap">
			<?php if ( has_post_thumbnail() ) : ?>
				<figure style="margin:0 0 40px; border-radius:var(--es-radius-lg); overflow:hidden;"><?php the_post_thumbnail( 'es-wide' ); ?></figure>
			<?php endif; ?>

			<div class="es-prose"><?php the_content(); ?></div>

			<?php if ( $location ) : ?>
				<p style="color:var(--es-muted); margin-top:24px;"><strong style="color:var(--es-ink);">Ort:</strong> <?php echo esc_html( $location ); ?></p>
			<?php endif; ?>

			<hr class="es-divider" />
			<?php es_back_link( home_url( '/veranstaltungen/' ), 'Alle Veranstaltungen' ); ?>
		</div>
	</section>

<?php endwhile;
get_footer();
