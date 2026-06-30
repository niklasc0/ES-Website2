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
	if ( ! $emp_type ) { $emp_type = 'Vollzeit'; }
	$start_date = es_meta( 'es_start_date' );
	$entry_str  = $start_date ? date_i18n( 'j. F Y', strtotime( $start_date ) ) : 'ab sofort';

	// Neue Felder: es_tasks (Aufgaben) + es_profile (Profil). Legacy-Fallback: es_bullets.
	$tasks    = es_meta( 'es_tasks' );
	if ( empty( $tasks ) ) {
		$legacy = es_meta( 'es_bullets' );
		if ( is_array( $legacy ) && ! empty( $legacy ) ) { $tasks = $legacy; }
	}
	$profile  = es_meta( 'es_profile' );

	// Globale Einstellungen (Settings-API)
	$kset = class_exists( 'ESC_Karriere_Settings' ) ? ESC_Karriere_Settings::get() : array();
	$kget = function ( $k, $d = '' ) use ( $kset ) { return $kset[ $k ] ?? $d; };
	$benefits_list = array(
		array( $kget( 'benefit1_title', 'Echte Verantwortung' ), $kget( 'benefit1_desc' ) ),
		array( $kget( 'benefit2_title', 'Interdisziplinarität' ), $kget( 'benefit2_desc' ) ),
		array( $kget( 'benefit3_title', 'Persönliche Entwicklung' ), $kget( 'benefit3_desc' ) ),
		array( $kget( 'benefit4_title', 'Modernes Büro' ), $kget( 'benefit4_desc' ) ),
	);
	?>

	<section class="es-stelle-single">
		<div style="max-width:960px;margin:0 auto;padding:0 24px;">
			<div class="es-article__crumb">
				<a href="<?php echo esc_url( home_url( '/karriere/' ) ); ?>" style="color:inherit;">Karriere</a>
			</div>
			<div class="es-article__eyebrow"><?php echo esc_html( $field_label ); ?></div>
			<h1 class="es-article__title"><?php the_title(); ?></h1>

			<div class="es-stelle-single__meta-row">
				<div><strong>Bereich:</strong> <?php echo esc_html( $field_label ); ?></div>
				<div><strong>Standort:</strong> <?php echo esc_html( $location ); ?></div>
				<div><strong>Anstellung:</strong> <?php echo esc_html( $emp_type ); ?></div>
				<div><strong>Eintritt:</strong> <?php echo esc_html( $entry_str ); ?></div>
			</div>

			<div class="es-stelle-single__section-title">Über die Rolle</div>
			<div class="es-stelle-single__body"><?php the_content(); ?></div>

			<?php if ( is_array( $tasks ) && ! empty( $tasks ) ) : ?>
				<div class="es-stelle-single__section-title">Was erwarten Dich für Aufgaben?</div>
				<ul class="es-stelle-single__list">
					<?php foreach ( $tasks as $t ) : ?><li><?php echo wp_kses_post( $t ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( is_array( $profile ) && ! empty( $profile ) ) : ?>
				<div class="es-stelle-single__section-title">Dein Profil</div>
				<ul class="es-stelle-single__list">
					<?php foreach ( $profile as $p ) : ?><li><?php echo wp_kses_post( $p ); ?></li><?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<div class="es-stelle-single__section-title"><?php echo esc_html( $kget( 'benefits_title', 'Was Dich bei uns erwartet' ) ); ?></div>
			<div class="esc-grid esc-grid--cols-2" style="gap:16px;margin-bottom:56px;">
				<?php foreach ( $benefits_list as $bn ) :
					if ( empty( $bn[0] ) ) continue; ?>
					<div style="padding:24px;border:1px solid #DADEC5;">
						<div style="width:4px;height:20px;background:#95D708;margin-bottom:14px;"></div>
						<div style="font-size:17px;font-weight:500;letter-spacing:-0.015em;margin-bottom:6px;"><?php echo esc_html( $bn[0] ); ?></div>
						<?php if ( ! empty( $bn[1] ) ) : ?><div style="font-size:14px;color:#899092;"><?php echo esc_html( $bn[1] ); ?></div><?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div style="padding:32px;background:#122023;color:#FFFFFF;display:grid;grid-template-columns:1fr auto;gap:32px;align-items:center;">
				<div>
					<div style="color:#95D708;font-size:11px;letter-spacing:0.2em;text-transform:uppercase;font-weight:500;margin-bottom:12px;"><?php echo esc_html( $kget( 'cta_eyebrow', 'Deine Bewerbung' ) ); ?></div>
					<div style="font-size:22px;font-weight:500;letter-spacing:-0.015em;"><?php echo esc_html( $kget( 'cta_title', 'Bereit, gemeinsam durchzustarten?' ) ); ?></div>
					<div style="font-size:14px;color:rgba(255,255,255,0.6);margin-top:6px;"><?php echo esc_html( $kget( 'cta_subtitle', '' ) ); ?></div>
				</div>
				<a class="es-btn es-btn--paper" href="mailto:<?php echo esc_attr( $kget( 'cta_recipient', 'karriere@energiesozietaet.de' ) ); ?>?subject=<?php echo esc_attr( 'Bewerbung — ' . get_the_title() ); ?>"><?php echo esc_html( $kget( 'cta_button_label', 'Jetzt bewerben' ) ); ?> →</a>
			</div>

			<div style="margin-top:56px;">
				<a class="es-team-single__back" href="<?php echo esc_url( home_url( '/karriere/' ) ); ?>">← Alle offenen Stellen</a>
			</div>
		</div>
	</section>

<?php endwhile;
get_footer();
