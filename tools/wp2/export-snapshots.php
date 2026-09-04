<?php
/**
 * Exportiert die Elementor-Seiten der Testinstanz als Snapshots nach
 * data/pages/<slug>.json. URLs der Instanz werden durch die Platzhalter
 * {{ES_HOME}} / {{ES_HOME_JSON}} ersetzt, die der Importer beim Einspielen
 * wieder durch home_url() ersetzt; Snapshots sind damit domain-portabel.
 * Optionaler Aufruf mit Slugs: php export-snapshots.php home kontakt
 * (ohne Argumente werden alle Standardseiten exportiert).
 *
 * Aus dem wp2-Verzeichnis aufrufen; schreibt direkt ins Repo.
 */
define( 'WP_USE_THEMES', false );
require __DIR__ . '/site/wp-load.php';

$dir   = '/home/user/ES-Website2/package/plugin/energiesozietaet-core/data/pages/';
$slugs = array_slice( $argv, 1 );
if ( ! $slugs ) {
	$slugs = array( 'home', 'philosophie', 'leistungen', 'rechtsberatung', 'steuerberatung', 'unternehmensberatung', 'team', 'karriere', 'kontakt', 'news', 'veranstaltungen', 'publikationen', 'impressum', 'datenschutzerklaerung' );
}
$home      = home_url();
$home_json = str_replace( '/', '\\/', $home );
foreach ( $slugs as $slug ) {
	$p = get_page_by_path( $slug );
	if ( ! $p ) { echo "FEHLT: $slug\n"; continue; }
	// post_content wird vom Importer verbatim uebernommen (keine Platzhalter-
	// Ersetzung) – dort direkt die Live-Domain einsetzen.
	$post_content = str_replace( $home, 'https://1229160.eu15.myftpupload.com', $p->post_content );
	$elementor    = (string) get_post_meta( $p->ID, '_elementor_data', true );
	// Reihenfolge wichtig: erst die JSON-escapte Form, dann die rohe
	$elementor = str_replace( $home_json, '{{ES_HOME_JSON}}', $elementor );
	$elementor = str_replace( $home, '{{ES_HOME}}', $elementor );
	$snap = array(
		'title'         => $p->post_title,
		'post_content'  => $post_content,
		'elementor'     => $elementor,
		'page_settings' => get_post_meta( $p->ID, '_elementor_page_settings', true ),
		'exported'      => $p->post_modified,
	);
	file_put_contents( $dir . $slug . '.json', wp_json_encode( $snap, JSON_UNESCAPED_UNICODE ) );
	echo $slug . ': ' . strlen( $elementor ) . " Bytes (Stand {$p->post_modified})\n";
}
