<?php
/**
 * Demo content importer.
 *
 * Reads data/content.json and creates:
 *   - All pages with Elementor JSON
 *   - All CPT entries (Team, Einzelleistungen, Karriere, Veranstaltungen, News, Publikationen)
 *   - Main nav menu
 *   - Homepage + Blog page options
 *   - Attaches team photos bundled in data/media/team/
 *
 * Trigger: admin "Werkzeuge → Energiesozietät importieren". Idempotent (skips if already imported).
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Importer {

	const OPT_DONE = 'esc_import_done';
	const OPT_MAP  = 'esc_import_idmap'; // slug => post_id

	public static function run( $force = false ) {
		@set_time_limit( 600 );
		if ( ! $force && get_option( self::OPT_DONE ) ) {
			return array( 'status' => 'skipped', 'message' => 'Bereits importiert. Über „Import erzwingen" kann erneut importiert werden.' );
		}
		// Ensure CPTs and taxonomies exist.
		ESC_CPTs::register();

		$data = self::load_data();
		if ( is_wp_error( $data ) ) { return $data; }

		$map = get_option( self::OPT_MAP, array() );
		if ( ! is_array( $map ) ) { $map = array(); }

		// 1. Import media (team photos)
		$media_map = self::import_media( $data );

		// 2. Create CPT entries
		self::import_team( $data['team'], $media_map, $map );
		self::import_einzelleistungen( $data['einzelleistungen'], $map );
		self::import_karriere( $data['karriere'], $map );
		self::import_veranstaltungen( $data['veranstaltungen'], $map );
		self::import_news( $data['news'], $map );
		self::import_publikationen( $data['publikationen'], $map );

		// 3. Create pages (so we have IDs for menu)
		self::import_pages( $map, $data );

		// 4. Create main menu
		self::build_menu( $map );

		// 5. Site options: set homepage + blog page
		self::configure_site( $map );

		update_option( self::OPT_MAP, $map );
		update_option( self::OPT_DONE, current_time( 'mysql' ) );

		flush_rewrite_rules();

		return array( 'status' => 'ok', 'map' => $map, 'message' => 'Import erfolgreich.' );
	}

	public static function reset() {
		delete_option( self::OPT_DONE );
		// Do not delete the actual content — users may have edited it.
	}

	protected static function load_data() {
		$file = ESC_DIR . 'data/content.json';
		if ( ! file_exists( $file ) ) {
			return new WP_Error( 'esc_no_data', 'content.json nicht gefunden.' );
		}
		$json = file_get_contents( $file );
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) { return new WP_Error( 'esc_bad_data', 'content.json ist fehlerhaft.' ); }
		return $data;
	}

	/** Import bundled media into the WP media library. */
	protected static function import_media( $data ) {
		$map = array();
		$dir = ESC_DIR . 'data/media/team/';
		foreach ( (array) ( $data['team'] ?? array() ) as $t ) {
			if ( empty( $t['image_file'] ) ) { continue; }
			$src = ESC_DIR . 'data/media/' . $t['image_file'];
			if ( ! file_exists( $src ) ) { continue; }
			$attach_id = self::sideload( $src, $t['name'] . ' Portrait' );
			if ( $attach_id && ! is_wp_error( $attach_id ) ) {
				$map[ 'team:' . $t['slug'] ] = $attach_id;
			}
		}
		return $map;
	}

	/** Sideload a file that already exists on disk into the media library. */
	protected static function sideload( $src, $title = '' ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$filename = wp_unique_filename( wp_upload_dir()['path'], basename( $src ) );
		$dest = wp_upload_dir()['path'] . '/' . $filename;
		if ( ! @copy( $src, $dest ) ) {
			return new WP_Error( 'copy_failed', 'Konnte Datei nicht kopieren: ' . $src );
		}

		$filetype = wp_check_filetype( $dest, null );
		$attachment = array(
			'post_mime_type' => $filetype['type'] ? $filetype['type'] : 'image/jpeg',
			'post_title'     => $title ? sanitize_file_name( $title ) : pathinfo( $dest, PATHINFO_FILENAME ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		);
		$attach_id = wp_insert_attachment( $attachment, $dest );
		if ( ! is_wp_error( $attach_id ) ) {
			$meta = wp_generate_attachment_metadata( $attach_id, $dest );
			wp_update_attachment_metadata( $attach_id, $meta );
		}
		return $attach_id;
	}

	/** Get or create a post. Returns post ID. */
	protected static function upsert_post( $args ) {
		$defaults = array(
			'post_status' => 'publish',
			'post_author' => 1,
		);
		$args = array_merge( $defaults, $args );
		// Try find existing by slug + type
		$existing = get_page_by_path( $args['post_name'], OBJECT, $args['post_type'] );
		if ( $existing ) {
			$args['ID'] = $existing->ID;
			wp_update_post( wp_slash( $args ) );
			return $existing->ID;
		}
		$id = wp_insert_post( wp_slash( $args ), true );
		return is_wp_error( $id ) ? 0 : $id;
	}

	protected static function import_team( $team, $media_map, &$map ) {
		$order = 0;
		foreach ( $team as $t ) {
			$id = self::upsert_post( array(
				'post_type'    => 'es_team',
				'post_name'    => $t['slug'],
				'post_title'   => $t['name'],
				'post_content' => wpautop_safe( (string) $t['bio'] ),
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( (string) $t['bio'] ), 26, '…' ),
				'menu_order'   => $order++,
			) );
			if ( ! $id ) { continue; }
			update_post_meta( $id, 'es_role',     (string) $t['role'] );
			update_post_meta( $id, 'es_more_bio', (string) ( $t['more_bio'] ?? '' ) );
			update_post_meta( $id, 'es_email',    (string) ( $t['email'] ?? '' ) );
			update_post_meta( $id, 'es_phone',    (string) ( $t['phone'] ?? '+49 211 159232-0' ) );
			update_post_meta( $id, 'es_location', (string) ( $t['location'] ?? 'Düsseldorf' ) );
			update_post_meta( $id, 'es_field',    (string) ( $t['field'] ?? 'rechtsberatung' ) );
			if ( ! empty( $t['focus_areas'] ) ) {
				update_post_meta( $id, 'es_focus_areas', $t['focus_areas'] );
			}
			if ( ! empty( $t['career'] ) ) {
				update_post_meta( $id, 'es_career', $t['career'] );
			}
			if ( ! empty( $media_map[ 'team:' . $t['slug'] ] ) ) {
				set_post_thumbnail( $id, $media_map[ 'team:' . $t['slug'] ] );
			}
			$map[ 'team:' . $t['slug'] ] = $id;
		}
	}

	protected static function import_einzelleistungen( $items, &$map ) {
		foreach ( $items as $e ) {
			$id = self::upsert_post( array(
				'post_type'    => 'es_einzelleistung',
				'post_name'    => $e['slug'],
				'post_title'   => $e['title'],
				'post_content' => wpautop_safe( (string) $e['description'] ),
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( (string) $e['description'] ), 24, '…' ),
			) );
			if ( ! $id ) { continue; }
			update_post_meta( $id, 'es_subtitle', (string) $e['subtitle'] );
			update_post_meta( $id, 'es_closing',  (string) $e['closing'] );
			if ( ! empty( $e['bullets'] ) ) { update_post_meta( $id, 'es_bullets', $e['bullets'] ); }
			if ( ! empty( $e['beratungsfeld'] ) ) {
				wp_set_object_terms( $id, $e['beratungsfeld'], 'es_beratungsfeld' );
			}
			$map[ 'einzel:' . $e['slug'] ] = $id;
		}
	}

	protected static function import_karriere( $items, &$map ) {
		foreach ( $items as $k ) {
			$id = self::upsert_post( array(
				'post_type'    => 'es_karriere',
				'post_name'    => $k['slug'],
				'post_title'   => $k['title'],
				'post_content' => wpautop_safe( (string) $k['description'] ),
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( (string) $k['description'] ), 26, '…' ),
			) );
			if ( ! $id ) { continue; }
			update_post_meta( $id, 'es_department',      (string) ( $k['department'] ?? 'Consulting' ) );
			update_post_meta( $id, 'es_location',        (string) ( $k['location']  ?? 'Düsseldorf' ) );
			update_post_meta( $id, 'es_employment_type', (string) ( $k['employment_type'] ?? 'Vollzeit' ) );
			if ( ! empty( $k['bullets'] ) ) { update_post_meta( $id, 'es_bullets', $k['bullets'] ); }
			$map[ 'karriere:' . $k['slug'] ] = $id;
		}
	}

	protected static function import_veranstaltungen( $items, &$map ) {
		foreach ( $items as $v ) {
			// Extract a date from title if possible
			$date = '';
			if ( preg_match( '/(\d{1,2}\.\d{1,2}\.20\d{2})/', $v['title'], $m ) ) { $date = date( 'Y-m-d', strtotime( str_replace( '.', '-', $m[1] ) ) ); }
			elseif ( ! empty( $v['date'] ) ) { $date = substr( $v['date'], 0, 10 ); }
			$id = self::upsert_post( array(
				'post_type'    => 'es_veranstaltung',
				'post_name'    => $v['slug'],
				'post_title'   => $v['title'],
				'post_content' => wpautop_safe( (string) $v['description'] ),
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( (string) $v['description'] ), 24, '…' ),
			) );
			if ( ! $id ) { continue; }
			if ( $date ) {
				update_post_meta( $id, 'es_start_date', $date );
			}
			$map[ 'veranst:' . $v['slug'] ] = $id;
		}
	}

	protected static function import_news( $items, &$map ) {
		foreach ( $items as $n ) {
			$id = self::upsert_post( array(
				'post_type'    => 'es_news',
				'post_name'    => $n['slug'],
				'post_title'   => $n['title'],
				'post_content' => (string) $n['body'],
				'post_date'    => isset( $n['date'] ) ? $n['date'] : current_time( 'mysql' ),
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( (string) $n['body'] ), 28, '…' ),
			) );
			if ( ! $id ) { continue; }
			$map[ 'news:' . $n['slug'] ] = $id;
		}
	}

	protected static function import_publikationen( $items, &$map ) {
		foreach ( $items as $i => $p ) {
			$slug = sanitize_title( $p['title'] );
			$post_date = ! empty( $p['date'] ) ? date( 'Y-m-d H:i:s', strtotime( (string) $p['date'] ) ) : current_time( 'mysql' );
			$id = self::upsert_post( array(
				'post_type'    => 'es_publikation',
				'post_name'    => $slug,
				'post_title'   => $p['title'],
				'post_content' => wpautop_safe( (string) ( $p['body'] ?? '' ) ),
				'post_excerpt' => wp_trim_words( wp_strip_all_tags( (string) ( $p['body'] ?? '' ) ), 24, '…' ),
				'post_date'    => $post_date,
				'post_date_gmt'=> $post_date,
			) );
			if ( ! $id ) { continue; }
			if ( ! empty( $p['link'] ) )   { update_post_meta( $id, 'es_link',   $p['link'] ); }
			if ( ! empty( $p['cat'] ) )    { update_post_meta( $id, 'es_cat',    $p['cat'] ); }
			if ( ! empty( $p['author'] ) ) { update_post_meta( $id, 'es_author', $p['author'] ); }
			if ( ! empty( $p['source'] ) ) { update_post_meta( $id, 'es_source', $p['source'] ); }
			if ( ! empty( $p['date'] ) )   { update_post_meta( $id, 'es_publication_date', $p['date'] ); }
			// Team-Autor-IDs per slug → post_id
			$author_ids = array();
			foreach ( (array) ( $p['author_slugs'] ?? array() ) as $aslug ) {
				if ( ! empty( $map[ 'team:' . $aslug ] ) ) { $author_ids[] = (int) $map[ 'team:' . $aslug ]; }
			}
			update_post_meta( $id, 'es_author_ids', $author_ids );
			// Beratungsfelder
			update_post_meta( $id, 'es_fields', (array) ( $p['fields'] ?? array() ) );
			$map[ 'pub:' . $slug ] = $id;
		}
	}

	protected static function import_pages( &$map, $data ) {
		$pages = ESC_Page_Blueprints::all( $data );
		foreach ( $pages as $slug => $page ) {
			$id = self::upsert_post( array(
				'post_type'    => 'page',
				'post_name'    => $slug,
				'post_title'   => $page['title'],
				'post_content' => isset( $page['post_content'] ) ? $page['post_content'] : '',
				'menu_order'   => isset( $page['menu_order'] ) ? $page['menu_order'] : 0,
			) );
			if ( ! $id ) { continue; }
			$map[ 'page:' . $slug ] = $id;
			if ( isset( $page['elementor'] ) ) {
				update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( $page['elementor'] ) ) );
				update_post_meta( $id, '_elementor_edit_mode', 'builder' );
				update_post_meta( $id, '_elementor_template_type', 'wp-page' );
				update_post_meta( $id, '_elementor_version', '3.20.0' );
			}
			if ( isset( $page['page_settings'] ) ) {
				update_post_meta( $id, '_elementor_page_settings', $page['page_settings'] );
			}
		}
	}

	protected static function build_menu( &$map ) {
		$menu_name = 'Hauptmenü';
		$menu      = wp_get_nav_menu_object( $menu_name );
		$menu_id   = $menu ? $menu->term_id : wp_create_nav_menu( $menu_name );
		if ( is_wp_error( $menu_id ) ) { return; }

		// Remove any existing items so it's idempotent
		$existing = wp_get_nav_menu_items( $menu_id );
		if ( is_array( $existing ) ) {
			foreach ( $existing as $item ) { wp_delete_post( $item->ID, true ); }
		}

		// Kontakt + Karriere leben in den Header-Buttons rechts — nicht im Hauptmenü.
		$order = array(
			'philosophie', 'leistungen', 'team', 'publikationen', 'news', 'veranstaltungen',
		);
		$leistungen_children = array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' );

		$parent_ids = array();
		$i = 1;
		foreach ( $order as $slug ) {
			if ( empty( $map[ 'page:' . $slug ] ) ) { continue; }
			$parent_ids[ $slug ] = wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'    => self::page_title( $slug ),
				'menu-item-object'   => 'page',
				'menu-item-object-id'=> $map[ 'page:' . $slug ],
				'menu-item-type'     => 'post_type',
				'menu-item-status'   => 'publish',
				'menu-item-position' => $i++,
			) );
		}
		foreach ( $leistungen_children as $slug ) {
			if ( empty( $map[ 'page:' . $slug ] ) || empty( $parent_ids['leistungen'] ) ) { continue; }
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'    => self::page_title( $slug ),
				'menu-item-object'   => 'page',
				'menu-item-object-id'=> $map[ 'page:' . $slug ],
				'menu-item-type'     => 'post_type',
				'menu-item-parent-id'=> $parent_ids['leistungen'],
				'menu-item-status'   => 'publish',
				'menu-item-position' => $i++,
			) );
		}

		$locations = get_theme_mod( 'nav_menu_locations' );
		if ( ! is_array( $locations ) ) { $locations = array(); }
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}

	protected static function page_title( $slug ) {
		$map = array(
			'home' => 'Home', 'philosophie' => 'Philosophie', 'leistungen' => 'Leistungen',
			'rechtsberatung' => 'Rechtsberatung', 'steuerberatung' => 'Steuerberatung', 'unternehmensberatung' => 'Unternehmensberatung',
			'team' => 'Team', 'publikationen' => 'Publikationen', 'karriere' => 'Karriere',
			'news' => 'News', 'veranstaltungen' => 'Veranstaltungen', 'kontakt' => 'Kontakt',
			'impressum' => 'Impressum', 'datenschutzerklaerung' => 'Datenschutzerklärung',
		);
		return isset( $map[ $slug ] ) ? $map[ $slug ] : ucfirst( $slug );
	}

	protected static function configure_site( &$map ) {
		if ( ! empty( $map['page:home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', (int) $map['page:home'] );
		}
		// NOTE: Do NOT set page_for_posts to the "news" page — we want /news/ to render
		// the Elementor-composed page (which itself embeds the es_news CPT grid).
		update_option( 'page_for_posts', 0 );
		// Pretty permalinks (for pages + CPTs)
		if ( get_option( 'permalink_structure' ) === '' ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}
		update_option( 'blogname', 'Energiesozietät' );
		update_option( 'blogdescription', 'Recht · Steuern · Beratung' );
	}
}
