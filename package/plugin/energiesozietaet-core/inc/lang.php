<?php
/**
 * Zweisprachigkeit DE/EN – Sprachweiche, /en/-Routing, Feld-Fallbacks.
 *
 * Modell: Ein Inhalt = ein Beitrag. Englische Fassungen liegen als Meta-Felder
 * (es_title_en, es_content_en, es_excerpt_en, es_slug_en) am selben Beitrag.
 * Alles unter /en/… rendert dieselben Routen im EN-Kontext; leere EN-Felder
 * fallen auf Deutsch zurück. Unübersetzte Inhalte werden im EN-Kontext auf
 * noindex gesetzt; die News-Listen blenden sie aus.
 *
 * Feste Seiten: EN-Kopien sind eigene WP-Seiten (englischer Slug) und werden
 * über die Meta-Paare es_lang=en / es_translation_of=<ID> mit der deutschen
 * Seite verknüpft.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ES_Lang {

	/** Aktuelle Sprache: 'de' | 'en'. */
	protected static $lang = 'de';

	/** CPT-URL-Basen DE => EN. */
	const BASES = array(
		'news-artikel'  => 'news-article',
		'teammitglied'  => 'team-member',
		'leistung'      => 'service',
		'stelle'        => 'job',
		'veranstaltung' => 'event',
		'publikation'   => 'publication',
	);

	/** CPTs, deren Detailseiten im EN-Kontext ohne Übersetzung noindex bekommen. */
	const CPTS = array( 'es_news', 'es_team', 'es_einzelleistung', 'es_karriere', 'es_veranstaltung', 'es_publikation' );

	/** CPT => deutsche URL-Basis (Spiegel der rewrite-Slugs in cpts.php). */
	const CPT_BASE = array(
		'es_news'           => 'news-artikel',
		'es_team'           => 'teammitglied',
		'es_einzelleistung' => 'leistung',
		'es_karriere'       => 'stelle',
		'es_veranstaltung'  => 'veranstaltung',
		'es_publikation'    => 'publikation',
	);

	public static function init() {
		self::detect();
		if ( 'en' === self::$lang ) {
			add_filter( 'locale', array( __CLASS__, 'frontend_locale' ) );
			add_filter( 'request', array( __CLASS__, 'resolve_en_slugs' ) );
			add_filter( 'the_title', array( __CLASS__, 'filter_title' ), 10, 2 );
			add_filter( 'single_post_title', function ( $title, $post ) {
				return $post ? self::filter_title( $title, $post->ID ) : $title;
			}, 10, 2 );
			add_filter( 'the_content', array( __CLASS__, 'filter_content' ), 8 );
			add_filter( 'get_the_excerpt', array( __CLASS__, 'filter_excerpt' ), 10, 2 );
			add_filter( 'body_class', function ( $c ) { $c[] = 'lang-en'; return $c; } );
			add_filter( 'language_attributes', function () { return 'lang="en"'; } );
		}
		// Permalink-Filter laufen immer – sie prüfen den Kontext selbst.
		add_filter( 'post_type_link', array( __CLASS__, 'localize_permalink' ), 10, 2 );
		add_filter( 'page_link', array( __CLASS__, 'localize_page_link' ), 10, 2 );
		add_action( 'wp_head', array( __CLASS__, 'head_tags' ), 1 );
		add_action( 'template_redirect', array( __CLASS__, 'canonical_redirects' ) );
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'sitemap_exclude_untranslated' ), 10, 2 );
	}

	/** Kanonische Weiterleitungen zwischen den Sprachfassungen. */
	public static function canonical_redirects() {
		if ( is_admin() || ! is_singular() ) { return; }
		$id = get_queried_object_id();
		// EN-Seitenkopie ohne /en/-Präfix aufgerufen → auf die EN-URL umleiten
		if ( 'de' === self::$lang && 'page' === get_post_type( $id ) && 'en' === get_post_meta( $id, 'es_lang', true ) ) {
			wp_safe_redirect( self::en_page_url( $id ), 301 );
			exit;
		}
		// /en/<deutscher Slug>/ obwohl eine EN-Fassung existiert → EN-URL
		if ( 'en' === self::$lang ) {
			$req = isset( $_SERVER['REQUEST_URI'] ) ? (string) strtok( (string) $_SERVER['REQUEST_URI'], '?' ) : '';
			if ( in_array( get_post_type( $id ), self::CPTS, true ) ) {
				$canonical = wp_parse_url( self::to_en_url( $id ), PHP_URL_PATH );
				// REQUEST_URI wurde von detect() bereits entpräfixt/gemappt –
				// vergleichbar machen (DE-Basis + DE-Slug)
				$current = wp_parse_url( self::to_de_url( $id ), PHP_URL_PATH );
				$slug_en = get_post_meta( $id, 'es_slug_en', true );
				if ( $slug_en && $req === $current ) {
					wp_safe_redirect( $canonical, 301 );
					exit;
				}
			} elseif ( 'page' === get_post_type( $id ) && 'en' === get_post_meta( $id, 'es_lang', true ) ) {
				$public = (string) get_post_meta( $id, 'es_public_slug', true );
				$de_id  = (int) get_post_meta( $id, 'es_translation_of', true );
				if ( $public && $de_id ) {
					$de_slug = get_post( $de_id ) ? get_post( $de_id )->post_name : '';
					if ( $de_slug && '/' . $de_slug . '/' === $req && $de_slug !== $public ) {
						wp_safe_redirect( self::en_page_url( $id ), 301 );
						exit;
					}
				}
			}
		}
	}

	/** Unübersetzte EN-Seitenkopien aus der XML-Sitemap halten. */
	public static function sitemap_exclude_untranslated( $args, $post_type ) {
		if ( 'page' !== $post_type ) { return $args; }
		$mq = isset( $args['meta_query'] ) && is_array( $args['meta_query'] ) ? $args['meta_query'] : array();
		$mq[] = array(
			'relation' => 'OR',
			array( 'key' => 'es_lang', 'compare' => 'NOT EXISTS' ),
			array( 'key' => 'es_lang', 'value' => 'en', 'compare' => '!=' ),
			array( 'key' => 'es_translated', 'value' => '1' ),
		);
		$args['meta_query'] = $mq;
		return $args;
	}

	/** /en/-Präfix erkennen und Request auf die DE-Routen zurückschreiben. */
	protected static function detect() {
		if ( is_admin() ) { return; }
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
		if ( ! preg_match( '#^/en(/|$|\?)#', $uri ) ) { return; }
		self::$lang = 'en';
		$rest = preg_replace( '#^/en#', '', $uri );
		if ( '' === $rest || '?' === $rest[0] ) { $rest = '/' . $rest; }
		// EN-CPT-Basis im ersten Pfadsegment auf die DE-Basis mappen
		foreach ( self::BASES as $de => $en ) {
			if ( preg_match( '#^/' . preg_quote( $en, '#' ) . '(/|$)#', $rest ) ) {
				$rest = preg_replace( '#^/' . preg_quote( $en, '#' ) . '#', '/' . $de, $rest );
				break;
			}
		}
		// Startseite: /en/ rendert die EN-Home-Kopie (Slug en-home), falls vorhanden
		if ( '/' === strtok( $rest, '?' ) ) {
			global $wpdb;
			$has = $wpdb ? $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_name = 'en-home' AND post_type = 'page' AND post_status = 'publish' LIMIT 1" ) : 0;
			if ( $has ) { $rest = '/en-home' . substr( $rest, 1 ); if ( '/en-home' === $rest ) { $rest .= '/'; } }
		}
		$_SERVER['REQUEST_URI'] = $rest;
		// Manche Server (u. a. PHP-Built-in) liefern den Pfad zusätzlich als
		// PATH_INFO – WP bevorzugt diesen beim Routing.
		$rest_path = (string) wp_parse_url( $rest, PHP_URL_PATH );
		foreach ( array( 'PATH_INFO', 'ORIG_PATH_INFO' ) as $k ) {
			if ( isset( $_SERVER[ $k ] ) && '' !== $_SERVER[ $k ] ) { $_SERVER[ $k ] = $rest_path; }
		}
	}

	public static function current() { return self::$lang; }

	public static function frontend_locale( $locale ) {
		return is_admin() ? $locale : 'en_US';
	}

	/** EN-Slug (es_slug_en) auf den DE-post_name auflösen. */
	public static function resolve_en_slugs( $vars ) {
		// Feste Seiten: /en/<slug>/ auf die EN-Kopie auflösen
		if ( ! empty( $vars['pagename'] ) && empty( $vars['post_type'] ) ) {
			$slug = $vars['pagename'];
			global $wpdb;
			// a) Öffentlicher EN-Slug einer Kopie (es_public_slug)
			$en_name = $wpdb->get_var( $wpdb->prepare(
				"SELECT p.post_name FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id
				 WHERE m.meta_key = 'es_public_slug' AND m.meta_value = %s AND p.post_type = 'page' AND p.post_status = 'publish' LIMIT 1", $slug ) );
			// b) Deutscher Slug mit verknüpfter EN-Kopie
			if ( ! $en_name ) {
				$de = get_page_by_path( $slug );
				if ( $de ) {
					$partner = self::translation_of( $de->ID );
					if ( $partner && 'en' === get_post_meta( $partner, 'es_lang', true ) ) {
						$pp = get_post( $partner );
						if ( $pp && 'publish' === $pp->post_status ) { $en_name = $pp->post_name; }
					}
				}
			}
			if ( $en_name ) { $vars['pagename'] = $en_name; }
			return $vars;
		}
		if ( empty( $vars['name'] ) ) { return $vars; }
		$slug = $vars['name'];
		$type = ! empty( $vars['post_type'] ) ? $vars['post_type'] : '';
		global $wpdb;
		$sql = "SELECT p.post_name, p.post_type FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id
			WHERE m.meta_key = 'es_slug_en' AND m.meta_value = %s AND p.post_status = 'publish'";
		$row = $type
			? $wpdb->get_row( $wpdb->prepare( $sql . ' AND p.post_type = %s LIMIT 1', $slug, $type ) )
			: $wpdb->get_row( $wpdb->prepare( $sql . ' LIMIT 1', $slug ) );
		if ( $row ) {
			$vars['name'] = $row->post_name;
			// CPT-Rewrites tragen den Slug zusätzlich in der eigenen Query-Var (z. B. es_news)
			if ( $type && isset( $vars[ $type ] ) ) { $vars[ $type ] = $row->post_name; }
			return $vars;
		}
		// Öffentlicher EN-Slug einer Seitenkopie kommt ohne verbose Page-Rule
		// als generischer name-Query an (z. B. /en/philosophy/)
		if ( ! $type ) {
			$en_name = $wpdb->get_var( $wpdb->prepare(
				"SELECT p.post_name FROM {$wpdb->postmeta} m JOIN {$wpdb->posts} p ON p.ID = m.post_id
				 WHERE m.meta_key = 'es_public_slug' AND m.meta_value = %s AND p.post_type = 'page' AND p.post_status = 'publish' LIMIT 1", $slug ) );
			if ( $en_name ) {
				unset( $vars['name'] );
				$vars['pagename'] = $en_name;
			}
		}
		return $vars;
	}

	// ---- Feld-Fallbacks (nur im EN-Kontext registriert) ----

	public static function filter_title( $title, $post_id = 0 ) {
		if ( ! $post_id || is_admin() ) { return $title; }
		if ( ! in_array( get_post_type( $post_id ), self::CPTS, true ) ) { return $title; }
		$en = get_post_meta( $post_id, 'es_title_en', true );
		return ( '' !== trim( (string) $en ) ) ? $en : $title;
	}

	public static function filter_content( $content ) {
		if ( is_admin() || ! in_the_loop() ) { return $content; }
		$id = get_the_ID();
		if ( ! $id || ! in_array( get_post_type( $id ), self::CPTS, true ) ) { return $content; }
		$en = get_post_meta( $id, 'es_content_en', true );
		return ( '' !== trim( (string) $en ) ) ? wpautop( $en ) : $content;
	}

	public static function filter_excerpt( $excerpt, $post = null ) {
		$id = $post ? $post->ID : get_the_ID();
		if ( ! $id || is_admin() || ! in_array( get_post_type( $id ), self::CPTS, true ) ) { return $excerpt; }
		$en = get_post_meta( $id, 'es_excerpt_en', true );
		if ( '' !== trim( (string) $en ) ) { return $en; }
		$cen = get_post_meta( $id, 'es_content_en', true );
		if ( '' !== trim( (string) $cen ) ) { return wp_trim_words( wp_strip_all_tags( $cen ), 26, '…' ); }
		return $excerpt;
	}

	// ---- Permalinks ----

	/** DE-Permalink eines CPT-Beitrags in seine EN-Form bringen. */
	public static function localize_permalink( $url, $post ) {
		if ( 'en' !== self::$lang || is_admin() ) { return $url; }
		if ( ! in_array( $post->post_type, self::CPTS, true ) ) { return $url; }
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $path ) { return $url; }
		foreach ( self::BASES as $de => $en ) {
			if ( preg_match( '#^/' . preg_quote( $de, '#' ) . '/#', $path ) ) {
				$slug_en = get_post_meta( $post->ID, 'es_slug_en', true );
				$slug    = $slug_en ? $slug_en : $post->post_name;
				return home_url( '/en/' . $en . '/' . $slug . '/' );
			}
		}
		return $url;
	}

	/** Seiten-Links im EN-Kontext: auf die verknüpfte EN-Seite (oder /en/-Präfix) zeigen. */
	public static function localize_page_link( $url, $page_id ) {
		if ( 'en' !== self::$lang || is_admin() ) { return $url; }
		// Zeigt der Link auf eine DE-Seite mit EN-Partner? Dann Partner-URL.
		$partner = self::translation_of( $page_id );
		if ( $partner && 'en' === get_post_meta( $partner, 'es_lang', true ) ) {
			$p = get_post( $partner );
			if ( $p && 'publish' === $p->post_status ) {
				return self::en_page_url( $partner );
			}
		}
		// EN-Seite selbst → /en/<öffentlicher Slug>/
		if ( 'en' === get_post_meta( $page_id, 'es_lang', true ) ) {
			return self::en_page_url( $page_id );
		}
		return $url;
	}

	/** Öffentliche URL einer EN-Seitenkopie. */
	public static function en_page_url( $page_id ) {
		$public = (string) get_post_meta( $page_id, 'es_public_slug', true );
		if ( '' === $public ) {
			$p = get_post( $page_id );
			$public = ( 'en-home' === $p->post_name ) ? '' : $p->post_name;
		}
		return home_url( '/en/' . ( $public ? $public . '/' : '' ) );
	}

	/** Partner-Seite (DE→EN oder EN→DE) einer festen Seite. */
	public static function translation_of( $page_id ) {
		$to = (int) get_post_meta( $page_id, 'es_translation_of', true );
		if ( $to ) { return $to; }
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'es_translation_of' AND meta_value = %d LIMIT 1", $page_id
		) );
	}

	// ---- <head>: hreflang + noindex ----

	public static function head_tags() {
		if ( ! is_singular() ) { return; }
		$id   = get_queried_object_id();
		$type = get_post_type( $id );

		if ( in_array( $type, self::CPTS, true ) ) {
			$translated = ( '' !== trim( (string) get_post_meta( $id, 'es_title_en', true ) ) );
			$de_url = self::to_de_url( $id );
			if ( $translated ) {
				$en_url = self::to_en_url( $id );
				echo '<link rel="alternate" hreflang="de" href="' . esc_url( $de_url ) . '" />' . "\n";
				echo '<link rel="alternate" hreflang="en" href="' . esc_url( $en_url ) . '" />' . "\n";
				echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $de_url ) . '" />' . "\n";
			} elseif ( 'en' === self::$lang ) {
				// Unübersetzt unter /en/ → nicht indexieren, Canonical auf DE
				echo '<meta name="robots" content="noindex,follow" />' . "\n";
				echo '<link rel="canonical" href="' . esc_url( $de_url ) . '" />' . "\n";
			}
		} elseif ( 'page' === $type ) {
			$partner = self::translation_of( $id );
			if ( $partner ) {
				$en_first = ( 'en' === get_post_meta( $id, 'es_lang', true ) );
				$en_id = $en_first ? $id : $partner;
				$de_id = $en_first ? $partner : $id;
				$de_url = ( (int) get_option( 'page_on_front' ) === (int) $de_id ) ? home_url( '/' ) : home_url( '/' . get_post( $de_id )->post_name . '/' );
				$en_url = self::en_page_url( $en_id );
				// SEO-Gate: solange die EN-Kopie nicht als übersetzt markiert ist,
				// keine hreflangs und die EN-Fassung nicht indexieren.
				$translated = (bool) get_post_meta( $en_id, 'es_translated', true );
				if ( $translated ) {
					echo '<link rel="alternate" hreflang="de" href="' . esc_url( $de_url ) . '" />' . "\n";
					echo '<link rel="alternate" hreflang="en" href="' . esc_url( $en_url ) . '" />' . "\n";
					echo '<link rel="alternate" hreflang="x-default" href="' . esc_url( $de_url ) . '" />' . "\n";
				} elseif ( 'en' === self::$lang ) {
					echo '<meta name="robots" content="noindex,follow" />' . "\n";
					echo '<link rel="canonical" href="' . esc_url( $de_url ) . '" />' . "\n";
				}
			} elseif ( 'en' === self::$lang ) {
				echo '<meta name="robots" content="noindex,follow" />' . "\n";
			}
		}
	}

	protected static function strip_en( $url ) {
		return preg_replace( '#/en/#', '/', $url, 1 );
	}

	/** Deutsche URL eines CPT-Beitrags – unabhängig von aktiven Permalink-Filtern. */
	public static function to_de_url( $post_id ) {
		$post = get_post( $post_id );
		$base = isset( self::CPT_BASE[ $post->post_type ] ) ? self::CPT_BASE[ $post->post_type ] : '';
		return $base ? home_url( '/' . $base . '/' . $post->post_name . '/' ) : self::strip_en( get_permalink( $post_id ) );
	}

	/** EN-URL eines CPT-Beitrags (unabhängig vom aktuellen Kontext). */
	public static function to_en_url( $post_id ) {
		$post = get_post( $post_id );
		$base = isset( self::CPT_BASE[ $post->post_type ] ) ? self::CPT_BASE[ $post->post_type ] : '';
		if ( $base && isset( self::BASES[ $base ] ) ) {
			$slug_en = get_post_meta( $post_id, 'es_slug_en', true );
			return home_url( '/en/' . self::BASES[ $base ] . '/' . ( $slug_en ? $slug_en : $post->post_name ) . '/' );
		}
		return home_url( '/en' . wp_parse_url( self::strip_en( get_permalink( $post_id ) ), PHP_URL_PATH ) );
	}

	/** URL der jeweils anderen Sprachfassung der aktuellen Ansicht (für den Umschalter). */
	public static function switch_url() {
		$to_en = ( 'de' === self::$lang );
		if ( is_singular( self::CPTS ) ) {
			$id = get_queried_object_id();
			return $to_en ? self::to_en_url( $id ) : self::to_de_url( $id );
		}
		if ( is_page() ) {
			$id      = get_queried_object_id();
			$partner = self::translation_of( $id );
			if ( $partner ) {
				if ( 'en' === get_post_meta( $partner, 'es_lang', true ) ) {
					return self::en_page_url( $partner );
				}
				// Zurück zur deutschen Seite: URL ungefiltert bauen, sonst
				// würde der EN-Kontext den Link wieder auf /en/ zurückbiegen.
				if ( (int) get_option( 'page_on_front' ) === (int) $partner ) { return home_url( '/' ); }
				$pp = get_post( $partner );
				return home_url( '/' . $pp->post_name . '/' );
			}
		}
		// Fallback: aktuelle URL präfixen/entpräfixen
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '/';
		if ( $to_en ) {
			// DE-Basis → EN-Basis zurückmappen
			foreach ( self::BASES as $de => $en ) {
				if ( preg_match( '#^/' . preg_quote( $de, '#' ) . '(/|$)#', $uri ) ) {
					$uri = preg_replace( '#^/' . preg_quote( $de, '#' ) . '#', '/' . $en, $uri );
					break;
				}
			}
			return home_url( '/en' . $uri );
		}
		return home_url( $uri );
	}

	/** Öffentliche EN-Slugs der Seitenkopien (Kunde kann später andere liefern). */
	const PAGE_SLUGS_EN = array(
		'home'                  => '',
		'philosophie'           => 'philosophy',
		'leistungen'            => 'services',
		'rechtsberatung'        => 'legal-advice',
		'steuerberatung'        => 'tax-advice',
		'unternehmensberatung'  => 'management-consulting',
		'team'                  => 'team',
		'karriere'              => 'careers',
		'kontakt'               => 'contact',
		'news'                  => 'news',
		'veranstaltungen'       => 'events',
		'publikationen'         => 'publications',
		'impressum'             => 'legal-notice',
		'datenschutzerklaerung' => 'privacy-policy',
	);

	/**
	 * Fehlende EN-Seitenkopien anlegen (Struktur + Inhalte = deutsche Live-Seite,
	 * bis Übersetzungen eingepflegt sind). Bestehende Kopien werden nie überschrieben.
	 */
	/** Englische Seitentitel der Kopien (für <title>/Backend-Liste). */
	const PAGE_TITLES_EN = array(
		'home' => 'Home', 'philosophie' => 'Philosophy', 'leistungen' => 'Services',
		'rechtsberatung' => 'Legal Advice', 'steuerberatung' => 'Tax Advice',
		'unternehmensberatung' => 'Management Consulting', 'team' => 'Team',
		'karriere' => 'Careers', 'kontakt' => 'Contact', 'news' => 'News',
		'veranstaltungen' => 'Events', 'publikationen' => 'Publications',
		'impressum' => 'Legal Notice', 'datenschutzerklaerung' => 'Privacy Policy',
	);

	public static function create_page_copies() {
		$made = array();
		// Bekannte Kernseiten + alle weiteren veröffentlichten Elementor-Seiten
		$slugs = array_keys( self::PAGE_SLUGS_EN );
		$all = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => -1 ) );
		foreach ( $all as $pg ) {
			if ( 'en' === get_post_meta( $pg->ID, 'es_lang', true ) ) { continue; }
			if ( in_array( $pg->post_name, $slugs, true ) ) { continue; }
			if ( get_post_meta( $pg->ID, '_elementor_data', true ) ) { $slugs[] = $pg->post_name; }
		}
		foreach ( $slugs as $de_slug ) {
			$public = isset( self::PAGE_SLUGS_EN[ $de_slug ] ) ? self::PAGE_SLUGS_EN[ $de_slug ] : $de_slug;
			$de = get_page_by_path( $de_slug );
			if ( ! $de ) { continue; }
			$existing = self::translation_of( $de->ID );
			if ( $existing ) {
				// Nachziehen: englischer Titel, falls die Kopie noch den deutschen trägt
				$copy = get_post( $existing );
				if ( $copy && isset( self::PAGE_TITLES_EN[ $de_slug ] ) && $copy->post_title === $de->post_title && $de->post_title !== self::PAGE_TITLES_EN[ $de_slug ] ) {
					wp_update_post( array( 'ID' => $existing, 'post_title' => self::PAGE_TITLES_EN[ $de_slug ] ) );
				}
				continue;
			}
			$en_name = 'en-' . $de_slug;
			$id = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_name'    => $en_name,
				'post_title'   => isset( self::PAGE_TITLES_EN[ $de_slug ] ) ? self::PAGE_TITLES_EN[ $de_slug ] : $de->post_title,
				'post_content' => $de->post_content,
				'post_author'  => $de->post_author,
			), true );
			if ( is_wp_error( $id ) || ! $id ) { continue; }
			foreach ( array( '_elementor_data', '_elementor_edit_mode', '_elementor_template_type', '_elementor_version', '_elementor_page_settings', '_wp_page_template' ) as $mk ) {
				$mv = get_post_meta( $de->ID, $mk, true );
				if ( '' !== $mv && null !== $mv && array() !== $mv ) {
					update_post_meta( $id, $mk, is_string( $mv ) ? wp_slash( $mv ) : $mv );
				}
			}
			update_post_meta( $id, 'es_lang', 'en' );
			update_post_meta( $id, 'es_translation_of', $de->ID );
			update_post_meta( $id, 'es_public_slug', ( '' === $public && 'home' !== $de_slug ) ? $de_slug : $public );
			update_post_meta( $de->ID, 'es_translation_of', $id );
			$made[] = $de_slug;
		}
		return $made;
	}
}

ES_Lang::init();

// ---- Template-Helper ----

/** Aktuelle Sprache ('de'|'en'). */
function es_lang() { return ES_Lang::current(); }

/** true im /en/-Kontext. */
function es_is_en() { return 'en' === ES_Lang::current(); }

/** UI-String je Sprache. */
function es_t( $de, $en ) { return es_is_en() ? $en : $de; }

/** Hat der Beitrag eine englische Fassung (Titel EN gefüllt)? */
function es_has_en( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	return '' !== trim( (string) get_post_meta( $post_id, 'es_title_en', true ) );
}

/** Meta-Feld sprachbewusst: liefert {$key}_en im EN-Kontext, sonst {$key} (mit Fallback). */
function es_meta_ml( $key, $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( es_is_en() ) {
		$en = get_post_meta( $post_id, $key . '_en', true );
		if ( is_string( $en ) ? ( '' !== trim( $en ) ) : ! empty( $en ) ) { return $en; }
	}
	return get_post_meta( $post_id, $key, true );
}
