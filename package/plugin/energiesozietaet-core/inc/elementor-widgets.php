<?php
/**
 * Minimal Elementor widget hooks. The shortcodes already work inside the built-in
 * "Shortcode" widget, but here we register proper widgets so they appear in the
 * Elementor panel under an "Energiesozietät" category.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

add_action( 'elementor/elements/categories_registered', function ( $manager ) {
	$manager->add_category( 'energiesozietaet', array(
		'title' => 'Energiesozietät',
		'icon'  => 'eicon-site-identity',
	) );
} );

add_action( 'elementor/widgets/register', function ( $widgets_manager ) {
	if ( ! class_exists( 'Elementor\\Widget_Base' ) ) { return; }
	require_once __DIR__ . '/widgets/class-grid-widget.php';
	foreach ( array(
		array( 'es_team',               'ES_Team_Widget',            'Team-Übersicht',          'eicon-person',        4 ),
		array( 'es_einzelleistungen',   'ES_Einzelleistungen_Widget','Einzelleistungen-Grid',   'eicon-posts-grid',    3 ),
		array( 'es_karriere',           'ES_Karriere_Widget',        'Karriere-Übersicht',      'eicon-briefcase',     3 ),
		array( 'es_veranstaltungen',    'ES_Veranstaltungen_Widget', 'Veranstaltungen',         'eicon-calendar',      3 ),
		array( 'es_news',               'ES_News_Widget',            'News-Übersicht',          'eicon-post-list',     3 ),
		array( 'es_publikationen',      'ES_Publikationen_Widget',   'Publikationen',           'eicon-document-file', 2 ),
	) as $w ) {
		es_register_grid_widget( $widgets_manager, $w[0], $w[1], $w[2], $w[3], $w[4] );
	}
} );
