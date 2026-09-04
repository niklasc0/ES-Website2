<?php
/**
 * Regeneriert data/content.json aus der Testinstanz-Datenbank.
 *
 * Es werden nur Eintraege neu aufgebaut, die seit dem Cutoff geaendert
 * wurden (Aufruf: php export-content.php "YYYY-MM-DD HH:MM:SS"); alle
 * anderen bleiben byte-identisch aus der bestehenden Datei erhalten.
 * Nicht ableitbare Felder (image_file, alte IDs) werden pro Slug aus der
 * bestehenden Datei uebernommen. Neue Eintraege werden angehaengt, nicht
 * mehr vorhandene (Papierkorb/geloescht) entfernt. Die Datei wird als
 * Roh-JSON geschrieben; das Aufrufer-Skript formatiert sie anschliessend
 * kanonisch (python: json.dumps(indent=1, ensure_ascii=False)).
 *
 * Aus dem wp2-Verzeichnis aufrufen; schreibt direkt ins Repo.
 */
define( 'WP_USE_THEMES', false );
require __DIR__ . '/site/wp-load.php';
wp_set_current_user( 1 );

$cutoff = isset( $argv[1] ) ? $argv[1] : '1970-01-01 00:00:00';
$file   = '/home/user/ES-Website2/package/plugin/energiesozietaet-core/data/content.json';
$data   = json_decode( file_get_contents( $file ), true );
if ( ! is_array( $data ) ) { fwrite( STDERR, "content.json nicht lesbar\n" ); exit( 1 ); }

/** Bestehende Liste mit DB-Stand mergen: Reihenfolge der Datei behalten,
 *  geaenderte Eintraege ersetzen, neue anhaengen, verschwundene entfernen. */
function merge_section( $existing, $db_entries, $cutoff, $label ) {
	$stats = array( 'neu' => array(), 'aktualisiert' => array(), 'entfernt' => array() );
	$by_slug = array();
	foreach ( $db_entries as $e ) { $by_slug[ $e['__slug'] ] = $e; }
	$out = array();
	foreach ( $existing as $old ) {
		$slug = $old['slug'] ?? sanitize_title( $old['title'] ?? '' );
		if ( ! isset( $by_slug[ $slug ] ) ) { $stats['entfernt'][] = $slug; continue; }
		$db = $by_slug[ $slug ];
		unset( $by_slug[ $slug ] );
		if ( $db['__modified'] < $cutoff ) { $out[] = $old; continue; }
		unset( $db['__slug'], $db['__modified'] );
		// Schluessel-Reihenfolge und nicht ableitbare Felder der Datei behalten
		$neu = array();
		foreach ( $old as $k => $v ) { $neu[ $k ] = array_key_exists( $k, $db ) ? $db[ $k ] : $v; }
		foreach ( $db as $k => $v ) { if ( ! array_key_exists( $k, $neu ) ) { $neu[ $k ] = $v; } }
		$out[] = $neu;
		$stats['aktualisiert'][] = $slug;
	}
	foreach ( $by_slug as $slug => $db ) {
		unset( $db['__slug'], $db['__modified'] );
		$out[] = $db;
		$stats['neu'][] = $slug;
	}
	printf( "%-16s %d Eintraege (%d aktualisiert, %d neu, %d entfernt)%s\n", $label, count( $out ),
		count( $stats['aktualisiert'] ), count( $stats['neu'] ), count( $stats['entfernt'] ),
		$stats['entfernt'] ? ' [entfernt: ' . implode( ', ', $stats['entfernt'] ) . ']' : '' );
	foreach ( array( 'aktualisiert', 'neu' ) as $s ) {
		if ( $stats[ $s ] ) { echo "  $s: " . implode( ', ', $stats[ $s ] ) . "\n"; }
	}
	return $out;
}

function meta_arr( $id, $key ) {
	$v = get_post_meta( $id, $key, true );
	return is_array( $v ) ? array_values( $v ) : array();
}

