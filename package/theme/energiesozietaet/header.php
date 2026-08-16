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
// ah5/Elementra: Header ist immer hell (weiß). Die dunklen Page-Hero-Bänder
// sitzen als separater, gerundeter Block UNTER dem Header.
$es_is_dark = false;
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
		<a class="es-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Energiesozietät – Zur Startseite">
			<?php $logo_src = function_exists( 'es_get_header_logo_url' ) ? es_get_header_logo_url( $es_is_dark ? 'dark' : 'light' ) : ''; ?>
			<?php if ( $logo_src ) : ?>
				<img class="es-brand__logo" src="<?php echo esc_url( $logo_src ); ?>" alt="<?php bloginfo( 'name' ); ?>" />
			<?php else : ?>
				<span class="es-brand__wordmark">
					<span class="es-brand__name">Energie<span class="es-brand__flag" aria-hidden="true"></span></span>
					<span class="es-brand__sub">sozietät</span>
				</span>
			<?php endif; ?>
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
			<a class="es-btn es-btn--ghost es-header__cta es-header__cta--jobs" href="<?php echo esc_url( home_url( es_t( '/karriere/', '/en/karriere/' ) ) ); ?>">
				<?php echo esc_html( es_t( 'Stellenangebote', 'Careers' ) ); ?>
			</a>
			<a class="es-btn es-btn--accent es-header__cta" href="<?php echo esc_url( home_url( es_t( '/kontakt/', '/en/kontakt/' ) ) ); ?>">
				<?php echo esc_html( es_t( 'Kontakt', 'Contact' ) ); ?>
			</a>
			<?php if ( class_exists( 'ES_Lang' ) ) : ?>
				<a class="es-header__lang" href="<?php echo esc_url( ES_Lang::switch_url() ); ?>" aria-label="<?php echo esc_attr( es_t( 'Switch to English', 'Zur deutschen Fassung wechseln' ) ); ?>">
					<span class="<?php echo es_is_en() ? '' : 'is-active'; ?>">DE</span><span aria-hidden="true">/</span><span class="<?php echo es_is_en() ? 'is-active' : ''; ?>">EN</span>
				</a>
			<?php endif; ?>
		</div>

		<button class="es-nav-toggle" aria-controls="es-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menü', 'energiesozietaet' ); ?>">
			<span class="bar" aria-hidden="true"></span>
		</button>
	</div>
</header>

<main id="es-main" class="es-main">
