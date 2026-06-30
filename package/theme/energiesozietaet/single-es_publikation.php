<?php
/**
 * Single Publikation — keine eigene Detailseite. Wir leiten auf die externe
 * Quelle weiter (sofern hinterlegt) oder zurück auf die Übersicht.
 *
 * @package Energiesozietaet
 */
while ( have_posts() ) : the_post();
	$link   = es_meta( 'es_link' );
	$target = $link ? $link : home_url( '/publikationen/' );
	wp_redirect( esc_url_raw( $target ), 302 );
	exit;
endwhile;