// --- Team (Reihenfolge = menu_order, wie vom Importer gesetzt) ---
$db = array();
foreach ( get_posts( array( 'post_type' => 'es_team', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $p ) {
	$db[] = array(
		'__slug' => $p->post_name, '__modified' => $p->post_modified,
		'id' => $p->ID, 'name' => $p->post_title, 'slug' => $p->post_name,
		'role' => (string) get_post_meta( $p->ID, 'es_role', true ),
		'bio' => $p->post_content,
		'more_bio' => (string) get_post_meta( $p->ID, 'es_more_bio', true ),
		'focus_areas' => meta_arr( $p->ID, 'es_focus_areas' ),
		'image_file' => 'team/' . $p->post_name . '.jpg',
		'field' => (string) get_post_meta( $p->ID, 'es_field', true ),
		'email' => (string) get_post_meta( $p->ID, 'es_email', true ),
		'phone' => (string) get_post_meta( $p->ID, 'es_phone', true ),
		'location' => (string) get_post_meta( $p->ID, 'es_location', true ),
		'career' => meta_arr( $p->ID, 'es_career' ),
	);
}
$data['team'] = merge_section( $data['team'], $db, $cutoff, 'team' );

// --- Einzelleistungen ---
$db = array();
foreach ( get_posts( array( 'post_type' => 'es_einzelleistung', 'post_status' => 'publish', 'numberposts' => -1 ) ) as $p ) {
	$terms = wp_get_post_terms( $p->ID, 'es_beratungsfeld', array( 'fields' => 'slugs' ) );
	$db[] = array(
		'__slug' => $p->post_name, '__modified' => $p->post_modified,
		'id' => $p->ID, 'title' => $p->post_title, 'slug' => $p->post_name,
		'beratungsfeld' => ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : '',
		'subtitle' => (string) get_post_meta( $p->ID, 'es_subtitle', true ),
		'description' => $p->post_content,
		'accordion' => meta_arr( $p->ID, 'es_accordion' ),
		'bullets' => meta_arr( $p->ID, 'es_bullets' ),
		'closing' => (string) get_post_meta( $p->ID, 'es_closing', true ),
	);
}
$data['einzelleistungen'] = merge_section( $data['einzelleistungen'], $db, $cutoff, 'einzelleistungen' );

// --- Karriere ---
$db = array();
foreach ( get_posts( array( 'post_type' => 'es_karriere', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ) ) as $p ) {
	$db[] = array(
		'__slug' => $p->post_name, '__modified' => $p->post_modified,
		'id' => $p->ID, 'title' => $p->post_title, 'slug' => $p->post_name,
		'department' => (string) get_post_meta( $p->ID, 'es_department', true ),
		'description' => $p->post_content,
		'employment_type' => (string) get_post_meta( $p->ID, 'es_employment_type', true ),
		'location' => (string) get_post_meta( $p->ID, 'es_location', true ),
		'tasks' => meta_arr( $p->ID, 'es_tasks' ),
		'profile' => meta_arr( $p->ID, 'es_profile' ),
		'offer' => meta_arr( $p->ID, 'es_offer' ),
		'closing' => (string) get_post_meta( $p->ID, 'es_closing', true ),
		'image_file' => 'karriere/' . $p->post_name . '.jpg',
		'field' => (string) get_post_meta( $p->ID, 'es_field', true ),
	);
}
$data['karriere'] = merge_section( $data['karriere'], $db, $cutoff, 'karriere' );

// --- News ---
$db = array();
foreach ( get_posts( array( 'post_type' => 'es_news', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ) ) as $p ) {
	$db[] = array(
		'__slug' => $p->post_name, '__modified' => $p->post_modified,
		'id' => $p->ID, 'title' => $p->post_title, 'slug' => $p->post_name,
		'body' => $p->post_content,
		'date' => $p->post_date,
		'teaser' => (string) $p->post_excerpt,
		'image_file' => 'news/' . $p->post_name . '.jpg',
	);
}
$data['news'] = merge_section( $data['news'], $db, $cutoff, 'news' );

// --- Publikationen (Slug = sanitize_title(Titel), kein slug-Feld im Schema) ---
$db = array();
foreach ( get_posts( array( 'post_type' => 'es_publikation', 'post_status' => 'publish', 'numberposts' => -1, 'orderby' => 'date', 'order' => 'DESC' ) ) as $p ) {
	$author_slugs = array();
	foreach ( (array) get_post_meta( $p->ID, 'es_author_ids', true ) as $aid ) {
		$ap = get_post( (int) $aid );
		if ( $ap ) { $author_slugs[] = $ap->post_name; }
	}
	$db[] = array(
		'__slug' => $p->post_name, '__modified' => $p->post_modified,
		'title' => $p->post_title,
		'body' => $p->post_content,
		'link' => (string) get_post_meta( $p->ID, 'es_link', true ),
		'cat' => (string) get_post_meta( $p->ID, 'es_cat', true ),
		'date' => (string) get_post_meta( $p->ID, 'es_publication_date', true ),
		'author' => (string) get_post_meta( $p->ID, 'es_author', true ),
		'author_slugs' => $author_slugs,
		'fields' => array_values( (array) get_post_meta( $p->ID, 'es_fields', true ) ),
		'source' => (string) get_post_meta( $p->ID, 'es_source', true ),
	);
}
$data['publikationen'] = merge_section( $data['publikationen'], $db, $cutoff, 'publikationen' );

// --- Rechtsseiten (Inhalt der WP-Seiten) ---
foreach ( array( 'impressum' => 'legal_impressum', 'datenschutzerklaerung' => 'legal_datenschutzerklaerung' ) as $slug => $key ) {
	$p = get_page_by_path( $slug );
	if ( $p && $p->post_modified >= $cutoff ) {
		$data[ $key ] = $p->post_content;
		echo "$key aktualisiert (" . strlen( $p->post_content ) . "B)\n";
	}
}

file_put_contents( $file . '.tmp', wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
echo "geschrieben: {$file}.tmp (bitte kanonisch formatieren und umbenennen)\n";
