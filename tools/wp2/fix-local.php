<?php
/**
 * Lokale Testinstanz nach dem Einspielen eines Live-Dumps reparieren:
 * Live-Domain -> http://127.0.0.1:8099 (serialisierungssicher), https-Reste,
 * Theme/Plugins aktivieren. Aus dem wp2-Verzeichnis aufrufen.
 */
define( 'SHORTINIT', true );
require __DIR__ . '/site/wp-load.php';
global $wpdb;
function deep_r( $v, $o, $n ) {
	if ( is_string( $v ) ) { return str_replace( array( $o, str_replace( '/', '\\/', $o ) ), array( $n, str_replace( '/', '\\/', $n ) ), $v ); }
	if ( is_array( $v ) ) { foreach ( $v as $k => $x ) { $v[ $k ] = deep_r( $x, $o, $n ); } }
	if ( is_object( $v ) ) { foreach ( get_object_vars( $v ) as $k => $x ) { $v->$k = deep_r( $x, $o, $n ); } }
	return $v;
}
function smart_r( $s, $o, $n ) {
	if ( strpos( $s, $o ) === false && strpos( $s, str_replace( '/', '\\/', $o ) ) === false ) { return $s; }
	$u = @unserialize( $s );
	if ( $u !== false || $s === 'b:0;' ) { return serialize( deep_r( $u, $o, $n ) ); }
	return deep_r( $s, $o, $n );
}
$total = 0;
foreach ( array(
	array( 'https://1229160.eu15.myftpupload.com', 'http://127.0.0.1:8099', '%myftpupload%' ),
	array( 'https://127.0.0.1:8099', 'http://127.0.0.1:8099', '%https://127.0.0.1%' ),
) as $round ) {
	list( $o, $n, $like ) = $round;
	$like_esc = str_replace( array( '://', '/' ), array( ':\\\\/\\\\/', '\\\\/' ), $like );
	foreach ( array(
		array( 'wp_options',  'option_id', 'option_value' ),
		array( 'wp_postmeta', 'meta_id',   'meta_value' ),
		array( 'wp_usermeta', 'umeta_id',  'meta_value' ),
		array( 'wp_termmeta', 'meta_id',   'meta_value' ),
	) as $t ) {
		list( $tb, $pk, $col ) = $t;
		foreach ( $wpdb->get_results( "SELECT $pk AS pk, $col AS v FROM $tb WHERE $col LIKE '$like' OR $col LIKE '$like_esc'" ) as $r ) {
			$nv = smart_r( $r->v, $o, $n );
			if ( $nv !== $r->v ) { $wpdb->update( $tb, array( $col => $nv ), array( $pk => $r->pk ) ); $total++; }
		}
	}
	foreach ( $wpdb->get_results( "SELECT ID, post_content, guid FROM wp_posts WHERE post_content LIKE '$like' OR guid LIKE '$like' OR post_content LIKE '$like_esc'" ) as $r ) {
		$wpdb->update( 'wp_posts', array( 'post_content' => deep_r( $r->post_content, $o, $n ), 'guid' => deep_r( $r->guid, $o, $n ) ), array( 'ID' => $r->ID ) );
		$total++;
	}
}
update_option( 'template', 'energiesozietaet' );
update_option( 'stylesheet', 'energiesozietaet' );
update_option( 'active_plugins', array( 'elementor/elementor.php', 'energiesozietaet-core/energiesozietaet-core.php' ) );
echo "fix-local ok ($total)\n";
