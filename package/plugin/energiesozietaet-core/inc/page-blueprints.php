<?php
/**
 * Page blueprints — generates Elementor JSON for every top-level page.
 *
 * Each entry returns an array with:
 *   - title, menu_order
 *   - elementor => array of Elementor sections (array form, JSON-encoded by importer)
 *   - optional post_content (used for pages that are not Elementor-driven, e.g. legal)
 *   - optional page_settings (serialized Elementor page settings string)
 *
 * All layouts use ONLY core Elementor widgets (heading, text-editor, button,
 * spacer, divider, image, shortcode, icon-list, icon-box, accordion) — no
 * third-party Elementor addons required.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Page_Blueprints {

	/**
	 * Entry point. $data is the decoded content.json.
	 * Returns slug => blueprint array.
	 */
	public static function all( $data ) {
		$pages = array();
		$order = 0;

		$pages['home']                   = array( 'title' => 'Home',                    'menu_order' => ++$order, 'elementor' => self::home( $data ) );
		$pages['philosophie']            = array( 'title' => 'Philosophie',             'menu_order' => ++$order, 'elementor' => self::philosophie() );
		$pages['leistungen']             = array( 'title' => 'Leistungen',              'menu_order' => ++$order, 'elementor' => self::leistungen() );
		$pages['rechtsberatung']         = array( 'title' => 'Rechtsberatung',          'menu_order' => ++$order, 'elementor' => self::beratungsfeld( 'rechtsberatung', 'Rechtsberatung', 'Wir stellen juristische Lösungen in den Gesamtkontext.', 'Mit unserem Beratungsangebot adressieren wir Energieversorgungsunternehmen, Bund, Länder und Kommunen sowie deren Einrichtungen und Unternehmen sowie private und öffentliche Infrastrukturbetreiber.' ) );
		$pages['steuerberatung']         = array( 'title' => 'Steuerberatung',          'menu_order' => ++$order, 'elementor' => self::beratungsfeld( 'steuerberatung', 'Steuerberatung', 'Fortlaufende Steuerberatung, Gestaltungsberatung, Neustrukturierungen.', 'Wir begleiten Ihr Unternehmen in steuerlich relevanten Fragen — von der laufenden Beratung über komplexe Gestaltungen bis zu weitreichenden Umstrukturierungen.' ) );
		$pages['unternehmensberatung']   = array( 'title' => 'Unternehmensberatung',    'menu_order' => ++$order, 'elementor' => self::beratungsfeld( 'unternehmensberatung', 'Unternehmensberatung', 'Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen.', 'Ob Unternehmensstrategie, Wirtschaftlichkeitsbetrachtungen oder Finanzierung — wir kennen die Hebel, die in einem sich wandelnden Umfeld wirken.' ) );
		$pages['team']                   = array( 'title' => 'Team',                    'menu_order' => ++$order, 'elementor' => self::team_page() );
		$pages['publikationen']          = array( 'title' => 'Publikationen',           'menu_order' => ++$order, 'elementor' => self::publikationen_page() );
		$pages['karriere']               = array( 'title' => 'Karriere',                'menu_order' => ++$order, 'elementor' => self::karriere_page() );
		$pages['news']                   = array( 'title' => 'News',                    'menu_order' => ++$order, 'elementor' => self::news_page() );
		$pages['veranstaltungen']        = array( 'title' => 'Veranstaltungen',         'menu_order' => ++$order, 'elementor' => self::veranstaltungen_page() );
		$pages['kontakt']                = array( 'title' => 'Kontakt',                 'menu_order' => ++$order, 'elementor' => self::kontakt() );
		$pages['impressum']              = array( 'title' => 'Impressum',               'menu_order' => ++$order, 'post_content' => isset( $data['legal_impressum'] ) ? $data['legal_impressum'] : '' );
		$pages['datenschutzerklaerung']  = array( 'title' => 'Datenschutzerklärung',    'menu_order' => ++$order, 'post_content' => isset( $data['legal_datenschutzerklaerung'] ) ? $data['legal_datenschutzerklaerung'] : '' );

		return $pages;
	}

	/* =========================================================================
	 * Shared section helpers
	 * ========================================================================= */

	/** Boxed content section (light background). */
	protected static function boxed( $widgets, $extra = array() ) {
		$b = 'ESC_Elementor_Builder';
		return $b::section( array( array( 'widgets' => $widgets ) ), array_merge( array(
			'layout'        => 'boxed',
			'content_width' => array( 'unit' => 'px', 'size' => 1200 ),
			'padding'       => array( 'unit' => 'px', 'top' => '90', 'right' => '24', 'bottom' => '100', 'left' => '24', 'isLinked' => false ),
		), $extra ) );
	}

	/** Soft/grey background section (for alternating tone). */
	protected static function soft( $widgets, $extra = array() ) {
		return self::boxed( $widgets, array_merge( array(
			'background_background' => 'classic',
			'background_color'      => '#f6f7f5',
		), $extra ) );
	}

	/** Dark section (e.g. CTAs). */
	protected static function dark( $widgets, $extra = array() ) {
		return self::boxed( $widgets, array_merge( array(
			'background_background' => 'classic',
			'background_color'      => '#0f1720',
		), $extra ) );
	}

	/** Multi-column section. $columns = array of widget-arrays. */
	protected static function cols( $columns, $extra = array() ) {
		$b = 'ESC_Elementor_Builder';
		$cfg = array();
		foreach ( $columns as $w ) { $cfg[] = array( 'widgets' => $w ); }
		return $b::section( $cfg, array_merge( array(
			'layout'        => 'boxed',
			'content_width' => array( 'unit' => 'px', 'size' => 1200 ),
			'padding'       => array( 'unit' => 'px', 'top' => '90', 'right' => '24', 'bottom' => '100', 'left' => '24', 'isLinked' => false ),
			'gap'           => 'extended',
		), $extra ) );
	}

	/** Eyebrow (small, green uppercase label). */
	protected static function eyebrow( $text ) {
		$b = 'ESC_Elementor_Builder';
		return $b::heading( $text, 'default', array(
			'header_size'              => 'p',
			'title_color'              => '#7bbc02',
			'typography_typography'    => 'custom',
			'typography_font_size'     => array( 'unit' => 'px', 'size' => 13 ),
			'typography_letter_spacing'=> array( 'unit' => 'px', 'size' => 2.6 ),
			'typography_text_transform'=> 'uppercase',
			'typography_font_weight'   => '600',
			'_margin'                  => array( 'unit' => 'px', 'top' => '0', 'bottom' => '14', 'left' => '0', 'right' => '0', 'isLinked' => false ),
		) );
	}

	/** Display heading (Fraunces serif). */
	protected static function display( $title, $color = '#0f1720', $size_em = 2.6, $tag = 'h2' ) {
		$b = 'ESC_Elementor_Builder';
		return $b::heading( $title, 'xl', array(
			'header_size'            => $tag,
			'title_color'            => $color,
			'typography_typography'  => 'custom',
			'typography_font_family' => 'Fraunces',
			'typography_font_weight' => '450',
			'typography_font_size'   => array( 'unit' => 'em', 'size' => $size_em ),
			'typography_line_height' => array( 'unit' => 'em', 'size' => 1.14 ),
			'_margin'                => array( 'unit' => 'px', 'top' => '0', 'bottom' => '18', 'left' => '0', 'right' => '0', 'isLinked' => false ),
		) );
	}

	/* =========================================================================
	 * Page: Home
	 * ========================================================================= */

	protected static function home( $data ) {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		// Hero
		$sections[] = $b::hero( array(
			'eyebrow' => 'Energiesozietät GmbH · Recht · Steuern · Beratung',
			'heading' => 'Expertise trifft Leidenschaft.',
			'lead'    => 'Rechts-, Steuer- und Unternehmensberatung für Energieversorger, öffentliche Hand und Infrastrukturbetreiber — interdisziplinär, pragmatisch, auf Ergebnisse ausgerichtet.',
			'buttons' => array(
				array( 'Unsere Leistungen', '/leistungen/', 'primary' ),
				array( 'Kontakt aufnehmen',  '/kontakt/',  'outline' ),
			),
		) );

		// Intro
		$sections[] = self::boxed( array(
			self::eyebrow( 'Leistungen' ),
			self::display( 'Interdisziplinäre Beratung.', '#0f1720', 2.8 ),
			$b::text( '<p>Wir denken Lösungen vom Ende her: strategische, technische, betriebs- und finanzwirtschaftliche, juristische und steuerliche Lösungen stellen wir dafür in den Gesamtkontext.</p>', array(
				'text_color'             => '#5a6270',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 19 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
			) ),
		) );

		// 3 Beratungsfelder icon-boxes
		$sections[] = self::cols( array(
			array(
				$b::icon_box( 'Rechtsberatung',         'Wir stellen juristische Lösungen in den Gesamtkontext.',                                  '/rechtsberatung/',        'fas fa-balance-scale' ),
			),
			array(
				$b::icon_box( 'Steuerberatung',         'Fortlaufende Steuerberatung, Gestaltungsberatung oder Neustrukturierungen.',              '/steuerberatung/',        'fas fa-calculator' ),
			),
			array(
				$b::icon_box( 'Unternehmensberatung',   'Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen.', '/unternehmensberatung/',  'fas fa-chart-line' ),
			),
		), array(
			'padding'               => array( 'unit' => 'px', 'top' => '30', 'right' => '24', 'bottom' => '110', 'left' => '24', 'isLinked' => false ),
		) );

		// Philosophy teaser (split: image left, copy right) — we skip image for lightweight bundle
		$sections[] = self::soft( array(
			self::eyebrow( 'Unser Beratungsansatz' ),
			self::display( 'Erfahrung und Innovation, Spezialisierung und Weitblick.', '#0f1720', 2.4 ),
			$b::text( '<p>Wir sind ein <span class="text-bg-green">junges, innovatives Beratungsunternehmen</span>, dessen langjährig erfahrene Rechtsanwälte und Steuer- und Unternehmensberater auf lange, enge, kollegiale und fruchtbare Zusammenarbeit zurückblicken.</p><p>Individuelle Beratung in komplexen Fragestellungen: fokussiert, ergebnisorientiert und kreativ.</p>', array(
				'text_color'             => '#1a1f26',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 18 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.7 ),
			) ),
			$b::button( 'Mehr zur Philosophie', '/philosophie/', 'primary' ),
		) );

		// Team teaser
		$sections[] = self::boxed( array(
			self::eyebrow( 'Team' ),
			self::display( 'Ein erfahrenes, interdisziplinäres Team.', '#0f1720', 2.4 ),
			$b::text( '<p>Die Energiesozietät gibt einem sehr erfahrenen Team einen agilen und pragmatischen Rahmen. Die Teammitglieder arbeiten seit vielen Jahren zusammen und bilden so — mit großem Vertrauen — das Fundament für unseren Beratungsansatz.</p>', array(
				'text_color'             => '#5a6270',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 18 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
				'_margin'                => array( 'unit' => 'px', 'top' => '0', 'bottom' => '30', 'left' => '0', 'right' => '0', 'isLinked' => false ),
			) ),
			$b::shortcode( '[es_team limit="8" columns="4"]' ),
			$b::spacer( 30 ),
			$b::button( 'Ganzes Team ansehen', '/team/', 'dark' ),
		) );

		// News teaser
		$sections[] = self::soft( array(
			self::eyebrow( 'Aktuelles' ),
			self::display( 'Neues aus der Energiebranche.', '#0f1720', 2.2 ),
			$b::spacer( 20 ),
			$b::shortcode( '[es_news limit="3" columns="3"]' ),
		) );

		// CTA
		$sections[] = self::dark( array(
			self::display( 'Haben wir Ihr Interesse geweckt?', '#ffffff', 2.6, 'h2' ),
			$b::text( '<p style="color:rgba(255,255,255,0.72);font-size:18px;">Wir freuen uns auf Ihre Kontaktaufnahme und beraten Sie gern unverbindlich.</p>', array() ),
			$b::button( 'Kontaktieren Sie uns', '/kontakt/', 'primary' ),
		), array(
			'padding' => array( 'unit' => 'px', 'top' => '110', 'right' => '24', 'bottom' => '120', 'left' => '24', 'isLinked' => false ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Philosophie
	 * ========================================================================= */

	protected static function philosophie() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Unsere Philosophie',
			'heading' => 'Ergebnisse, die weitertragen.',
			'lead'    => 'Wir wissen, wie komplex Ihre Aufgabe ist — und bauen unsere Beratung darauf, dass unsere Lösungen lange über die Entscheidung hinaus tragen.',
		) );

		// 4 complexity icon-boxes
		$sections[] = self::boxed( array(
			self::eyebrow( 'Ihre Herausforderungen' ),
			self::display( 'Ein extrem komplexes Umfeld.', '#0f1720', 2.4 ),
			$b::text( '<p>Entscheidungsträger und Gestalter in der öffentlichen Wirtschaft und der Energieversorgung bewegen sich in einem extrem komplexen Umfeld. Wir sind uns dieser Einflussfaktoren sehr bewusst und beziehen sie in jede unserer Lösungen mit ein.</p>', array(
				'text_color'             => '#5a6270',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 18 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
				'_margin'                => array( 'unit' => 'px', 'top' => '0', 'bottom' => '36', 'left' => '0', 'right' => '0', 'isLinked' => false ),
			) ),
		), array(
			'padding' => array( 'unit' => 'px', 'top' => '90', 'right' => '24', 'bottom' => '20', 'left' => '24', 'isLinked' => false ),
		) );

		$sections[] = self::cols( array(
			array( $b::icon_box( 'Wachsende Aufgaben',                    'Immer mehr Themen auf immer weniger Schultern.',              '', 'fas fa-layer-group' ) ),
			array( $b::icon_box( 'Regulatorische Anforderungen',          'Die regulatorische Dichte nimmt kontinuierlich zu.',          '', 'fas fa-gavel' ) ),
			array( $b::icon_box( 'Komplexe Entscheidungsprozesse',        'Mehr Stakeholder, mehr Schnittstellen, mehr Gremien.',        '', 'fas fa-sitemap' ) ),
			array( $b::icon_box( 'Multi-Stakeholder',                     'Politik, Verwaltung, Aufsichtsgremien, Öffentlichkeit.',      '', 'fas fa-users' ) ),
		), array(
			'padding' => array( 'unit' => 'px', 'top' => '10', 'right' => '24', 'bottom' => '90', 'left' => '24', 'isLinked' => false ),
		) );

		// 6 values (01..06) as accordion
		$values = array(
			array( 'title' => '01 · Leidenschaft',  'content' => 'Wir lieben, was wir tun. Diese Leidenschaft ist die Energie, die unser Team täglich antreibt und den Unterschied für unsere Mandanten macht.' ),
			array( 'title' => '02 · Boutique',      'content' => 'Wir sind eine bewusste Boutique. Kurze Wege, klare Verantwortlichkeiten und persönliche Ansprechpartner auf allen Ebenen gehören zum Kern unseres Selbstverständnisses.' ),
			array( 'title' => '03 · Flexibilität',  'content' => 'Jede Fragestellung ist eigen. Wir formen unsere Beratung pragmatisch entlang Ihrer Anforderungen — nicht umgekehrt.' ),
			array( 'title' => '04 · Expertise',     'content' => 'Juristische, steuerliche, wirtschaftliche und regulatorische Expertise aus einer Hand — gereift in unzähligen Projekten an den realen Herausforderungen der Branche.' ),
			array( 'title' => '05 · Qualität',      'content' => 'Qualität steht vor Geschwindigkeit. Wir liefern belastbare Arbeit — heute, und auch in fünf Jahren noch, wenn Ihre Entscheidung auf dem Prüfstand steht.' ),
			array( 'title' => '06 · Vertrauen',     'content' => 'Vertrauen ist die Grundlage jeder langfristigen Zusammenarbeit. Wir kommunizieren klar, halten Wort und übernehmen Verantwortung.' ),
		);
		$sections[] = self::soft( array(
			self::eyebrow( 'Was uns besonders macht' ),
			self::display( 'Was Energiesozietät auszeichnet.', '#0f1720', 2.4 ),
			$b::spacer( 30 ),
			$b::accordion( $values ),
		) );

		// CTA
		$sections[] = self::dark( array(
			self::display( 'Lernen Sie uns kennen.', '#ffffff', 2.4, 'h2' ),
			$b::text( '<p style="color:rgba(255,255,255,0.72);font-size:18px;">Ein Gespräch sagt mehr als tausend Worte.</p>', array() ),
			$b::button( 'Kontakt aufnehmen', '/kontakt/', 'primary' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Leistungen
	 * ========================================================================= */

	protected static function leistungen() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Leistungen',
			'heading' => 'Interdisziplinäre Beratung.',
			'lead'    => 'Recht, Steuern und Unternehmensberatung aus einer Hand — für Energieversorger, öffentliche Hand und Infrastrukturbetreiber.',
		) );

		$sections[] = self::boxed( array(
			$b::text( '<p>Wir denken Lösungen vom Ende her: strategische, technische, betriebs- und finanzwirtschaftliche, juristische und steuerliche Lösungen stellen wir dafür in den Gesamtkontext. Unseren Mandanten ist eines gemein — sie bewegen sich alle in einem hochkomplexen und sich kontinuierlich weiterentwickelnden Umfeld.</p>', array(
				'text_color'             => '#1a1f26',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 20 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
			) ),
		) );

		// 3 Beratungsfelder icon_boxes → ganze Seiten
		$sections[] = self::cols( array(
			array(
				$b::icon_box( 'Rechtsberatung',       'Wir stellen juristische Lösungen in den Gesamtkontext.',                                  '/rechtsberatung/',       'fas fa-balance-scale' ),
			),
			array(
				$b::icon_box( 'Steuerberatung',       'Fortlaufende Steuerberatung, Gestaltungsberatung oder Neustrukturierungen.',              '/steuerberatung/',       'fas fa-calculator' ),
			),
			array(
				$b::icon_box( 'Unternehmensberatung', 'Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen.', '/unternehmensberatung/', 'fas fa-chart-line' ),
			),
		) );

		// Alle Einzelleistungen im Überblick
		$sections[] = self::soft( array(
			self::eyebrow( 'Alle Einzelleistungen' ),
			self::display( 'Unsere Beratungsschwerpunkte.', '#0f1720', 2.2 ),
			$b::spacer( 20 ),
			$b::shortcode( '[es_einzelleistungen columns="3"]' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Pages: Rechts-, Steuer- und Unternehmensberatung (shared template)
	 * ========================================================================= */

	protected static function beratungsfeld( $slug, $title, $lead, $long_intro ) {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Beratungsfeld',
			'heading' => $title,
			'lead'    => $lead,
		) );

		$sections[] = self::boxed( array(
			$b::text( '<p>' . $long_intro . '</p>', array(
				'text_color'             => '#1a1f26',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 20 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
			) ),
		) );

		$sections[] = self::soft( array(
			self::eyebrow( 'Einzelleistungen' ),
			self::display( 'Unsere Schwerpunkte in ' . $title . '.', '#0f1720', 2.2 ),
			$b::spacer( 20 ),
			$b::shortcode( '[es_einzelleistungen beratungsfeld="' . $slug . '" columns="3"]' ),
		) );

		$sections[] = self::dark( array(
			self::display( 'Ihre Fragestellung — unsere Expertise.', '#ffffff', 2.2, 'h2' ),
			$b::text( '<p style="color:rgba(255,255,255,0.72);font-size:18px;">Wir freuen uns auf Ihre Anfrage.</p>', array() ),
			$b::button( 'Kontakt aufnehmen', '/kontakt/', 'primary' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Team
	 * ========================================================================= */

	protected static function team_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Team',
			'heading' => 'Ein erfahrenes, interdisziplinäres Team.',
			'lead'    => 'Die Energiesozietät gibt einem sehr erfahrenen Team einen agilen und pragmatischen Rahmen.',
		) );

		$sections[] = self::boxed( array(
			$b::text( '<p>Die Teammitglieder arbeiten seit vielen Jahren zusammen und bilden so — mit großem Vertrauen — das Fundament für unseren Beratungsansatz. Jedes Teammitglied zeichnet sich durch besondere Fähigkeiten aus; gemeinsam entsteht daraus eine interdisziplinäre Stärke, die unseren Mandanten zugutekommt.</p>', array(
				'text_color'             => '#1a1f26',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 20 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
			) ),
			$b::spacer( 30 ),
			$b::shortcode( '[es_team columns="4"]' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Publikationen
	 * ========================================================================= */

	protected static function publikationen_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Publikationen',
			'heading' => 'Unsere Veröffentlichungen.',
			'lead'    => 'Energie- und Kommunalwirtschaft befinden sich in einem fortwährenden Wandel — wir lernen mit und teilen unser Wissen.',
		) );

		$sections[] = self::boxed( array(
			$b::text( '<p>Manche unserer neuen Erkenntnisse möchten wir gerne teilen. Die nachstehend aufgelisteten Publikationen sind darum auch als Anregung gedacht, mit uns in den Austausch zu treten.</p>', array(
				'text_color'             => '#1a1f26',
				'typography_typography'  => 'custom',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 20 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.6 ),
			) ),
			$b::spacer( 30 ),
			$b::shortcode( '[es_publikationen columns="2"]' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Karriere
	 * ========================================================================= */

	protected static function karriere_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Karriere',
			'heading' => 'Starte gemeinsam mit uns durch!',
			'lead'    => 'Entfalte Dich selbst in einem jungen, schnell wachsenden Beratungsunternehmen.',
		) );

		$sections[] = self::boxed( array(
			self::eyebrow( 'Warum Energiesozietät' ),
			self::display( 'Was wir Dir bieten.', '#0f1720', 2.2 ),
			$b::icon_list( array(
				'Ein Team auf Augenhöhe, in dem Dein Beitrag zählt.',
				'Anspruchsvolle Mandate aus Energie, öffentlicher Hand und Infrastruktur.',
				'Flexible Arbeitsmodelle und echte Eigenverantwortung.',
				'Strukturierte Weiterentwicklung — juristisch, steuerlich, wirtschaftlich.',
				'Ein modernes Büro im Herzen Düsseldorfs.',
			) ),
		) );

		$sections[] = self::soft( array(
			self::eyebrow( 'Offene Stellen' ),
			self::display( 'Unsere aktuellen Stellenangebote.', '#0f1720', 2.2 ),
			$b::spacer( 20 ),
			$b::shortcode( '[es_karriere columns="3"]' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: News
	 * ========================================================================= */

	protected static function news_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'News',
			'heading' => 'Aktuelles aus der Kanzlei.',
			'lead'    => 'Finden Sie relevante Branchenentwicklungen und Einblicke in unsere Arbeit.',
		) );

		$sections[] = self::boxed( array(
			$b::shortcode( '[es_news limit="-1" columns="3"]' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Veranstaltungen
	 * ========================================================================= */

	protected static function veranstaltungen_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Veranstaltungen',
			'heading' => 'Aktuelle Termine.',
			'lead'    => 'Wir laden Sie regelmäßig zu spannenden und für Sie relevanten Veranstaltungen ein.',
		) );

		$sections[] = self::boxed( array(
			$b::shortcode( '[es_veranstaltungen columns="3"]' ),
		) );

		return $sections;
	}

	/* =========================================================================
	 * Page: Kontakt
	 * ========================================================================= */

	protected static function kontakt() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero( array(
			'eyebrow' => 'Kontakt',
			'heading' => 'Bitte zögern Sie nicht, uns anzusprechen.',
			'lead'    => 'Wir freuen uns auf Ihre Kontaktaufnahme.',
		) );

		// 2 columns: contact details | form placeholder
		$sections[] = self::cols( array(
			array(
				self::eyebrow( 'Hauptsitz' ),
				self::display( 'Düsseldorf.', '#0f1720', 2.2, 'h3' ),
				$b::text( '<p><strong>Energiesozietät GmbH</strong><br>Roßstraße 92 · Kennedyhaus<br>40476 Düsseldorf</p><p>Telefon: <a href="tel:+492111592320">+49 211 159232-0</a><br>Fax: +49 211 159232-99<br>E-Mail: <a href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a></p>', array() ),
			),
			array(
				self::eyebrow( 'Weitere Standorte' ),
				self::display( 'Hamburg · Mannheim.', '#0f1720', 2.2, 'h3' ),
				$b::text( '<p><strong>Hamburg</strong><br>Caffamacherreihe 8<br>20355 Hamburg</p><p><strong>Mannheim</strong><br>Harrlachweg 4<br>68163 Mannheim</p>', array() ),
			),
		) );

		$sections[] = self::soft( array(
			self::eyebrow( 'Nachricht schreiben' ),
			self::display( 'Nutzen Sie unser Kontaktformular.', '#0f1720', 2.2 ),
			$b::text( '<p>Schildern Sie uns kurz Ihr Anliegen — wir melden uns binnen eines Werktages.</p><p><em>Hinweis: Tragen Sie hier ein Formular-Plugin Ihrer Wahl (z.&nbsp;B. Contact Form 7) ein und platzieren Sie den entsprechenden Shortcode.</em></p>', array() ),
		) );

		return $sections;
	}
}
