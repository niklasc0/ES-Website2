<?php
/**
 * 301-Weiterleitungen von den URL-Strukturen der alten TYPO3-Website
 * (www.energiesozietaet.de) auf die neuen WordPress-Pfade.
 *
 * Greift nur bei 404 – existierende neue Seiten werden nie angefasst.
 * Grundlage ist die komplette Sitemap der alten Website (Stand 09/2026):
 * Seitenbaum, /team/detail/<slug> und /news/detail/<slug>. Auflösung in
 * drei Stufen: exakter Slug, Alias-Tabelle, normalisierter Vergleich
 * (Transliteration oe/ue/ae, Titel-Präfixe, Zähl-Suffixe, gekürzte Slugs).
 * Ohne Treffer landet der Besucher auf der jeweiligen Übersichtsseite.
 *
 * @package Energiesozietaet_Core
 */

defined( 'ABSPATH' ) || exit;

class ESC_Redirects {

	/** Alte Team-Slugs, die sich nicht algorithmisch ableiten lassen (Tippfehler auf der alten Seite). */
	const TEAM_ALIASES = array(
		'bermhardine-kleinhenz-jeannot' => 'dr-bernhardine-kleinhenz-jeannot',
		'julian-fuehrung'               => 'julian-fuehring',
	);

	/**
	 * TYPO3-Personen-Nummern (tx_tmenergies_personvcard[person]=<uid>) aus den
	 * QR-Codes der NEUEN gedruckten Visitenkarten, zugeordnet zu den heutigen
	 * Team-Slugs. Unbekannte Nummern leiten auf die Team-Seite.
	 */
	const TYPO3_PERSON_IDS = array(
		24 => 'niklas-celecki',
	);

	/** Alte Leistungs-Slugs ohne Entsprechung: Ziel ist die passende Bereichsseite. */
	const SERVICE_FALLBACKS = array(
		'steuerrecht'          => '/steuerberatung/',
		'gluecksspielrecht'    => '/rechtsberatung/',
		'projektmanagement'    => '/unternehmensberatung/',
		'wasserstoff'          => '/unternehmensberatung/',
		'erneuerbare-energien' => '/unternehmensberatung/',
	);

