<?php
/** Konverter-Roundtrip: Rubriken html->text->html muss byte-identisch sein. */
define( 'WP_USE_THEMES', false );
require __DIR__ . '/site/wp-load.php';
wp_set_current_user( 1 );
$fail = 0; $n = 0;
foreach ( get_posts( array( 'post_type' => 'es_einzelleistung', 'post_status' => 'any', 'numberposts' => -1 ) ) as $p ) {
	foreach ( array( 'es_accordion', 'es_accordion_en' ) as $k ) {
		$acc = get_post_meta( $p->ID, $k, true );
		if ( ! is_array( $acc ) ) { continue; }
		foreach ( $acc as $i => $row ) {
			$c = (string) ( $row['content'] ?? '' );
			if ( '' === $c ) { continue; }
			$n++;
			$t = ESC_MetaBoxes::acc_html_to_text( $c );
			$h = wp_kses_post( ESC_MetaBoxes::acc_text_to_html( $t ) );
			if ( false !== strpos( $t, '<' ) ) { $fail++; echo "TAGS IM TEXT: {$p->post_name} $k #$i\n"; }
			if ( $h !== $c ) { $fail++; echo "DIFF: {$p->post_name} $k #$i\n"; }
		}
	}
}
echo $fail ? "FEHLER: $fail von $n\n" : "KONVERTER OK: $n Rubriken, Text ohne Tags, HTML byte-identisch\n";
