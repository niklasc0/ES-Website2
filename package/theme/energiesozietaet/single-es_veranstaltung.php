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
	$kind       = es_meta( 'es_kind' );
	if ( ! $kind ) { $kind = 'Veranstaltung'; }
	$reg_url    = es_meta( 'es_registration_url' );
	$thumb_id   = get_post_thumbnail_id();

	$ts  = $start_date ? strtotime( $start_date ) : false;
	$ts2 = $end_date ? strtotime( $end_date ) : false;
	$upcoming = $ts && $ts >= time(); ?>

	<section class="es-event-single">
		<div style="max-width:960px;margin:0 auto;padding:0 24px;">
			<div class="es-article__crumb">
				<a href="<?php echo esc_url( home_url( '/veranstaltungen/' ) ); ?>" style="color:inherit;"><?php echo esc_html( es_t( 'Veranstaltungen', 'Events' ) ); ?></a>
			</div>

			<div style="display:flex;align-items:flex-start;gap:40px;flex-wrap:wrap;margin-bottom:28px;">
				<?php if ( $ts ) : ?>
					<div class="es-event-single__datecard" style="margin:0;">
						<strong><?php echo esc_html( date_i18n( 'd', $ts ) ); ?></strong>
						<span><?php echo esc_html( date_i18n( 'M Y', $ts ) ); ?></span>
						<?php if ( $ts2 && $ts2 !== $ts ) : ?>
							<span style="color:#B6BAAF;margin:0 8px;">–</span>
							<strong style="color:#122023;"><?php echo esc_html( date_i18n( 'd', $ts2 ) ); ?></strong>
							<span><?php echo esc_html( date_i18n( 'M Y', $ts2 ) ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div style="padding-top:18px;flex:1;min-width:220px;">
					<div class="es-article__eyebrow" style="margin-bottom:0;"><?php echo esc_html( $kind ); ?><?php if ( $upcoming ) : ?> · <span style="color:#95D708;"><?php echo esc_html( es_t( 'Anmeldung möglich', 'Registration open' ) ); ?></span><?php endif; ?></div>
				</div>
			</div>

			<h1 class="es-article__title"><?php the_title(); ?></h1>

			<div class="es-stelle-single__meta-row">
				<?php if ( $location ) : ?><div><strong><?php echo esc_html( es_t( 'Ort:', 'Location:' ) ); ?></strong> <?php echo esc_html( $location ); ?></div><?php endif; ?>
				<?php if ( $ts ) : ?><div><strong><?php echo esc_html( es_t( 'Datum:', 'Date:' ) ); ?></strong> <?php echo esc_html( date_i18n( 'j. F Y', $ts ) ); ?><?php if ( $ts2 && $ts2 !== $ts ) echo ' – ' . esc_html( date_i18n( 'j. F Y', $ts2 ) ); ?></div><?php endif; ?>
			</div>
		</div>

		<?php if ( $thumb_id ) : ?>
			<div class="es-article__hero" style="max-width:1160px;margin:40px auto;padding:0 24px;">
				<?php echo wp_get_attachment_image( $thumb_id, 'large', false, array( 'loading' => 'eager', 'style' => 'width:100%;height:auto;aspect-ratio:16/9;object-fit:cover;display:block;' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="es-article__body es-article__body--justify" style="max-width:860px;margin-left:auto;margin-right:auto;padding:0 24px;">
			<?php the_content(); ?>
		</div>

		<div style="max-width:860px;margin:56px auto 0;padding:0 24px;">
			<?php if ( $reg_url && $upcoming ) : ?>
				<div style="padding:32px;background:#122023;color:#FFFFFF;display:grid;grid-template-columns:1fr auto;gap:32px;align-items:center;border-radius:var(--es-radius-card);">
					<div>
						<div style="color:#95D708;font-size:var(--es-fs-eyebrow);letter-spacing:0.2em;text-transform:uppercase;font-weight:500;margin-bottom:12px;"><?php echo esc_html( es_t( 'Anmeldung', 'Register' ) ); ?></div>
						<div style="font-size:var(--es-fs-heading-sub);font-weight:500;letter-spacing:-0.015em;"><?php echo esc_html( es_t( 'Sichern Sie sich Ihren Platz.', 'Secure your place.' ) ); ?></div>
					</div>
					<a class="es-btn es-btn--paper" href="<?php echo esc_url( $reg_url ); ?>" target="_blank" rel="noopener">Jetzt anmelden →</a>
				</div>
			<?php endif; ?>
			<div style="margin-top:56px;">
				<a class="es-team-single__back" href="<?php echo esc_url( home_url( '/veranstaltungen/' ) ); ?>">← Alle Veranstaltungen</a>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
