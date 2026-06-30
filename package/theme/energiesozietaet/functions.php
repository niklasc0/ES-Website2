<?php
/**
 * Energiesozietät — theme bootstrap.
 *
 * @package Energiesozietaet
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'ES_THEME_VERSION', '1.0.0' );
define( 'ES_THEME_DIR', get_template_directory() );
define( 'ES_THEME_URI', get_template_directory_uri() );

/**
 * Theme supports.
 */
function es_theme_setup() {
	load_theme_textdomain( 'energiesozietaet', ES_THEME_DIR . '/languages' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 40,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-units' );
	add_theme_support( 'elementor' );

	register_nav_menus( array(
		'primary' => __( 'Hauptmenü', 'energiesozietaet' ),
		'footer'  => __( 'Footer-Menü', 'energiesozietaet' ),
		'legal'   => __( 'Rechtliches (Footer)', 'energiesozietaet' ),
	) );

	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Tinte', 'energiesozietaet' ),        'slug' => 'ink',        'color' => '#122023' ),
		array( 'name' => __( 'Tinte weich', 'energiesozietaet' ),  'slug' => 'ink-soft',   'color' => '#1D2D2D' ),
		array( 'name' => __( 'Papier', 'energiesozietaet' ),       'slug' => 'paper',      'color' => '#FFFFFF' ),
		array( 'name' => __( 'Papier warm (dunkel)', 'energiesozietaet' ), 'slug' => 'paper-warm', 'color' => '#1D2D2D' ),
		array( 'name' => __( 'Papier kühl', 'energiesozietaet' ),  'slug' => 'paper-cool', 'color' => '#F5F5F5' ),
		array( 'name' => __( 'Akzent', 'energiesozietaet' ),       'slug' => 'accent',     'color' => '#95D708' ),
		array( 'name' => __( 'Text', 'energiesozietaet' ),         'slug' => 'text',       'color' => '#151E20' ),
		array( 'name' => __( 'Muted', 'energiesozietaet' ),        'slug' => 'muted',      'color' => '#899092' ),
	) );

	add_image_size( 'es-team', 900, 900, true );
	add_image_size( 'es-card', 900, 600, true );
	add_image_size( 'es-wide', 1800, 900, true );
}
add_action( 'after_setup_theme', 'es_theme_setup' );

/**
 * Enqueue styles & scripts.
 */
