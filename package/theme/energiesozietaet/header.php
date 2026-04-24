<?php
/**
 * Site header.
 *
 * @package Energiesozietaet
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="es-header" id="es-header">
	<div class="es-wrap es-header__inner">
		<a class="es-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="es-brand__mark" aria-hidden="true">ES</span>
			<span>
				Energiesozietät
				<span class="es-brand__sub">Recht · Steuern · Beratung</span>
			</span>
		</a>

		<nav class="es-nav" id="es-nav" aria-label="<?php esc_attr_e( 'Hauptnavigation', 'energiesozietaet' ); ?>">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => '',
					'walker'         => new ES_Nav_Walker(),
					'depth'          => 2,
				) );
			} else {
				es_fallback_menu();
			}
			?>
		</nav>

		<button class="es-nav-toggle" aria-controls="es-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Menü', 'energiesozietaet' ); ?>">
			<span class="bar" aria-hidden="true"></span>
		</button>
	</div>
</header>

<main id="es-main" class="es-main">
