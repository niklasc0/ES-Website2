<?php
/**
 * Team-Namen in Inhalten automatisch mit der jeweiligen Personenseite
 * verlinken. Die URLs entstehen zur Laufzeit über get_permalink() und
 * funktionieren damit auf jeder Domain (keine fest gespeicherten Links).
 *
 * Erkannt werden die vollen Post-Titel der Team-Mitglieder sowie
 * Varianten ohne akademische Titel (z. B. "Sven-Joachim Otto" für
 * "Prof. Dr. Sven-Joachim Otto"). Bereits verlinkte Namen (innerhalb
 * von <a>…</a>) bleiben unangetastet.
 *
 * @package Energiesozietaet_Core
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Name → URL-Karte aller veröffentlichten Team-Mitglieder.
 * Längste Namen zuerst, damit "Prof. Dr. X Y" vor "X Y" ersetzt wird.
 */
function esc_team_link_map() {
	static $map = null;
	if ( null !== $map ) { return $map; }
	$map = array();
	$members = get_posts( array(
		'post_type'      => 'es_team',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	) );
	foreach ( $members as $m ) {
		$url  = get_permalink( $m );
		$name = trim( $m->post_title );
		if ( '' === $name || ! $url ) { continue; }
		$map[ $name ] = $url;
		// Varianten ohne akademische Titel: "Prof. Dr. X" → "Dr. X" → "X"
		$bare = $name;
		while ( preg_match( '/^(Prof\.|Dr\.|Dipl\.-[\p{L}]+\.?|mult\.|h\.c\.)\s+/u', $bare ) ) {
			$bare = trim( preg_replace( '/^(Prof\.|Dr\.|Dipl\.-[\p{L}]+\.?|mult\.|h\.c\.)\s+/u', '', $bare, 1 ) );
			if ( '' !== $bare && ! isset( $map[ $bare ] ) ) {
				$map[ $bare ] = $url;
			}
		}
	}
	uksort( $map, function ( $a, $b ) {
		return mb_strlen( $b ) - mb_strlen( $a );
	} );
	return $map;
}

/**
 * Slug → URL-Karte (post_name plus Variante ohne Titel-Präfix dr-/prof-dr-).
 */
function esc_team_slug_map() {
	static $map = null;
	if ( null !== $map ) { return $map; }
	$map = array();
	$members = get_posts( array(
		'post_type'      => 'es_team',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
	) );
	foreach ( $members as $m ) {
		$url = get_permalink( $m );
		if ( ! $url ) { continue; }
		$map[ $m->post_name ] = $url;
		$bare = preg_replace( '/^(prof-)?(dr-)+/', '', $m->post_name );
		if ( $bare && ! isset( $map[ $bare ] ) ) { $map[ $bare ] = $url; }
	}
	return $map;
}

/**
 * Bestehende Links auf Team-Seiten (z. B. hart kodierte Links der alten
 * Live-Seite wie https://www.energiesozietaet.de/team/detail/<slug>)
 * auf die lokale Personenseite umschreiben. Existiert die Person nicht
 * mehr, wird der Link entfernt und nur der Name als Text belassen.
 */
function esc_rewrite_team_hrefs( $html ) {
	if ( false === stripos( $html, '/team' ) && false === stripos( $html, 'mailto:' ) ) { return $html; }
	// mailto-Links, deren Linktext ein Team-Name ist (nicht die Adresse
	// selbst), ebenfalls auf die Personenseite umbiegen.
	$html = preg_replace_callback(
		'/<a\b[^>]*href="mailto:[^"]*"[^>]*>(.*?)<\/a>/is',
		function ( $m ) {
			$names = esc_team_link_map();
			$plain = trim( wp_strip_all_tags( $m[1] ) );
			if ( isset( $names[ $plain ] ) ) {
				return '<a class="es-team-link" href="' . esc_url( $names[ $plain ] ) . '">' . $m[1] . '</a>';
			}
			return $m[0];
		},
		$html
	);
	return preg_replace_callback(
		'/<a\b[^>]*href="[^"]*\/(?:team\/detail|teammitglied)\/([^"\/?#]+)\/?"[^>]*>(.*?)<\/a>/is',
		function ( $m ) {
			$slug = strtolower( $m[1] );
			$text = $m[2];
			$slugs = esc_team_slug_map();
			// 1) Über den Linktext (robust gegen Slug-Abweichungen/Tippfehler)
			$names = esc_team_link_map();
			$plain = trim( wp_strip_all_tags( $text ) );
			$url   = isset( $names[ $plain ] ) ? $names[ $plain ] : '';
			// 2) Über den Slug (direkt, ohne Titel-Präfix, alte ue/oe/ae-Schreibweise)
			if ( ! $url ) {
				$cands = array( $slug, preg_replace( '/^(prof-)?(dr-)+/', '', $slug ) );
				$cands[] = str_replace( array( 'ue', 'oe', 'ae' ), array( 'u', 'o', 'a' ), $cands[1] );
				foreach ( $cands as $c ) {
					if ( isset( $slugs[ $c ] ) ) { $url = $slugs[ $c ]; break; }
				}
			}
			if ( ! $url ) { return $text; } // Person nicht (mehr) vorhanden → entlinken
			return '<a class="es-team-link" href="' . esc_url( $url ) . '">' . $text . '</a>';
		},
		$html
	);
}

/**
 * Ersetzt Team-Namen in HTML durch Links zur Personenseite.
 * Nur Textknoten werden verändert; Inhalte innerhalb bestehender
 * <a>-Elemente und HTML-Attribute bleiben unberührt.
 */
function esc_link_team_names( $html ) {
	if ( ! is_string( $html ) || '' === $html ) {
		return $html;
	}
	$map = esc_team_link_map();
	if ( empty( $map ) ) { return $html; }
	$html = esc_rewrite_team_hrefs( $html );

	$parts   = preg_split( '/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE );
	$depth_a = 0;
	foreach ( $parts as $i => $part ) {
		if ( '' === $part ) { continue; }
		if ( '<' === $part[0] ) {
			if ( preg_match( '/^<a[\s>]/i', $part ) ) { $depth_a++; }
			elseif ( preg_match( '/^<\/a>/i', $part ) ) { $depth_a = max( 0, $depth_a - 1 ); }
			continue;
		}
		if ( $depth_a > 0 ) { continue; }
		foreach ( $map as $name => $url ) {
			if ( false === strpos( $part, $name ) ) { continue; }
			// Ganzwort-Treffer (case-sensitiv – Slugs in URLs sind
			// kleingeschrieben und werden so nie getroffen).
			$part = preg_replace(
				'/(?<![\p{L}\p{N}\p{M}-])' . preg_quote( $name, '/' ) . '(?![\p{L}\p{N}\p{M}-])/u',
				'<a class="es-team-link" href="' . esc_url( $url ) . '">' . $name . '</a>',
				$part
			);
		}
		$parts[ $i ] = $part;
	}
	return implode( '', $parts );
}

/**
 * the_content-Filter: nur für redaktionelle Inhaltstypen, nicht für
 * Elementor-Seiten (dort würden Links das Layout der Zitate/Karten stören).
 */
function esc_link_team_names_content( $content ) {
	$type = get_post_type();
	if ( ! in_array( $type, array( 'es_news', 'es_einzelleistung', 'es_karriere', 'es_veranstaltung', 'es_publikation' ), true ) ) {
		return $content;
	}
	return esc_link_team_names( $content );
}
add_filter( 'the_content', 'esc_link_team_names_content', 20 );
