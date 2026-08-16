<?php
/**
 * Single News-Beitrag – nach Mockup S1 News-Artikel.
 * Schmaler Header (780px), full-width Artikelbild, Fließtext in 720px.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$thumb_id = get_post_thumbnail_id();
	$felder   = get_the_terms( get_the_ID(), 'es_beratungsfeld' );
	$cats     = get_the_terms( get_the_ID(), 'es_news_kategorie' );
	$cat_name = ( $felder && ! is_wp_error( $felder ) ) ? $felder[0]->name : ( ( $cats && ! is_wp_error( $cats ) ) ? $cats[0]->name : es_t( 'Aktuelles', 'Latest' ) );
	$content  = trim( get_the_content() );
	$words    = str_word_count( wp_strip_all_tags( $content ) );
	$reading  = max( 1, (int) round( $words / 220 ) ); ?>

	<article class="es-article">
		<header class="es-article__header">
			<div class="es-article__crumb">
				<a href="<?php echo esc_url( home_url( es_t( '/news/', '/en/news/' ) ) ); ?>" style="color:inherit;">News</a>  /  <?php echo esc_html( $cat_name ); ?>
			</div>
			<div class="es-article__eyebrow"><?php echo esc_html( $cat_name ); ?> · <?php echo esc_html( get_the_date( es_t( 'j. F Y', 'j F Y' ) ) ); ?></div>
			<h1 class="es-article__title"><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) : ?>
				<p class="es-article__lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
			<div class="es-article__byline">
				<div class="es-article__byline-avatar">
					<?php
					$author_slug = es_meta( 'es_author_slug' );
					if ( $author_slug ) {
						$author = get_page_by_path( $author_slug, OBJECT, 'es_team' );
						if ( $author ) { echo get_the_post_thumbnail( $author->ID, 'es-team', array( 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); }
					} ?>
				</div>
				<div>
					<div class="es-article__byline-name"><?php echo esc_html( es_meta( 'es_author' ) ? es_meta( 'es_author' ) : 'Energiesozietät' ); ?></div>
					<div class="es-article__byline-meta"><?php echo (int) $reading; ?> <?php echo esc_html( es_t( 'Min. Lesezeit', 'min read' ) ); ?></div>
				</div>
			</div>
		</header>

		<?php if ( $thumb_id ) : ?>
			<figure class="es-article__hero" style="max-width:1040px;margin:0 auto 80px;padding:0 24px;">
				<?php echo wp_get_attachment_image( $thumb_id, 'large', false, array( 'loading' => 'eager', 'style' => 'width:100%;height:auto;aspect-ratio:16/9;object-fit:cover;display:block;' ) ); ?>
			</figure>
		<?php endif; ?>

		<div class="es-article__body es-article__body--justify" style="max-width:860px;margin-left:auto;margin-right:auto;padding:0 24px;">
			<?php the_content(); ?>
		</div>

		<div style="max-width:860px;margin:80px auto 0;padding:0 24px;">
			<a class="es-team-single__back" href="<?php echo esc_url( home_url( es_t( '/news/', '/en/news/' ) ) ); ?>">← <?php echo esc_html( es_t( 'Zurück zu den News', 'Back to news' ) ); ?></a>
		</div>
	</article>

<?php endwhile;
get_footer();
