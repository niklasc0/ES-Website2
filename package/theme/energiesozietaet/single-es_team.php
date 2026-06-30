<?php
/**
 * Single Team-Mitglied — nach Mockup S3.
 * Position über dem Namen (groß), keine Duplicate-Rolle, Kontakt-Box mit
 * E-Mail + Telefon, vCard-Download automatisch, Sections Schwerpunkte,
 * Werdegang, Publikationen (Reverse-Lookup via es_author_ids).
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$post_id  = get_the_ID();
	$role     = es_meta( 'es_role' );
	$email    = es_meta( 'es_email' );
	$phone    = es_meta( 'es_phone' );
	$linkedin = es_meta( 'es_linkedin' );
	$location = es_meta( 'es_location' );
	if ( ! $location ) { $location = 'Düsseldorf'; }
	$focus    = es_meta( 'es_focus_areas' );
	$career   = es_meta( 'es_career' );
	$more_bio = es_meta( 'es_more_bio' );
	$thumb_id = get_post_thumbnail_id();
	$vcard    = add_query_arg( array( 'es_vcard' => $post_id ), home_url( '/' ) );

	// Related Publications via es_author_ids
	$related_pubs = new WP_Query( array(
		'post_type' => 'es_publikation',
		'posts_per_page' => 6,
		'orderby' => 'date', 'order' => 'DESC',
		'meta_query' => array( array( 'key' => 'es_author_ids', 'value' => 'i:' . (int) $post_id . ';', 'compare' => 'LIKE' ) ),
	) );
	?>

	<section class="es-team-single">
		<div class="es-wrap">
			<div class="es-team-single__crumb">
				<a href="<?php echo esc_url( home_url( '/team/' ) ); ?>" style="color:inherit;">Team</a>  /  <?php echo esc_html( get_the_title() ); ?>
			</div>
			<div class="es-team-single__grid">
				<div class="es-team-single__col-left">
					<div class="es-team-single__photo">
						<?php if ( $thumb_id ) : ?>
							<?php echo wp_get_attachment_image( $thumb_id, 'es-team', false, array( 'loading' => 'eager', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
						<?php else : ?>
							<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#B6BAAF;font-size:64px;"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></div>
						<?php endif; ?>
					</div>

					<div class="es-team-single__contact-card">
						<div class="es-eyebrow" style="margin-bottom:16px;color:#95D708;">Kontakt</div>
						<?php if ( $email ) : ?>
							<div class="es-team-single__contact-row">
								<small>E-Mail</small>
								<a href="mailto:<?php echo esc_attr( $email ); ?>" style="color:inherit;"><?php echo esc_html( $email ); ?></a>
							</div>
						<?php endif; ?>
						<?php if ( $phone ) : ?>
							<div class="es-team-single__contact-row">
								<small>Telefon</small>
								<a href="tel:<?php echo esc_attr( str_replace( array( ' ', '-' ), '', $phone ) ); ?>" style="color:inherit;"><?php echo esc_html( $phone ); ?></a>
							</div>
						<?php endif; ?>
						<div class="es-team-single__contact-row">
							<small>Standort</small>
							<?php echo esc_html( $location ); ?>
						</div>
						<?php if ( $linkedin ) : ?>
							<div class="es-team-single__contact-row">
								<small>LinkedIn</small>
								<a href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener" style="color:inherit;">Profil ansehen ↗︎</a>
							</div>
						<?php endif; ?>
						<div class="es-team-single__contact-actions">
							<a class="es-btn es-btn--paper" href="mailto:<?php echo esc_attr( $email ? $email : 'info@energiesozietaet.de' ); ?>?subject=<?php echo esc_attr( 'Terminanfrage — ' . get_the_title() ); ?>">Termin vereinbaren →</a>
							<a class="es-btn es-btn--ghost-paper" href="<?php echo esc_url( $vcard ); ?>">Visitenkarte (vCard)</a>
						</div>
					</div>
				</div>

				<div>
					<?php if ( $role ) : ?>
						<div class="es-team-single__role-eyebrow"><?php echo esc_html( $role ); ?></div>
					<?php endif; ?>
					<h1 class="es-team-single__name"><?php the_title(); ?></h1>

					<div class="es-team-single__bio"><?php the_content(); ?></div>

					<?php if ( $more_bio ) : ?>
						<div class="es-team-single__section">
							<h3>Profil</h3>
							<div style="font-size:17px;line-height:1.7;color:#899092;"><?php echo wp_kses_post( wpautop( $more_bio ) ); ?></div>
						</div>
					<?php endif; ?>

					<?php if ( is_array( $focus ) && ! empty( $focus ) ) : ?>
						<div class="es-team-single__section">
							<h3>Ausgewählte Schwerpunkte</h3>
							<div class="es-team-single__focus">
								<?php foreach ( $focus as $item ) :
									$t = is_array( $item ) ? ( $item['title'] ?? '' ) : $item;
									if ( ! $t ) continue; ?>
									<div class="es-team-single__focus-item"><?php echo esc_html( $t ); ?></div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( is_array( $career ) && ! empty( $career ) ) : ?>
						<div class="es-team-single__section">
							<h3>Werdegang</h3>
							<div class="es-team-single__career">
								<?php foreach ( $career as $row ) :
									$w = is_array( $row ) ? ( $row['when'] ?? '' ) : '';
									$t = is_array( $row ) ? ( $row['what'] ?? '' ) : (string) $row; ?>
									<div class="es-team-single__career-row">
										<div class="es-team-single__career-when"><?php echo esc_html( $w ); ?></div>
										<div class="es-team-single__career-what"><?php echo esc_html( $t ); ?></div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( $related_pubs->have_posts() ) : ?>
						<div class="es-team-single__section">
							<h3>Publikationen (Auswahl)</h3>
							<div class="es-team-single__pubs">
								<?php while ( $related_pubs->have_posts() ) : $related_pubs->the_post();
									$link = es_meta( 'es_link' );
									if ( $link ) : ?>
										<a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener"><?php the_title(); ?> ↗︎</a>
									<?php else : ?>
										<span><?php the_title(); ?></span>
									<?php endif;
								endwhile; wp_reset_postdata(); ?>
							</div>
						</div>
					<?php endif; ?>

					<a class="es-team-single__back" href="<?php echo esc_url( home_url( '/team/' ) ); ?>">← Zurück zum Team</a>
				</div>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
