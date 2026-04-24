<?php
/**
 * Site header (G1 per mockup).
 *
 * Zwei Varianten:
 *   - Standard: helles Paper
 *   - .es-header--dark: dunkles Ink (über Hero-Dark-Sections)
 *
 * Variant wird automatisch gewählt: Home, Philosophie, Leistungen-Pages, Team,
 * Publikationen, Karriere, News, Veranstaltungen, Kontakt → dark
 * Single-Templates + Legal → light
 *
 * @package Energiesozietaet
 */
$es_dark_slugs = array(
	'home', 'philosophie', 'leistungen', 'rechtsberatung', 'steuerberatung',
	'unternehmensberatung', 'team', 'publikationen', 'karriere', 'news',
	'veranstaltungen', 'kontakt',
);
$es_current_slug = '';
if ( is_front_page() || is_home() ) { $es_current_slug = 'home'; }
elseif ( is_page() ) {
	$es_page = get_queried_object();
	$es_current_slug = $es_page ? (string) $es_page->post_name : '';
}
$es_is_dark = in_array( $es_current_slug, $es_dark_slugs, true );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<?php wp_head(); ?>
</head>
<body <?php body_class( $es_is_dark ? 'es-has-dark-header' : 'es-has-light-header' ); ?>>
<?php wp_body_open(); ?>
<a class="screen-reader-text" href="#es-main"><?php esc_html_e( 'Zum Inhalt springen', 'energiesozietaet' ); ?></a>

<header class="es-header<?php echo $es_is_dark ? ' es-header--dark' : ''; ?>" id="es-header" data-variant="<?php echo $es_is_dark ? 'dark' : 'light'; ?>">
	<div class="es-wrap es-header__inner">
		<a class="es-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Energiesozietät — Zur Startseite">
			<span class="es-brand__mark" aria-hidden="true">ES</span>
			<span class="es-brand__wordmark">
				<span class="es-brand__name">Energiesozietät</span>
				<span class="es-brand__sub">Recht · Steuern · Beratung</span>
			</span>
		</a>

		<nav class="es-nav" id="es-nav" aria-label="<?php esc_attr_e( 'Hauptnavigation', 'energiesozietaet' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'es-nav__list',
					'walker'         => new ES_Nav_Walker(),
					'depth'          => 2,
					'fallback_cb'    => 'es_fallback_menu',
				) );
			} else {
				es_fallback_menu();
			}
			?>
		</nav>

		<div class="es-header__actions">
			<a class="es-btn-jobs" href="<?php echo esc_url( home_url( '/karriere/' ) ); ?>">
				<span class="es-btn-jobs__dot" aria-hidden="true"></span>
				<?php esc_html_e( 'Offene Stellen', 'energiesozietaet' ); ?>
			</a>
			<a class="es-btn-kontakt" href="<?php echo esc_url( home_url( '/kontakt/' ) ); ?>">
				<?php esc_html_e( 'Kontakt', 'energiesozietaet' ); ?> →
			</a>
		</div>

		<button class="es-nav-toggle" aria-controls="es-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menü', 'energiesozietaet' ); ?>">
			<span class="bar" aria-hidden="true"></span>
		</button>
	</div>
</header>

<main id="es-main" class="es-main">
