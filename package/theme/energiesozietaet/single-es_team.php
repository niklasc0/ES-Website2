<?php
/**
 * Single Team member.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$role     = es_meta( 'es_role' );
	$email    = es_meta( 'es_email' );
	$phone    = es_meta( 'es_phone' );
	$linkedin = es_meta( 'es_linkedin' );
	$focus    = es_meta( 'es_focus_areas' ); // array of [title, content]
	$more_bio = es_meta( 'es_more_bio' );
	$thumb_id = get_post_thumbnail_id();
	?>

	<section class="es-page-head">
		<div class="es-wrap">
			<div class="es-page-head__crumbs">
				<a href="<?php echo esc_url( home_url( '/team/' ) ); ?>">Team</a> <span aria-hidden="true">›</span> <?php echo esc_html( get_the_title() ); ?>
			</div>
			<div style="display:grid; grid-template-columns: minmax(0,1fr); gap:40px; align-items:flex-end;">
				<div>
					<?php if ( $role ) : ?>
						<span class="es-eyebrow"><?php echo esc_html( $role ); ?></span>
					<?php endif; ?>
					<h1 style="margin-top:.25em;"><?php the_title(); ?></h1>
				</div>
			</div>
		</div>
	</section>

	<section class="es-section">
		<div class="es-wrap" style="display:grid; grid-template-columns: minmax(0,1fr); gap:64px; align-items:flex-start;">
			<style>@media (min-width: 900px) { .es-team-single { grid-template-columns: 400px 1fr !important; gap:72px !important; } }</style>
			<div class="es-team-single" style="display:grid; grid-template-columns: 1fr; gap: 40px;">
				<figure style="margin:0; border-radius:var(--es-radius-lg); overflow:hidden; background:var(--es-soft); aspect-ratio: 1/1;">
					<?php if ( $thumb_id ) : ?>
						<?php echo wp_get_attachment_image( $thumb_id, 'es-team', false, array( 'style' => 'width:100%;height:100%;object-fit:cover;', 'loading' => 'eager' ) ); ?>
					<?php else : ?>
						<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--es-muted);font-family:var(--es-font-display);font-size:3rem;"><?php echo esc_html( mb_substr( get_the_title(), 0, 1 ) ); ?></div>
					<?php endif; ?>
				</figure>

				<div>
					<div class="es-prose"><?php the_content(); ?></div>

					<?php if ( $more_bio ) : ?>
						<details style="margin-top:20px;">
							<summary style="cursor:pointer; color:var(--es-accent-deep); font-weight:600;">Mehr lesen</summary>
							<div class="es-prose" style="margin-top:16px;"><?php echo wp_kses_post( wpautop( $more_bio ) ); ?></div>
						</details>
					<?php endif; ?>

					<div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:28px;">
						<?php if ( $email ) : ?>
							<a class="es-btn es-btn--primary" href="mailto:<?php echo esc_attr( $email ); ?>">Kontakt aufnehmen</a>
						<?php endif; ?>
						<?php if ( $linkedin ) : ?>
							<a class="es-btn es-btn--ghost" href="<?php echo esc_url( $linkedin ); ?>" target="_blank" rel="noopener">LinkedIn</a>
						<?php endif; ?>
					</div>

					<?php if ( is_array( $focus ) && ! empty( $focus ) ) : ?>
						<h2 style="margin-top:64px;">Ausgewählte persönliche Schwerpunkte</h2>
						<div class="es-accordion">
							<?php foreach ( $focus as $item ) :
								$t = isset( $item['title'] ) ? $item['title'] : '';
								$c = isset( $item['content'] ) ? $item['content'] : '';
								if ( ! $t && ! $c ) continue; ?>
								<details style="border:1px solid var(--es-line); border-radius:10px; padding:16px 20px; margin-bottom:10px; transition: background var(--es-dur) var(--es-ease);">
									<summary style="cursor:pointer; font-family:var(--es-font-display); font-weight:500; font-size:1.1rem; color:var(--es-ink); list-style:none; display:flex; justify-content:space-between; align-items:center; gap:20px;">
										<span><?php echo esc_html( $t ); ?></span>
										<span aria-hidden="true" style="color:var(--es-accent-deep); transition: transform var(--es-dur) var(--es-ease);">+</span>
									</summary>
									<div style="margin-top:12px; color:var(--es-muted);"><?php echo wp_kses_post( wpautop( $c ) ); ?></div>
								</details>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<hr class="es-divider" />
					<?php es_back_link( home_url( '/team/' ), 'Zurück zum Team' ); ?>
				</div>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
