<?php
/**
 * Single Einzelleistung – redaktionelles Leistungs-Layout:
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
	$bf_name = $bf ? es_term_name_ml( $bf ) : es_t( 'Leistungen', 'Services' );
	$ap_id   = (int) es_meta( 'es_ansprechpartner' );
	$ap      = $ap_id ? get_post( $ap_id ) : null;
	$ap_ok   = ( $ap && 'es_team' === $ap->post_type && 'publish' === $ap->post_status );
	?>

	<section class="es-leistung">
		<div class="es-wrap">
			<header class="es-leistung__header">
				<nav class="es-leistung__crumb" aria-label="Brotkrumen">
					<a href="<?php echo esc_url( home_url( '/leistungen/' ) ); ?>"><?php echo esc_html( es_t( 'Leistungen', 'Services' ) ); ?></a>
					<span aria-hidden="true">/</span>
					<a href="<?php echo esc_url( $bf_url ); ?>"><?php echo esc_html( $bf_name ); ?></a>
					<span aria-hidden="true">/</span>
					<span class="es-leistung__crumb-current"><?php echo esc_html( get_the_title() ); ?></span>
				</nav>
				<?php if ( $bf ) : ?>
					<div class="es-eyebrow es-eyebrow--accent"><?php echo esc_html( es_term_name_ml( $bf ) ); ?></div>
				<?php endif; ?>
				<h1 class="es-leistung__title"><?php the_title(); ?></h1>
				<?php if ( $sub ) : ?>
					<p class="es-leistung__lede"><?php echo esc_html( $sub ); ?></p>
				<?php endif; ?>
			</header>

			<div class="es-leistung__grid">
				<div class="es-leistung__main">
					<div class="es-article__body es-leistung__body">
						<?php
						// Aufklapp-Rubriken: bevorzugt aus dem strukturierten Feld
						// (es_accordion / es_accordion_en). Fallback-Kette in EN:
						// EN-Rubriken → EN-Inhalt mit h3-Automatik → DE-Rubriken.
						$acc = get_post_meta( get_the_ID(), es_is_en() ? 'es_accordion_en' : 'es_accordion', true );
						$rendered_content = apply_filters( 'the_content', get_the_content() );
						if ( es_is_en() && ( ! is_array( $acc ) || empty( $acc ) ) && substr_count( $rendered_content, '<h3' ) < 3 ) {
							$acc = get_post_meta( get_the_ID(), 'es_accordion', true );
						}
						// Standardisierte Zwischenüberschriften (grün):
						// "Schwerpunkte unserer Beratung" über den Punkten,
						// "Unsere Leistungen zum Thema …" über den Aufklapp-Rubriken.
						$print_vorteile = function () use ( &$bullets ) {
							if ( ! is_array( $bullets ) || empty( $bullets ) ) { return; }
							echo '<h2 class="es-leistung__subhead">' . esc_html( es_t( 'Schwerpunkte unserer Beratung', 'Focus areas of our advice' ) ) . '</h2><ul>';
							foreach ( $bullets as $b ) { echo '<li>' . wp_kses_post( $b ) . '</li>'; }
							echo '</ul>';
							$bullets = null;
						};
						if ( is_array( $acc ) && ! empty( $acc ) ) {
							echo $rendered_content; // phpcs:ignore WordPress.Security.EscapeOutput
							$print_vorteile();
							echo '<h2 class="es-leistung__subhead">' . esc_html( es_t( 'Unsere Leistungen zum Thema', 'Our services in' ) ) . ' ' . esc_html( get_the_title() ) . '</h2>';
							foreach ( $acc as $acc_item ) {
								$acc_title   = trim( (string) ( $acc_item['title'] ?? '' ) );
								$acc_content = trim( (string) ( $acc_item['content'] ?? '' ) );
								if ( '' === $acc_title && '' === $acc_content ) { continue; }
								echo '<details class="es-acc"><summary>' . esc_html( $acc_title ) . '</summary><div class="es-acc__body">' . wp_kses_post( wpautop( $acc_content ) ) . '</div></details>';
							}
						} else {
							echo es_accordionize( $rendered_content ); // phpcs:ignore WordPress.Security.EscapeOutput
							$print_vorteile();
						}
						?>

						<?php if ( $closing ) : ?>
							<blockquote><?php $closing_html = wp_kses_post( wpautop( $closing ) ); echo function_exists( 'esc_link_team_names' ) ? esc_link_team_names( $closing_html ) : $closing_html; // phpcs:ignore WordPress.Security.EscapeOutput ?></blockquote>
						<?php endif; ?>
					</div>
				</div>

				<aside class="es-leistung__aside">
					<div class="es-leistung__card">
						<?php if ( $ap_ok ) :
							$ap_role   = get_post_meta( $ap->ID, 'es_role', true );
							$ap_gender = get_post_meta( $ap->ID, 'es_gender', true );
							$ap_label  = es_is_en() ? 'Your contact' : ( ( 'w' === $ap_gender ) ? 'Ihre Ansprechpartnerin' : ( ( 'd' === $ap_gender ) ? 'Ihr:e Ansprechpartner:in' : 'Ihr Ansprechpartner' ) );
							$ap_photo  = get_the_post_thumbnail( $ap->ID, 'es-team', array( 'style' => 'width:100%;height:100%;object-fit:cover;', 'alt' => esc_attr( $ap->post_title ) ) );
							?>
							<div class="es-eyebrow"><?php echo esc_html( $ap_label ); ?></div>
							<div class="es-leistung__card-person">
								<div class="es-leistung__card-photo"><?php echo $ap_photo; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
								<div>
									<div class="es-leistung__card-name"><?php echo esc_html( $ap->post_title ); ?></div>
									<?php if ( $ap_role ) : ?><div class="es-leistung__card-role"><?php echo esc_html( $ap_role ); ?></div><?php endif; ?>
								</div>
							</div>
						<?php else : ?>
							<div class="es-eyebrow"><?php echo esc_html( es_t( 'Kontakt', 'Contact' ) ); ?></div>
							<p class="es-leistung__card-title"><?php echo esc_html( es_t( 'Persönliche Beratung.', 'Personal advice.' ) ); ?></p>
						<?php endif; ?>
						<p class="es-leistung__card-text"><?php echo esc_html( es_t( 'Bitte zögern Sie nicht, uns anzusprechen. Wir freuen uns auf Ihre Kontaktaufnahme.', 'Please do not hesitate to get in touch. We look forward to hearing from you.' ) ); ?></p>
						<a class="es-btn es-btn--paper" href="<?php echo esc_url( home_url( es_t( '/kontakt/', '/en/kontakt/' ) ) ); ?>"><?php echo esc_html( es_t( 'Termin anfragen', 'Request a meeting' ) ); ?> →</a>
					</div>
					<a class="es-leistung__back" href="<?php echo esc_url( $bf_url ); ?>">← <?php echo esc_html( es_t( 'Alle Leistungen:', 'All services:' ) ); ?> <?php echo esc_html( $bf_name ); ?></a>
				</aside>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
