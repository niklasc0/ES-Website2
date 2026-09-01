<?php
/** Import-Stabilitaet: "Import erzwingen" auf dem Zielstand darf nichts aendern. */
define( 'WP_USE_THEMES', false );
require __DIR__ . '/site/wp-load.php';
wp_set_current_user( 1 );
function fingerprint() {
	$types = array( 'page', 'es_team', 'es_einzelleistung', 'es_karriere', 'es_news', 'es_veranstaltung', 'es_publikation', 'es_linkedin' );
	$out = array();
	foreach ( get_posts( array( 'post_type' => $types, 'post_status' => 'any', 'numberposts' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) ) as $p ) {
		$metas = get_post_meta( $p->ID );
		ksort( $metas );
		unset( $metas['_edit_lock'], $metas['_edit_last'], $metas['_elementor_css'], $metas['_elementor_element_cache'] );
		$out[ $p->post_type . ':' . $p->post_name ] = md5( serialize( array( $p->post_title, $p->post_content, $p->post_status, $p->menu_order, $p->post_date, $metas ) ) );
	}
	$menu = array();
	foreach ( wp_get_nav_menu_items( 'hauptmenue' ) ?: array() as $mi ) { $menu[] = $mi->title; }
	$out['__menu'] = md5( serialize( $menu ) );
	return $out;
}
$before = fingerprint();
ESC_Importer::run( true );
global $wpdb;
$wpdb->query( "DELETE FROM wp_postmeta WHERE meta_key='_elementor_element_cache'" );
$after = fingerprint();
$diff = 0;
foreach ( $before as $k => $v ) { if ( ! isset( $after[ $k ] ) || $after[ $k ] !== $v ) { $diff++; echo "GEÄNDERT: $k\n"; } }
foreach ( $after as $k => $v ) { if ( ! isset( $before[ $k ] ) ) { $diff++; echo "NEU: $k\n"; } }
echo $diff ? "FEHLER: $diff Abweichungen\n" : "ROUNDTRIP OK: Import ändert nichts am Ist-Stand (" . count( $before ) . " Einträge)\n";