	public static function register() {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 1 );
	}

	public static function maybe_redirect() {
		if ( ! is_404() ) { return; }
		$path = (string) parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
		$path = strtolower( trim( rawurldecode( $path ), '/' ) );
		$path = preg_replace( '/\.html?$/', '', $path );
		if ( '' === $path ) { return; }

		$target = self::resolve( $path );
		if ( $target ) {
			wp_safe_redirect( home_url( $target ), 301 );
			exit;
		}
	}

	/** Liefert den neuen Pfad (mit führendem /) oder null, wenn keine Regel greift. */
	public static function resolve( $path ) {
		// Statische Seiten der alten Website mit neuem Namen.
		if ( 'datenschutz' === $path ) { return '/datenschutzerklaerung/'; }
		if ( 'anmeldung' === $path ) { return '/veranstaltungen/'; }
		if ( 'sitemap.xml' === $path ) { return '/wp-sitemap.xml'; }

		// Blätter-Seiten (/team/seite/2) und Detail-Container ohne Slug.
		if ( preg_match( '#^(team|news|publikationen|veranstaltungen)/seite/\d+$#', $path, $m ) ) {
			return '/' . $m[1] . '/';
		}
		if ( 'veranstaltungen/detail' === $path || 0 === strpos( $path, 'veranstaltungen/detail/' ) ) {
			return '/veranstaltungen/';
		}

		// Alte Stellenanzeigen (enthalten m/w/d-Pfadsegmente): auf die Karriere-Seite.
		if ( 0 === strpos( $path, 'karriere/' ) ) { return '/karriere/'; }

		// Team-Profile.
		if ( preg_match( '#^team/detail/(.+)$#', $path, $m ) ) {
			return self::resolve_team( $m[1] );
		}

		// News-Artikel.
		if ( preg_match( '#^news/detail/(.+)$#', $path, $m ) ) {
			return self::resolve_news( $m[1] );
		}

		// Leistungs-Seiten (alte Struktur: alles unter /leistungen/...).
		if ( preg_match( '#^leistungen/(.+)$#', $path, $m ) ) {
			return self::resolve_service( $m[1] );
		}

		return null;
	}

	protected static function resolve_team( $old_slug ) {
		return self::permalink_path( self::resolve_team_slug( $old_slug ), 'es_team' ) ?: '/team/';
	}

	/**
	 * Öffentlicher Helfer: findet zu einem (auch alten) Team-Slug den
	 * aktuellen Slug oder null. Wird außer von den Weiterleitungen auch vom
	 * vCard-Download des Themes genutzt (QR-Codes der gedruckten
	 * Visitenkarten tragen teils noch die alten Slugs).
	 */
	public static function resolve_team_slug( $old_slug ) {
		$old_slug = self::TEAM_ALIASES[ $old_slug ] ?? $old_slug;
		return self::match_slug( $old_slug, 'es_team' );
	}

	protected static function resolve_news( $old_slug ) {
		return self::permalink_path( self::match_slug( $old_slug, 'es_news' ), 'es_news' ) ?: '/news/';
	}

	/** Permalink-Pfad zum gefundenen Slug, damit die Weiterleitung ohne Umweg auf der kanonischen URL landet. */
	protected static function permalink_path( $slug, $post_type ) {
		if ( ! $slug ) { return null; }
		$posts = get_posts( array( 'name' => $slug, 'post_type' => $post_type, 'post_status' => 'publish', 'numberposts' => 1 ) );
		if ( ! $posts ) { return null; }
		$path = (string) parse_url( get_permalink( $posts[0]->ID ), PHP_URL_PATH );
		return $path ?: null;
	}

	protected static function resolve_service( $rest ) {
		$parts = explode( '/', $rest );
		// /leistungen/unternehmensberatung/<slug> u.ä.: letztes Segment zählt,
		// das Bereichssegment dient als Rückfallebene.
		$last    = array_pop( $parts );
		$bereich = $parts ? $parts[0] : '';
		$base    = preg_replace( '/(-\d+)+$/', '', $last );

		if ( in_array( $base, array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' ), true ) ) {
			return '/' . $base . '/';
		}
		if ( isset( self::SERVICE_FALLBACKS[ $base ] ) ) {
			return self::SERVICE_FALLBACKS[ $base ];
		}
		$hit = self::permalink_path( self::match_slug( $last, 'es_einzelleistung' ), 'es_einzelleistung' );
		if ( $hit ) { return $hit; }
		if ( in_array( $bereich, array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' ), true ) ) {
			return '/' . $bereich . '/';
		}
		return '/leistungen/';
	}

	/**
	 * Sucht zum alten Slug den passenden veröffentlichten Beitrag des
	 * Post-Types: erst exakt, dann normalisiert, dann als Präfix-Vergleich
	 * (die neuen Slugs sind teils anders gekürzt als die alten).
	 */
	protected static function match_slug( $old_slug, $post_type ) {
		$slugs = self::published_slugs( $post_type );
		if ( in_array( $old_slug, $slugs, true ) ) { return $old_slug; }

		$stripped = preg_replace( '/(-\d+)+$/', '', $old_slug );
		if ( $stripped !== $old_slug && in_array( $stripped, $slugs, true ) ) { return $stripped; }

		$norm_old = self::normalize( $old_slug );
		$best     = null;
		foreach ( $slugs as $slug ) {
			$norm_new = self::normalize( $slug );
			if ( $norm_new === $norm_old ) { return $slug; }
			// Wortreihenfolge egal (z.B. kooperation-und-transaktion).
			if ( self::token_key( $old_slug ) === self::token_key( $slug ) ) { return $slug; }
			// Unterschiedlich gekürzte lange Slugs: Präfix-Vergleich.
			$len = min( strlen( $norm_old ), strlen( $norm_new ) );
			if ( $len >= 25 && 0 === substr_compare( $norm_old, substr( $norm_new, 0, $len ), 0, $len )
				&& ( null === $best || strlen( $slug ) > strlen( $best ) ) ) {
				$best = $slug;
			}
		}
		return $best;
	}

	/** Slug-Normalisierung: Titel-Präfixe, Abschluss-Suffixe, Umlaut-Schreibweisen, Doppelbuchstaben. */
	protected static function normalize( $slug ) {
		$s = strtolower( $slug );
		$s = preg_replace( '/^(prof-)?(dr-)+/', '', $s );
		$s = preg_replace( '/-(msc|llm|mba|ll-m|m-sc)$/', '', $s );
		$s = preg_replace( '/(-\d+)+$/', '', $s );
		$s = str_replace( array( 'oe', 'ue', 'ae' ), array( 'o', 'u', 'a' ), $s );
		$s = str_replace( '-', '', $s );
		$s = preg_replace( '/(.)\1+/', '$1', $s );
		return $s;
	}

	/** Bindestrich-Wörter alphabetisch sortiert, für vertauschte Wortreihenfolgen. */
	protected static function token_key( $slug ) {
		$t = explode( '-', strtolower( preg_replace( '/(-\d+)+$/', '', $slug ) ) );
		sort( $t );
		return implode( '-', $t );
	}

	protected static function published_slugs( $post_type ) {
		static $cache = array();
		if ( ! isset( $cache[ $post_type ] ) ) {
			global $wpdb;
			$cache[ $post_type ] = $wpdb->get_col( $wpdb->prepare(
				"SELECT post_name FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
				$post_type
			) );
		}
		return $cache[ $post_type ];
	}
}

ESC_Redirects::register();