function es_theme_enqueue_assets() {
	// Manrope (Text/UI) + Sora (Display/Headlines) — Design-Sprache des ah5/Elementra-Templates.
	wp_enqueue_style(
		'es-fonts',
		'https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700;800&family=Sora:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	// Cache-Busting via filemtime → Browser zieht bei jedem Deploy neue Files.
	$css_path = ES_THEME_DIR . '/style.css';
	$js_path  = ES_THEME_DIR . '/assets/js/ui.js';
	$css_ver  = file_exists( $css_path ) ? (string) filemtime( $css_path ) : ES_THEME_VERSION;
	$js_ver   = file_exists( $js_path )  ? (string) filemtime( $js_path )  : ES_THEME_VERSION;
	wp_enqueue_style( 'energiesozietaet', get_stylesheet_uri(), array( 'es-fonts' ), $css_ver );
	wp_enqueue_script( 'energiesozietaet-ui', ES_THEME_URI . '/assets/js/ui.js', array(), $js_ver, true );
}
add_action( 'wp_enqueue_scripts', 'es_theme_enqueue_assets' );

/**
 * Add preconnects to Google Fonts.
 */
function es_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		$urls[] = 'https://fonts.googleapis.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'es_resource_hints', 10, 2 );

/**
 * Elementor global colors + fonts — makes every Elementor page pick up our design tokens.
 */
function es_elementor_globals() {
	if ( ! did_action( 'elementor/loaded' ) ) { return; }
	// Replace Elementor's kit globals so they reflect our brand.
	add_filter( 'elementor/theme/get_location_templates/template_id', '__return_false' );
}
add_action( 'init', 'es_elementor_globals' );

/**
 * Register page templates used by the importer.
 */
require_once ES_THEME_DIR . '/inc/template-helpers.php';
require_once ES_THEME_DIR . '/inc/walker-menu.php';

/**
 * Body class helpers.
 */
function es_body_classes( $classes ) {
	$classes[] = 'es-theme';
	if ( is_page() || is_singular() ) {
		$post = get_queried_object();
		if ( $post ) { $classes[] = 'es-single-' . sanitize_html_class( $post->post_name ); }
	}
	return $classes;
}
add_filter( 'body_class', 'es_body_classes' );

/**
 * Elementor compat — expose text-bg-green class to Elementor's rich text editors.
 */
function es_elementor_editor_stylesheets( $post_css ) {
	if ( ! is_admin() ) { return $post_css; }
	return $post_css;
}
add_filter( 'elementor/frontend/after_register_styles', function() {
	wp_enqueue_style( 'es-elementor-overrides', ES_THEME_URI . '/assets/css/elementor.css', array(), ES_THEME_VERSION );
} );

/**
 * Editor toolbar: add colored-text classes usable via TinyMCE "Formats".
 */
function es_tinymce_formats( $formats ) {
	$formats = is_array( $formats ) ? $formats : array();
	$formats[] = array(
		'title' => 'Akzent Grün (#94d707)',
		'inline' => 'span',
		'classes' => 'text-bg-green',
	);
	$formats[] = array(
		'title' => 'Akzent Grün unterstrichen',
		'inline' => 'span',
		'classes' => 'es-underline-accent',
	);
	$formats[] = array(
		'title' => 'Highlight (Tinte)',
		'inline' => 'span',
		'classes' => 'es-ink-text',
	);
	$formats[] = array(
		'title' => 'Dezent (Muted)',
		'inline' => 'span',
		'classes' => 'es-muted-text',
	);
	$formats[] = array(
		'title' => 'Highlight Hintergrund',
		'inline' => 'span',
		'classes' => 'es-highlight',
	);
	return $formats;
}
add_filter( 'tiny_mce_before_init', function( $init ) {
	$style_formats = array();
	foreach ( es_tinymce_formats( array() ) as $f ) { $style_formats[] = $f; }
	$init['style_formats'] = wp_json_encode( $style_formats );
	$init['block_formats'] = 'Absatz=p;Überschrift 2=h2;Überschrift 3=h3;Überschrift 4=h4';
	return $init;
} );

add_filter( 'mce_buttons_2', function( $buttons ) {
	if ( ! in_array( 'styleselect', $buttons, true ) ) { array_unshift( $buttons, 'styleselect' ); }
	return $buttons;
} );

/**
 * Allow the span+class used for accent text in classic + Elementor editors.
 */
function es_kses_allow_spans( $tags, $context ) {
	if ( 'post' === $context ) {
		if ( ! isset( $tags['span'] ) ) { $tags['span'] = array(); }
		$tags['span']['class'] = true;
	}
	return $tags;
}
add_filter( 'wp_kses_allowed_html', 'es_kses_allow_spans', 10, 2 );

/**
 * Customizer: zwei separate Logo-Felder (dunkler Header, heller Header).
 * Fallback: WP custom_logo (Site Identity).
 */
function es_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'es_logos', array(
		'title'    => __( 'Logos (Energiesozietät)', 'energiesozietaet' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'es_logo_dark',  array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_setting( 'es_logo_light', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );

	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'es_logo_dark', array(
		'label'       => __( 'Logo für dunkle Menüleisten', 'energiesozietaet' ),
		'description' => __( 'Version für weiße Schrift auf dunklem Ink-Hintergrund (Home, Philosophie, Leistungen …).', 'energiesozietaet' ),
		'section'     => 'es_logos',
		'mime_type'   => 'image',
	) ) );

	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'es_logo_light', array(
		'label'       => __( 'Logo für helle Menüleisten', 'energiesozietaet' ),
		'description' => __( 'Version für dunkle Schrift auf hellem Hintergrund (Einzelseiten, News-Detail, Legal …).', 'energiesozietaet' ),
		'section'     => 'es_logos',
		'mime_type'   => 'image',
	) ) );
}
add_action( 'customize_register', 'es_customize_register' );

