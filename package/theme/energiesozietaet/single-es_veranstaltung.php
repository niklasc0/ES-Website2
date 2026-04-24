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
		<div class="es-wrap es-wrap--narrow">
			<div class="es-article__crumb">
				<a href="<?php echo esc_url( home_url( '/veranstaltungen/' ) ); ?>" style="color:inherit;">Veranstaltungen</a>  /  <?php echo esc_html( $kind ); ?>
			</div>

			<?php if ( $ts ) : ?>
				<div class="es-event-single__datecard">
					<strong><?php echo esc_html( date_i18n( 'd', $ts ) ); ?></strong>
					<span><?php echo esc_html( date_i18n( 'M Y', $ts ) ); ?></span>
					<?php if ( $ts2 && $ts2 !== $ts ) : ?>
						<span style="color:#8591A3;margin:0 8px;">—</span>
						<strong style="color:#0E1A2B;"><?php echo esc_html( date_i18n( 'd', $ts2 ) ); ?></strong>
						<span><?php echo esc_html( date_i18n( 'M Y', $ts2 ) ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="es-article__eyebrow"><?php echo esc_html( $kind ); ?><?php if ( $upcoming ) : ?> · <span style="color:#95D708;">Anmeldung möglich</span><?php endif; ?></div>
			<h1 class="es-article__title"><?php the_title(); ?></h1>

			<div class="es-stelle-single__meta-row">
				<?php if ( $location ) : ?><div>📍 <strong>Ort:</strong> <?php echo esc_html( $location ); ?></div><?php endif; ?>
				<?php if ( $ts ) : ?><div>🗓 <strong>Datum:</strong> <?php echo esc_html( date_i18n( 'j. F Y', $ts ) ); ?><?php if ( $ts2 && $ts2 !== $ts ) echo ' – ' . esc_html( date_i18n( 'j. F Y', $ts2 ) ); ?></div><?php endif; ?>
			</div>
		</div>

		<?php if ( $thumb_id ) : ?>
			<div class="es-article__hero">
				<?php echo wp_get_attachment_image( $thumb_id, 'es-wide', false, array( 'loading' => 'eager', 'style' => 'width:100%;height:100%;object-fit:cover;' ) ); ?>
			</div>
		<?php endif; ?>

		<div class="es-article__body">
			<?php the_content(); ?>
		</div>

		<div class="es-wrap es-wrap--narrow" style="padding-top:56px;">
			<?php if ( $reg_url && $upcoming ) : ?>
				<div style="padding:32px;background:#0E1A2B;color:#FFFFFF;display:grid;grid-template-columns:1fr auto;gap:32px;align-items:center;">
					<div>
						<div style="color:#95D708;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;font-weight:500;margin-bottom:12px;">Anmeldung</div>
						<div style="font-size:20px;font-weight:500;letter-spacing:-0.015em;">Sichern Sie sich Ihren Platz.</div>
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
