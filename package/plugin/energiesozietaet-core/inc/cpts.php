<?php
/**
 * Custom Post Types + Taxonomies.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_CPTs {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ), 5 );
	}

	public static function register() {
		self::register_cpts();
		self::register_taxonomies();
		self::ensure_terms();
	}

	protected static function cpt( $slug, $labels_singular, $labels_plural, $args = array() ) {
		$labels = array(
			'name'               => $labels_plural,
			'singular_name'      => $labels_singular,
			'menu_name'          => $labels_plural,
			'add_new'            => __( 'Neu hinzufügen', 'energiesozietaet-core' ),
			'add_new_item'       => sprintf( __( 'Neue(r) %s', 'energiesozietaet-core' ), $labels_singular ),
			'edit_item'          => sprintf( __( '%s bearbeiten', 'energiesozietaet-core' ), $labels_singular ),
			'view_item'          => sprintf( __( '%s ansehen', 'energiesozietaet-core' ), $labels_singular ),
			'all_items'          => sprintf( __( 'Alle %s', 'energiesozietaet-core' ), $labels_plural ),
			'search_items'       => sprintf( __( '%s durchsuchen', 'energiesozietaet-core' ), $labels_plural ),
			'not_found'          => sprintf( __( 'Keine %s gefunden', 'energiesozietaet-core' ), $labels_plural ),
			'not_found_in_trash' => sprintf( __( 'Keine %s im Papierkorb gefunden', 'energiesozietaet-core' ), $labels_plural ),
		);
		$defaults = array(
			'public'       => true,
			'show_in_rest' => true,
			'has_archive'  => true,
			'menu_position'=> 5,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions', 'excerpt', 'page-attributes', 'elementor' ),
			'labels'       => $labels,
		);
		register_post_type( $slug, array_merge( $defaults, $args ) );
	}

	public static function register_cpts() {
		// Individual-post rewrite slugs are namespaced so they never collide with the
		// top-level static pages (team, karriere, news, veranstaltungen, publikationen
		// and the leistungen/<beratungsfeld>/ pages). The static pages carry the grid
		// shortcode and serve as the canonical overview – CPT archives are disabled.
		self::cpt( 'es_team', 'Teammitglied', 'Team', array(
			'menu_icon'    => 'dashicons-groups',
			'rewrite'      => array( 'slug' => 'teammitglied', 'with_front' => false ),
			'has_archive'  => false,
			'supports'     => array( 'title', 'editor', 'thumbnail', 'revisions', 'excerpt', 'page-attributes', 'custom-fields', 'elementor' ),
		) );
		self::cpt( 'es_einzelleistung', 'Einzelleistung', 'Einzelleistungen', array(
			'menu_icon'    => 'dashicons-portfolio',
			'rewrite'      => array( 'slug' => 'leistung', 'with_front' => false ),
			'has_archive'  => false,
		) );
		self::cpt( 'es_karriere', 'Stellenangebot', 'Karriere', array(
			'menu_icon'    => 'dashicons-businessperson',
			'rewrite'      => array( 'slug' => 'stelle', 'with_front' => false ),
			'has_archive'  => false,
		) );
		self::cpt( 'es_veranstaltung', 'Veranstaltung', 'Veranstaltungen', array(
			'menu_icon'    => 'dashicons-calendar-alt',
			'rewrite'      => array( 'slug' => 'veranstaltung', 'with_front' => false ),
			'has_archive'  => false,
		) );
		self::cpt( 'es_news', 'News-Beitrag', 'News', array(
			'menu_icon'    => 'dashicons-megaphone',
			'rewrite'      => array( 'slug' => 'news-artikel', 'with_front' => false ),
			'has_archive'  => false,
		) );
		self::cpt( 'es_publikation', 'Publikation', 'Publikationen', array(
			'menu_icon'    => 'dashicons-book',
			'rewrite'      => array( 'slug' => 'publikation', 'with_front' => false ),
			'has_archive'  => false,
		) );
	}

	public static function register_taxonomies() {
		register_taxonomy( 'es_beratungsfeld', array( 'es_einzelleistung', 'es_news' ), array(
			'label'             => __( 'Beratungsfelder', 'energiesozietaet-core' ),
			'labels'            => array(
				'name'          => 'Beratungsfelder',
				'singular_name' => 'Beratungsfeld',
				'menu_name'     => 'Beratungsfeld',
				'all_items'     => 'Alle Beratungsfelder',
				'edit_item'     => 'Beratungsfeld bearbeiten',
				'add_new_item'  => 'Neues Beratungsfeld',
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'beratungsfeld', 'hierarchical' => false ),
		) );

		register_taxonomy( 'es_news_kategorie', array( 'es_news' ), array(
			'label'             => __( 'News-Kategorien', 'energiesozietaet-core' ),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'news-kategorie' ),
		) );
	}

	/**
	 * Ensure the three Beratungsfeld terms exist.
	 */
	public static function ensure_terms() {
		$terms = array(
			'rechtsberatung'        => array( 'Rechtsberatung',       'Wir stellen juristische Lösungen in den Gesamtkontext.' ),
			'steuerberatung'        => array( 'Steuerberatung',       'Fortlaufende Steuerberatung, Gestaltungsberatung und Neustrukturierungen.' ),
			'unternehmensberatung'  => array( 'Unternehmensberatung', 'Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen.' ),
		);
		foreach ( $terms as $slug => $t ) {
			if ( ! term_exists( $slug, 'es_beratungsfeld' ) ) {
				wp_insert_term( $t[0], 'es_beratungsfeld', array( 'slug' => $slug, 'description' => $t[1] ) );
			}
		}
	}
}

ESC_CPTs::init();