/**
 * Gibt die Logo-URL zurück passend zur Header-Variante ('dark'|'light').
 * Fallback-Kette: spezifisches Logo → WP custom_logo → ''.
 */
function es_get_header_logo_url( $variant = 'dark' ) {
	$key = 'dark' === $variant ? 'es_logo_dark' : 'es_logo_light';
	$id  = (int) get_theme_mod( $key, 0 );
	if ( $id ) { return wp_get_attachment_image_url( $id, 'full' ); }
	if ( has_custom_logo() ) {
		$logo_id = get_theme_mod( 'custom_logo' );
		if ( $logo_id ) { return wp_get_attachment_image_url( $logo_id, 'full' ); }
	}
	return '';
}

/**
 * vCard-Download für Team-Mitglieder.
 * Route: /?es_vcard=<post_id>
 */
function es_vcard_handler() {
	if ( empty( $_GET['es_vcard'] ) ) { return; }
	$id = (int) $_GET['es_vcard'];
	$p  = get_post( $id );
	if ( ! $p || 'es_team' !== $p->post_type ) { return; }

	$name_parts = explode( ' ', trim( get_the_title( $p ) ) );
	$given      = array_shift( $name_parts );
	$family     = implode( ' ', $name_parts );
	$role       = (string) get_post_meta( $id, 'es_role', true );
	$email      = (string) get_post_meta( $id, 'es_email', true );
	$phone      = (string) get_post_meta( $id, 'es_phone', true );
	$location   = (string) get_post_meta( $id, 'es_location', true );

	$vcard  = "BEGIN:VCARD\r\nVERSION:3.0\r\n";
	$vcard .= "N:" . $family . ';' . $given . ";;;\r\n";
	$vcard .= "FN:" . get_the_title( $p ) . "\r\n";
	$vcard .= "ORG:Energiesozietät GmbH\r\n";
	if ( $role ) { $vcard .= "TITLE:" . $role . "\r\n"; }
	if ( $email ) { $vcard .= "EMAIL;TYPE=WORK:" . $email . "\r\n"; }
	if ( $phone ) { $vcard .= "TEL;TYPE=WORK,VOICE:" . $phone . "\r\n"; }
	$vcard .= "ADR;TYPE=WORK:;;Roßstraße 92 / Kennedyhaus;Düsseldorf;;40476;Deutschland\r\n";
	$vcard .= "URL:" . home_url( '/teammitglied/' . $p->post_name . '/' ) . "\r\n";
	$vcard .= "END:VCARD\r\n";

	$filename = sanitize_file_name( sanitize_title( get_the_title( $p ) ) . '.vcf' );
	header( 'Content-Type: text/vcard; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	echo $vcard;
	exit;
}
add_action( 'init', 'es_vcard_handler' );

/**
 * Simple excerpt.
 */
function es_excerpt( $post, $length = 28 ) {
	if ( ! $post ) { return ''; }
	if ( ! empty( $post->post_excerpt ) ) { return wp_strip_all_tags( $post->post_excerpt ); }
	return wp_trim_words( wp_strip_all_tags( $post->post_content ), $length, '…' );
}

/**
 * Light-weight fallback header menu if no menu assigned.
 */
function es_fallback_menu() {
	// Kontakt + Karriere leben in den Header-Buttons rechts — nicht im Hauptmenü.
	$items = array(
		home_url( '/philosophie/' )     => __( 'Philosophie', 'energiesozietaet' ),
		home_url( '/leistungen/' )      => __( 'Leistungen', 'energiesozietaet' ),
		home_url( '/team/' )            => __( 'Team', 'energiesozietaet' ),
		home_url( '/publikationen/' )   => __( 'Publikationen', 'energiesozietaet' ),
		home_url( '/news/' )            => __( 'News', 'energiesozietaet' ),
		home_url( '/veranstaltungen/' ) => __( 'Veranstaltungen', 'energiesozietaet' ),
	);
	echo '<ul class="es-nav__list">';
	foreach ( $items as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
