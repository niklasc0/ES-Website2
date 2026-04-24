<?php
/**
 * Single Stellenangebot — nach Mockup S4.
 * "Bereich" zeigt den Beratungsbereich (Meta es_field, Mapping zu Label) statt
 * der Rolle. Du-Form statt Sie-Form für Karriere-Kontext.
 *
 * @package Energiesozietaet
 */
get_header();

/** Mapping Feld-Slug → Label. */
function es_karriere_field_label( $slug ) {
	$map = array(
		'rechtsberatung'       => 'Rechtsberatung',
		'steuerberatung'       => 'Steuerberatung',
		'unternehmensberatung' => 'Unternehmensberatung',
		'management'           => 'Büroleitung',
	);
	return $map[ $slug ] ?? '';
}

while ( have_posts() ) : the_post();
	$department  = es_meta( 'es_department' );       // Rolle-Text (z.B. "Consulting")
	$field_slug  = es_meta( 'es_field' );            // neue Meta für Beratungsbereich
	$field_label = $field_slug ? es_karriere_field_label( $field_slug ) : '';
	// Fallback: wenn kein es_field, versuche department-Text zu mappen
	if ( ! $field_label ) {
		$lower = strtolower( (string) $department );
		if ( strpos( $lower, 'recht' ) !== false )      { $field_label = 'Rechtsberatung'; }
		elseif ( strpos( $lower, 'steuer' ) !== false ) { $field_label = 'Steuerberatung'; }
		elseif ( strpos( $lower, 'consulting' ) !== false || strpos( $lower, 'unternehmen' ) !== false ) { $field_label = 'Unternehmensberatung'; }
		else { $field_label = 'Consulting'; }
	}
	$location   = es_meta( 'es_location' );
	if ( ! $location ) { $location = 'Düsseldorf'; }
	$emp_type   = es_meta( 'es_employment_type' );
	if ( ! $emp_type ) { $emp_type = 'Vollzeit' === $emp_type ? 'Vollzeit' : ( $emp_type ? $emp_type : 'Vollzeit' ); }
	$bullets    = es_meta( 'es_bullets' );
	$profile    = es_meta( 'es_profile' );
	$benefits   = es_meta( 'es_benefits' );
	?>

	<section class="es-stelle-single">
		<div style="max-width:960px;margin:0 auto;padding:0 24px;">
			<div class="es-article__crumb">
				<a href="<?php echo esc_url( home_url( '/karriere/' ) ); ?>" style="color:inherit;">Karriere</a>  /  Offene Stelle
			</div>
			<div class="es-article__eyebrow"><?php echo esc_html( $field_label ); ?></div>
			<h1 class="es-article__title"><?php the_title(); ?></h1>

			<div class="es-stelle-single__meta-row">
				<div><strong>Bereich:</strong> <?php echo esc_html( $field_label ); ?></div>
				<div><strong>Standort:</strong> <?php echo esc_html( $location ); ?></div>
				<div><strong>Anstellung:</strong> <?php echo esc_html( $emp_type ); ?></div>
				<div><strong>Eintritt:</strong> ab sofort</div>
			</div>

			<div class="es-stelle-single__section-title">Über die Rolle</div>
			<div class="es-article__body es-article__body--justify" style="font-size:18px;line-height:1.7;color:#0E1A2B;"><?php the_content(); ?></div>

			<?php if ( is_array( $bullets ) && ! empty( $bullets ) ) : ?>
				<div class="es-stelle-single__section-title">Deine Aufgaben</div>
				<ul style="font-size:16px;line-height:1.7;color:#0E1A2B;padding-left:20px;">
					<?php foreach ( $bullets as $b ) : ?><li style="margin-bottom:8px;"><?php echo wp_kses_post( $b ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( is_array( $profile ) && ! empty( $profile ) ) : ?>
				<div class="es-stelle-single__section-title">Dein Profil</div>
				<ul style="font-size:16px;line-height:1.7;color:#0E1A2B;padding-left:20px;">
					<?php foreach ( $profile as $p ) : ?><li style="margin-bottom:8px;"><?php echo esc_html( $p ); ?></li><?php endforeach; ?>
				</ul>
			<?php else : ?>
				<div class="es-stelle-single__section-title">Dein Profil</div>
				<ul style="font-size:16px;line-height:1.7;color:#0E1A2B;padding-left:20px;">
					<li style="margin-bottom:8px;">Abgeschlossenes Studium oder vergleichbare Qualifikation</li>
					<li style="margin-bottom:8px;">Freude an interdisziplinärer Arbeit und direktem Mandantenkontakt</li>
					<li style="margin-bottom:8px;">Hohes Maß an Eigenverantwortung und Teamgeist</li>
					<li style="margin-bottom:8px;">Sehr gute Deutschkenntnisse; Englisch von Vorteil</li>
				</ul>
			<?php endif; ?>

			<div class="es-stelle-single__section-title">Was Dich bei uns erwartet</div>
			<div class="esc-grid esc-grid--cols-2" style="gap:16px;margin-bottom:56px;">
				<?php
				$default_benefits = array(
					array( 'Echte Verantwortung', 'Du arbeitest von Tag eins direkt am Mandat.' ),
					array( 'Interdisziplinarität', 'Recht, Steuern und Unternehmensberatung zusammengedacht.' ),
					array( 'Persönliche Entwicklung', 'Fortbildung, Promotion, Fachanwaltschaften.' ),
					array( 'Modernes Büro', 'Im Herzen Düsseldorfs — mit flexiblen Arbeitsmodellen.' ),
				);
				$list = ( is_array( $benefits ) && ! empty( $benefits ) ) ? $benefits : $default_benefits;
				foreach ( $list as $bn ) :
					$t = is_array( $bn ) ? ( $bn[0] ?? '' ) : (string) $bn;
					$dx = is_array( $bn ) ? ( $bn[1] ?? '' ) : '';
				?>
					<div style="padding:24px;border:1px solid #E4E7EC;">
						<div style="width:4px;height:20px;background:#95D708;margin-bottom:14px;"></div>
						<div style="font-size:17px;font-weight:500;letter-spacing:-0.015em;margin-bottom:6px;"><?php echo esc_html( $t ); ?></div>
						<?php if ( $dx ) : ?><div style="font-size:14px;color:#5A6577;"><?php echo esc_html( $dx ); ?></div><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div style="padding:32px;background:#0E1A2B;color:#FFFFFF;display:grid;grid-template-columns:1fr auto;gap:32px;align-items:center;">
				<div>
					<div style="color:#95D708;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;font-weight:500;margin-bottom:12px;">Deine Bewerbung</div>
					<div style="font-size:22px;font-weight:500;letter-spacing:-0.015em;">Bereit, gemeinsam durchzustarten?</div>
					<div style="font-size:14px;color:rgba(255,255,255,0.6);margin-top:6px;">Schicke uns Deine Unterlagen — wir melden uns binnen eines Werktags.</div>
				</div>
				<a class="es-btn es-btn--paper" href="mailto:karriere@energiesozietaet.de?subject=<?php echo esc_attr( 'Bewerbung — ' . get_the_title() ); ?>">Jetzt bewerben →</a>
			</div>

			<div style="margin-top:56px;">
				<a class="es-team-single__back" href="<?php echo esc_url( home_url( '/karriere/' ) ); ?>">← Alle offenen Stellen</a>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
