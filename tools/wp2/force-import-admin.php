<?php
/** Import erzwingen (als Admin wegen KSES) + Caches leeren. Aus wp2 aufrufen. */
define( 'WP_USE_THEMES', false );
require __DIR__ . '/site/wp-load.php';
wp_set_current_user( 1 );
ESC_Importer::run( true );
global $wpdb;
$wpdb->query( "DELETE FROM wp_postmeta WHERE meta_key='_elementor_element_cache'" );
if ( class_exists( '\Elementor\Plugin' ) ) { \Elementor\Plugin::$instance->files_manager->clear_cache(); }
flush_rewrite_rules();
echo "Import erzwungen\n";
