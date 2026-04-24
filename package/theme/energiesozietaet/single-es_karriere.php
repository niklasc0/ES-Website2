<?php
/**
 * Single Karriere / Stellenangebot.
 *
 * @package Energiesozietaet
 */
get_header();
while ( have_posts() ) : the_post();
	$department = es_meta( 'es_department' );
	$location   = es_meta( 'es_location' );
	$emp_type   = es_meta( 'es_employment_type' );
	$bullets    = es_meta( 'es_bullets' );

	es_page_head( array(
		'eyebrow' => $department ? $department : 'Karriere',
		'title'   => get_the_title(),
		'crumbs'  => array( array( 'Karriere', home_url( '/karriere/' ) ), array( get_the_title() ) ),
	) );
	?>

	<section class="es-section">
		<div class="es-wrap" style="display:grid; grid-template-columns:minmax(0,1fr); gap:48px;">
			<style>@media (min-width:900px){.es-karriere-grid{grid-template-columns: 1fr 320px !important; gap:56px !important}}</style>
			<div class="es-karriere-grid" style="display:grid; grid-template-columns:1fr; gap:32px;">
				<div class="es-prose">
					<?php the_content(); ?>
					<?php if ( is_array( $bullets ) && ! empty( $bullets ) ) : ?>
						<ul>
							<?php foreach ( $bullets as $b ) : ?><li><?php echo wp_kses_post( $b ); ?></li><?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
				<aside style="background:var(--es-soft); border-radius:var(--es-radius); padding:28px; align-self:start; position:sticky; top:calc(var(--es-header-h) + 24px);">
					<h4 style="margin-top:0; font-family:var(--es-font-body); font-size:0.82rem; text-transform:uppercase; letter-spacing:0.16em; color:var(--es-muted); font-weight:600;">Eckdaten</h4>
					<dl style="margin:0; display:grid; gap:10px; font-size:0.95rem;">
						<?php if ( $department ) : ?><div><dt style="color:var(--es-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:.12em;">Bereich</dt><dd style="margin:2px 0 0;font-weight:500;"><?php echo esc_html( $department ); ?></dd></div><?php endif; ?>
						<?php if ( $location ) : ?><div><dt style="color:var(--es-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:.12em;">Standort</dt><dd style="margin:2px 0 0;font-weight:500;"><?php echo esc_html( $location ); ?></dd></div><?php endif; ?>
						<?php if ( $emp_type ) : ?><div><dt style="color:var(--es-muted);font-size:.82rem;text-transform:uppercase;letter-spacing:.12em;">Anstellung</dt><dd style="margin:2px 0 0;font-weight:500;"><?php echo esc_html( $emp_type ); ?></dd></div><?php endif; ?>
					</dl>
					<a class="es-btn es-btn--primary" style="margin-top:24px; width:100%; justify-content:center;" href="mailto:karriere@energiesozietaet.de?subject=Bewerbung: <?php echo esc_attr( get_the_title() ); ?>">Jetzt bewerben</a>
				</aside>
			</div>

			<hr class="es-divider" />
			<?php es_back_link( home_url( '/karriere/' ), 'Alle offenen Stellen' ); ?>
		</div>
	</section>

<?php endwhile;
get_footer();
