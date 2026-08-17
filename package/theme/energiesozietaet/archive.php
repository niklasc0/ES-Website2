<?php
/**
 * Archive fallback for all CPT & taxonomy archives.
 *
 * @package Energiesozietaet
 */
get_header();

$title   = '';
$eyebrow = '';
$lead    = '';
$back    = null;
$grid    = 'es-grid es-grid--3';

$obj = get_queried_object();

if ( is_post_type_archive( 'es_team' ) ) {
	$title   = 'Team';
	$eyebrow = 'Unser Team';
	$lead    = 'Ein erfahrenes Team, das seit vielen Jahren zusammenarbeitet – agil, pragmatisch, interdisziplinär.';
	$grid    = 'es-grid es-grid--4';
} elseif ( is_post_type_archive( 'es_einzelleistung' ) ) {
	$title   = 'Einzelleistungen';
	$eyebrow = 'Leistungen';
	$lead    = 'Unsere fachlichen Schwerpunkte in Recht, Steuern und Beratung.';
} elseif ( is_tax( 'es_beratungsfeld' ) ) {
	$title   = es_term_name_ml( $obj );
	$eyebrow = es_t( 'Leistungen', 'Services' );
	$lead    = $obj->description;
} elseif ( is_post_type_archive( 'es_karriere' ) ) {
	$title   = 'Karriere';
	$eyebrow = 'Jobs';
	$lead    = 'Entfalte Dich in einem jungen, schnell wachsenden Beratungsunternehmen.';
} elseif ( is_post_type_archive( 'es_veranstaltung' ) ) {
	$title   = 'Veranstaltungen';
	$eyebrow = 'Termine';
	$lead    = 'Wir laden Sie regelmäßig zu spannenden und für Sie relevanten Veranstaltungen von und mit unserer Kanzlei ein.';
} elseif ( is_post_type_archive( 'es_news' ) ) {
	$title   = 'News';
	$eyebrow = 'Aktuelles';
	$lead    = 'Relevante Branchenentwicklungen und Einblicke in unsere Arbeit für den Energiesektor.';
} elseif ( is_post_type_archive( 'es_publikation' ) ) {
	$title   = 'Publikationen';
	$eyebrow = 'Veröffentlichungen';
	$lead    = 'Eine Auswahl unserer Beiträge, Kommentare und Fachpublikationen.';
} else {
	$title   = get_the_archive_title();
	$eyebrow = '';
	$lead    = get_the_archive_description();
}

es_page_head( array(
	'eyebrow' => $eyebrow,
	'title'   => $title,
	'lead'    => $lead,
) );
?>

<section class="es-section">
	<div class="es-wrap">
		<?php if ( have_posts() ) : ?>
			<div class="<?php echo esc_attr( $grid ); ?>">
				<?php while ( have_posts() ) : the_post();
					if ( get_post_type() === 'es_team' ) : ?>
						<a class="es-team-card es-reveal" href="<?php the_permalink(); ?>">
							<div class="es-team-card__photo">
								<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'es-team' );
								else : ?><div style="width:100%;height:100%;background:var(--es-soft);display:flex;align-items:center;justify-content:center;color:var(--es-muted);font-family:var(--es-font-display);font-size:2.2rem;"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></div><?php endif; ?>
							</div>
							<div class="es-team-card__body">
								<h3 class="es-team-card__name"><?php the_title(); ?></h3>
								<p class="es-team-card__role"><?php echo esc_html( es_meta( 'es_role' ) ); ?></p>
							</div>
						</a>
					<?php elseif ( get_post_type() === 'es_veranstaltung' ) :
						$start = es_meta( 'es_start_date' );
						$ts    = $start ? strtotime( $start ) : false;
						?>
						<article class="es-card es-reveal">
							<div class="es-card__meta"><?php echo $ts ? esc_html( date_i18n( es_t( 'j. F Y', 'j F Y' ), $ts ) ) : ''; ?></div>
							<h3 class="es-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="es-card__text"><?php echo esc_html( es_excerpt( get_post(), 28 ) ); ?></p>
							<a class="es-card__link" href="<?php the_permalink(); ?>">Details</a>
						</article>
					<?php elseif ( get_post_type() === 'es_karriere' ) : ?>
						<article class="es-card es-reveal">
							<div class="es-card__meta"><?php echo esc_html( es_meta( 'es_department' ) ); ?></div>
							<h3 class="es-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="es-card__text"><?php echo esc_html( es_excerpt( get_post(), 28 ) ); ?></p>
							<a class="es-card__link" href="<?php the_permalink(); ?>">Zur Stellenbeschreibung</a>
						</article>
					<?php elseif ( get_post_type() === 'es_einzelleistung' ) :
						$terms = wp_get_post_terms( get_the_ID(), 'es_beratungsfeld' ); ?>
						<article class="es-card es-reveal">
							<div class="es-card__meta"><?php echo $terms && ! is_wp_error( $terms ) ? esc_html( es_term_name_ml( $terms[0] ) ) : esc_html( es_t( 'Leistung', 'Service' ) ); ?></div>
							<h3 class="es-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="es-card__text"><?php echo esc_html( es_excerpt( get_post(), 24 ) ); ?></p>
							<a class="es-card__link" href="<?php the_permalink(); ?>">Mehr erfahren</a>
						</article>
					<?php elseif ( get_post_type() === 'es_publikation' ) : ?>
						<article class="es-card es-reveal">
							<div class="es-card__meta">Publikation</div>
							<h3 class="es-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="es-card__text"><?php echo esc_html( es_excerpt( get_post(), 28 ) ); ?></p>
							<a class="es-card__link" href="<?php the_permalink(); ?>">Details</a>
						</article>
					<?php else : ?>
						<article class="es-card es-reveal">
							<div class="es-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
							<h3 class="es-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="es-card__text"><?php echo esc_html( es_excerpt( get_post(), 28 ) ); ?></p>
							<a class="es-card__link" href="<?php the_permalink(); ?>">Weiterlesen</a>
						</article>
					<?php endif;
				endwhile; ?>
			</div>
			<div style="margin-top:40px; text-align:center;">
				<?php the_posts_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'Keine Einträge gefunden.', 'energiesozietaet' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer();
