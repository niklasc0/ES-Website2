<?php
/**
 * Page blueprints — generates Elementor JSON for every top-level page.
 *
 * STATUS: Stub. `all()` returns minimal placeholder blueprints so the importer
 * can create the page records + menu. The real rich Elementor layouts are
 * authored in a later commit. Impressum + Datenschutz already use the raw
 * legal HTML from content.json.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Page_Blueprints {

	public static function all( $data ) {
		$b = 'ESC_Elementor_Builder';
		$pages = array();
		$order = 0;

		// Simple hero + intro-text + optional shortcode pattern, used for most landing pages until
		// the full per-page layouts ship.
		$stock = function( $title, $lead, $shortcode = '' ) use ( $b ) {
			$elements = array();
			$elements[] = $b::hero( array(
				'eyebrow' => $title,
				'heading' => $title,
				'lead'    => $lead,
			) );
			if ( $shortcode ) {
				$elements[] = $b::section( array( array( 'widgets' => array( $b::shortcode( $shortcode ) ) ) ), array(
					'layout'        => 'boxed',
					'content_width' => array( 'unit' => 'px', 'size' => 1240 ),
					'padding'       => array( 'unit' => 'px', 'top' => '80', 'right' => '24', 'bottom' => '100', 'left' => '24', 'isLinked' => false ),
				) );
			}
			return $elements;
		};

		$pages['home'] = array(
			'title'      => 'Home',
			'menu_order' => ++$order,
			'elementor'  => $stock(
				'Expertise trifft Leidenschaft',
				'Energiesozietät GmbH — Recht · Steuern · Beratung.',
				'[es_team limit="8" columns="4"]'
			),
		);

		$pages['philosophie'] = array(
			'title'      => 'Philosophie',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Unsere Philosophie', 'Ergebnisse, die weitertragen.' ),
		);

		$pages['leistungen'] = array(
			'title'      => 'Leistungen',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Leistungen', 'Interdisziplinäre Beratung: Recht, Steuern, Unternehmensberatung.' ),
		);

		$pages['rechtsberatung'] = array(
			'title'      => 'Rechtsberatung',
			'menu_order' => ++$order,
			'elementor'  => $stock(
				'Rechtsberatung',
				'Wir stellen juristische Lösungen in den Gesamtkontext.',
				'[es_einzelleistungen beratungsfeld="rechtsberatung" columns="3"]'
			),
		);

		$pages['steuerberatung'] = array(
			'title'      => 'Steuerberatung',
			'menu_order' => ++$order,
			'elementor'  => $stock(
				'Steuerberatung',
				'Fortlaufende Steuerberatung, Gestaltungsberatung, Neustrukturierungen.',
				'[es_einzelleistungen beratungsfeld="steuerberatung" columns="3"]'
			),
		);

		$pages['unternehmensberatung'] = array(
			'title'      => 'Unternehmensberatung',
			'menu_order' => ++$order,
			'elementor'  => $stock(
				'Unternehmensberatung',
				'Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen.',
				'[es_einzelleistungen beratungsfeld="unternehmensberatung" columns="3"]'
			),
		);

		$pages['team'] = array(
			'title'      => 'Team',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Team', 'Ein erfahrenes, interdisziplinäres Team — agil und pragmatisch.', '[es_team columns="4"]' ),
		);

		$pages['publikationen'] = array(
			'title'      => 'Publikationen',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Publikationen', 'Unsere Veröffentlichungen.', '[es_publikationen columns="2"]' ),
		);

		$pages['karriere'] = array(
			'title'      => 'Karriere',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Karriere', 'Starte gemeinsam mit uns durch.', '[es_karriere columns="3"]' ),
		);

		$pages['news'] = array(
			'title'      => 'News',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'News', 'Aktuelles aus der Kanzlei.', '[es_news limit="-1" columns="3"]' ),
		);

		$pages['veranstaltungen'] = array(
			'title'      => 'Veranstaltungen',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Veranstaltungen', 'Aktuelle Termine.', '[es_veranstaltungen columns="3"]' ),
		);

		$pages['kontakt'] = array(
			'title'      => 'Kontakt',
			'menu_order' => ++$order,
			'elementor'  => $stock( 'Kontakt', 'Bitte zögern Sie nicht, uns anzusprechen.' ),
		);

		$pages['impressum'] = array(
			'title'        => 'Impressum',
			'menu_order'   => ++$order,
			'post_content' => isset( $data['legal_impressum'] ) ? $data['legal_impressum'] : '',
		);

		$pages['datenschutzerklaerung'] = array(
			'title'        => 'Datenschutzerklärung',
			'menu_order'   => ++$order,
			'post_content' => isset( $data['legal_datenschutzerklaerung'] ) ? $data['legal_datenschutzerklaerung'] : '',
		);

		return $pages;
	}
}
