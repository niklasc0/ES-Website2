<?php
/**
 * 404 – Seite nicht gefunden (zweisprachig).
 *
 * @package Energiesozietaet
 */
get_header(); ?>

<section class="es-stage es-stage--ink es-stage-sec es-own-ink es-round-bottom" style="min-height:46vh;display:flex;align-items:center;">
	<div class="es-wrap" style="position:relative;z-index:1;padding:120px 24px;">
		<div class="es-eyebrow" style="margin-bottom:20px;">404</div>
		<h1 style="font-size:clamp(36px,5vw,64px);font-weight:400;letter-spacing:-0.03em;margin:0 0 20px;">
			<?php echo esc_html( es_t( 'Seite nicht gefunden.', 'Page not found.' ) ); ?>
		</h1>
		<p style="max-width:560px;color:rgba(255,255,255,0.65);margin:0 0 36px;">
			<?php echo esc_html( es_t( 'Die aufgerufene Seite existiert nicht oder wurde verschoben.', 'The page you are looking for does not exist or has been moved.' ) ); ?>
		</p>
		<a class="es-btn es-btn--accent" href="<?php echo esc_url( home_url( es_t( '/', '/en/' ) ) ); ?>">
			<?php echo esc_html( es_t( 'Zur Startseite', 'Back to home' ) ); ?> →
		</a>
	</div>
</section>

<?php get_footer();
