<?php
/**
 * Page blueprints — Elementor-JSON für jede Top-Level-Seite.
 * Layouts folgen dem Design-Mockup "ES.Website.3" (handoff/blocks.md +
 * handoff/templates.md). Jede Methode erzeugt die sections-Liste für ein Slug.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Page_Blueprints {

	public static function all( $data ) {
		$pages = array();
		$order = 0;
		$pages['home']                   = array( 'title' => 'Home',                 'menu_order' => ++$order, 'elementor' => self::home( $data ) );
		$pages['philosophie']            = array( 'title' => 'Philosophie',          'menu_order' => ++$order, 'elementor' => self::philosophie() );
		$pages['leistungen']             = array( 'title' => 'Leistungen',           'menu_order' => ++$order, 'elementor' => self::leistungen( $data ) );
		$pages['rechtsberatung']         = array( 'title' => 'Rechtsberatung',       'menu_order' => ++$order, 'elementor' => self::beratungsfeld_detail( 'rechtsberatung', $data ) );
		$pages['steuerberatung']         = array( 'title' => 'Steuerberatung',       'menu_order' => ++$order, 'elementor' => self::beratungsfeld_detail( 'steuerberatung', $data ) );
		$pages['unternehmensberatung']   = array( 'title' => 'Unternehmensberatung', 'menu_order' => ++$order, 'elementor' => self::beratungsfeld_detail( 'unternehmensberatung', $data ) );
		$pages['team']                   = array( 'title' => 'Team',                 'menu_order' => ++$order, 'elementor' => self::team_page() );
		$pages['publikationen']          = array( 'title' => 'Publikationen',        'menu_order' => ++$order, 'elementor' => self::publikationen_page() );
		$pages['karriere']               = array( 'title' => 'Karriere',             'menu_order' => ++$order, 'elementor' => self::karriere_page() );
		$pages['news']                   = array( 'title' => 'News',                 'menu_order' => ++$order, 'elementor' => self::news_page() );
		$pages['veranstaltungen']        = array( 'title' => 'Veranstaltungen',      'menu_order' => ++$order, 'elementor' => self::veranstaltungen_page() );
		$pages['kontakt']                = array( 'title' => 'Kontakt',              'menu_order' => ++$order, 'elementor' => self::kontakt_page() );
		$pages['impressum']              = array( 'title' => 'Impressum',            'menu_order' => ++$order, 'post_content' => isset( $data['legal_impressum'] ) ? $data['legal_impressum'] : '' );
		$pages['datenschutzerklaerung']  = array( 'title' => 'Datenschutzerklärung', 'menu_order' => ++$order, 'post_content' => isset( $data['legal_datenschutzerklaerung'] ) ? $data['legal_datenschutzerklaerung'] : '' );
		return $pages;
	}

	/* ==========================================================================
	 * Hilfsdaten — Beratungsfelder, Ansprechpartner, Topic-Texte aus Mockup.
	 * ========================================================================== */

	protected static function beratungsfelder() {
		return array(
			'rechtsberatung' => array(
				'n' => '01',
				'title' => 'Rechtsberatung',
				'sub' => 'Juristische Lösungen im Gesamtkontext',
				'lede' => 'Wir stellen juristische Lösungen in den Gesamtkontext. Mit einem ausgeprägten Verständnis von Energiemärkten, Verwaltungsorganisationen und politischen Entscheidungsprozessen.',
				'ansprechpartner' => array(
					array( 'prof-dr-sven-joachim-otto', 'Prof. Dr. Sven-Joachim Otto', 'Partner · Rechtsanwalt' ),
					array( 'dr-bernhardine-kleinhenz-jeannot', 'Dr. Bernhardine Kleinhenz-Jeannot', 'Partnerin · Rechtsanwältin' ),
				),
				'long_title' => 'Wir stellen juristische Lösungen in den Gesamtkontext — mit Blick auf das wirtschaftliche Ziel.',
				'long_copy' => array(
					'Wir haben ein ausgeprägtes Verständnis von Energiemärkten, Verwaltungsorganisationen und ihren politischen Entscheidungsprozessen. Politikberatung ist integraler Bestandteil unserer Tätigkeit, um die Projekte unserer Mandanten zum Erfolg zu führen.',
					'Energiewirtschaftsrechtliche Fragestellungen stehen selten für sich allein — sie sind eingebettet in unternehmerische, steuerliche und regulatorische Zusammenhänge. Wir beraten interdisziplinär, Hand in Hand mit unseren Steuer- und Unternehmensberatern.',
				),
				'topics' => array(
					array( 'Energie', 'Transformation unserer Energieversorgung — eine gesamtgesellschaftliche Aufgabe.' ),
					array( 'Kommunalwirtschaft', 'Wirtschaftliche Betätigung von Kommunen unter besonderen Spielregeln.' ),
					array( 'Vergaberecht', 'Anspruchsvolle rechtliche Anforderungen an öffentliche Beschaffung.' ),
					array( 'Gesellschaftsrecht', 'Gesellschaftsrechtlicher Rahmen für strukturelle Veränderungen.' ),
					array( 'Bau- und Planungsrecht', 'Komplexe Vorhaben sind unsere Stärke.' ),
					array( 'Umweltrecht', 'Rechtsberatung für eine nachhaltige und innovative Zukunft.' ),
				),
			),
			'steuerberatung' => array(
				'n' => '02',
				'title' => 'Steuerberatung',
				'sub' => 'Fortlaufend · Gestaltend · Strukturierend',
				'lede' => 'Fortlaufende Steuerberatung, Gestaltungsberatung oder herausfordernde Neustrukturierungen — mit besonderem Know-how in der Betreuung der öffentlichen Hand und ihrer Unternehmen.',
				'ansprechpartner' => array(
					array( 'elke-beermann', 'Elke Beermann', 'Partnerin · Steuerberaterin' ),
					array( 'christopher-siebler', 'Christopher Siebler', 'Associated Partner · Steuerberater' ),
				),
				'long_title' => 'Besonderes Know-how in der Betreuung der öffentlichen Hand — erfolgreich eingesetzt auch in der Privatwirtschaft.',
				'long_copy' => array(
					'Wir kennen die steuerlichen Besonderheiten von Eigenbetrieben, Beteiligungsgesellschaften und juristischen Personen des öffentlichen Rechts. Dieses Wissen setzen wir ebenso erfolgreich in der Privatwirtschaft ein.',
					'Von der laufenden Steuerberatung über Deklarationen bis hin zu strukturellen Neustrukturierungen — wir denken steuerliche Lösungen im Gesamtkontext unserer Mandanten.',
				),
				'topics' => array(
					array( 'Gründungs- und Strukturberatung', 'Neues schaffen, Bestehendes grundlegend verändern.' ),
					array( 'Steuererklärungen & Deklaration', 'Ein Thema, mit dem man sich fortlaufend befassen muss.' ),
					array( 'Strom- und Energiesteuern', 'Hohe administrative Aufgaben richtig managen.' ),
					array( 'Tax Compliance Management', 'Steuerliche Risiken strukturiert managen.' ),
					array( 'Finanzbuchhaltung', 'Ordnungsgemäß, digital, effizient.' ),
					array( 'Lohn- und Gehaltsbuchhaltung', 'Komplexität im Griff, Compliance sichern.' ),
				),
			),
			'unternehmensberatung' => array(
				'n' => '03',
				'title' => 'Unternehmensberatung',
				'sub' => 'Strategie · Wirtschaft · Finanzen',
				'lede' => 'Wir navigieren Sie durch die Anforderungen neuer Energieträger und Erzeugungsarten sowie die damit verbundenen wirtschaftlichen und finanziellen Fragestellungen.',
				'ansprechpartner' => array(
					array( 'torsten-stockem', 'Torsten Stockem', 'Partner · Unternehmensberatung' ),
					array( 'hartmut-muller', 'Hartmut Müller', 'Senior Advisor' ),
				),
				'long_title' => 'Investier- und Finanzierbarkeit von Transformation erreichen.',
				'long_copy' => array(
					'In der Energiebranche erzeugt die Erfordernis, den Klimazielen gerecht zu werden, enormen Druck. Traditionellen Geschäftsmodellen werden Grenzen aufgezeigt. Neue Energieträger, innovative Erzeugungstechnologien und leistungsfähige Netze sollen die Energieversorgung von morgen sicherstellen.',
					'Unsere Experten begleiten Sie von Beginn an — als Sparringspartner, bei der Entwicklung von Strategieoptionen, deren Konkretisierung in Business Plänen, bei der Finanzierung und der Suche nach strategischen Partnern für die Umsetzung.',
				),
				'topics' => array(
					array( 'Strategie & Geschäftsmodelle', 'Zukunftsfähige Modelle für eine Branche im Wandel.' ),
					array( 'Wirtschaftliche Bewertung', 'Infrastruktur-Investitionen belastbar bewerten.' ),
					array( 'Finanzierung & Förderung', 'Finanzierungsstrukturen und Fördermittel zielgenau nutzen.' ),
					array( 'Organisationsentwicklung', 'Prozesse, Rollen und Governance für neue Anforderungen.' ),
					array( 'Transformationsbegleitung', 'Projekte von der Idee bis zur Umsetzung.' ),
					array( 'Post-Merger-Integration', 'Zusammenführung nach Transaktionen — operativ wie kulturell.' ),
					array( 'Projektmanagement', 'Transformation anstoßen, entwickeln und umsetzen — Projekte, die ankommen.' ),
				),
			),
		);
	}

	/* ==========================================================================
	 * Home
	 * ========================================================================== */
	/* ========================== Home ========================== */
	protected static function home( $data ) {
		$b = 'ESC_Elementor_Builder';
		$s = array();

		// 1. Hero
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Recht · Steuern · Beratung',
			'headline_html' => 'Expertise trifft <span class="text-bg-green">Leidenschaft.</span>',
			'lead' => '<p>Wir sind ein <em>junges, innovatives Beratungsunternehmen</em>, dessen langjährig erfahrene Rechtsanwälte, Steuer- und Unternehmensberater sich der <em>Transformation der öffentlichen Hand und der Energiewirtschaft</em> verschrieben haben. Wir arbeiten hoch spezialisiert, fachübergreifend und fokussiert an den Themen unserer Zeit.</p>',
			'claims' => array(
				array( '20+', 'Berufsträger & Mitarbeiter' ),
				array( '3', 'Beratungsfelder' ),
				array( '2023', 'Gegründet' ),
				array( '3', 'Standorte · DUS · HH · MA' ),
			),
			'padding' => 'tall',
			// Rechte Spalte: die zwei Stat-Cards ersetzen die früheren Buttons
			// und sind die interaktiven Elemente (Team / Leistungen).
			'side_html' =>
				'<div class="es-hero-stats">'
				. '<a class="es-hero-stat es-hero-stat--dark" href="/team/">'
				.   '<span class="es-hero-stat__top"><span class="es-hero-stat__label">Team</span><span class="es-hero-stat__arrow" aria-hidden="true">↗</span></span>'
				.   '<span class="es-hero-stat__num">20+</span><span class="es-hero-stat__sub">Beschäftigte</span></a>'
				. '<a class="es-hero-stat es-hero-stat--light" href="/leistungen/">'
				.   '<span class="es-hero-stat__top"><span class="es-hero-stat__label">Leistungen</span><span class="es-hero-stat__arrow" aria-hidden="true">↗</span></span>'
				.   '<span class="es-hero-stat__num">30+</span><span class="es-hero-stat__sub">Fachgebiete</span></a>'
				. '</div>',
		) );

		// 2. Intro (Split Text warm) — H2 als eigentliche Überschrift, Eyebrow klein
		$s[] = $b::split_native( array(
			'eyebrow' => 'Unser Anspruch',
			'title_html' => 'Hoch spezialisiert.<br>Fachübergreifend.<br><span class="text-bg-green">Ergebnisorientiert.</span>',
			'paragraphs' => array(
				'<p>Wir arbeiten hoch spezialisiert, fachübergreifend und fokussiert an den Themen unserer Zeit — unaufgeregt, ergebnisorientiert und mit individuellen Persönlichkeiten.</p>',
				'<p><a class="es-link" href="/philosophie/">Unsere Philosophie</a> · <a class="es-link" href="/team/">Unser Team</a></p>',
			),
			'variant' => 'warm',
			'padding' => 'short',
		) );

		// 3. Leistungen-3-Cards (Heading+Text+Link in 3 Columns)
		$s[] = $b::section_native( array(
			'variant' => '',
			'css_classes' => 'es-home-services-head',
			'padding' => array( '120', '0', '40', '0' ),
			'cols' => array(
				array(
					$b::wid_heading( 'Leistungen', 'p', 'es-eyebrow' ),
					$b::wid_heading( 'Interdisziplinäre Beratung.<br><span style="color:#899092;">Drei Felder, ein Gedanke.</span>', 'h2', 'es-home-services-head__title' ),
					$b::wid_text( '<p>Wir denken Lösungen vom Ende her: strategische, betriebswirtschaftliche, juristische und steuerliche Themen stellen wir in den Gesamtkontext.</p>' ),
				),
			),
		) );
		// 3 Service-Cards in 3 columns
		$s[] = $b::section_native( array(
			'variant' => '',
			'css_classes' => 'es-home-services',
			'padding' => array( '0', '0', '120', '0' ),
			'column_classes' => 'es-service-card',
			'cols' => array(
				array(
					$b::wid_html( '<div class="es-service-card__head"><span class="es-service-card__num">01 / 03</span><span class="es-service-card__dot"></span></div>' ),
					$b::wid_heading( 'Rechtsberatung', 'h3', 'es-service-card__title' ),
					$b::wid_text( '<p>Energierecht, Vergaberecht, Regulierung, M&A, Gesellschaftsrecht. Wir stellen juristische Lösungen in den Gesamtkontext.</p>', 'es-service-card__body' ),
					$b::wid_html( '<a class="es-link" href="/rechtsberatung/">Mehr erfahren →</a>' ),
				),
				array(
					$b::wid_html( '<div class="es-service-card__head"><span class="es-service-card__num">02 / 03</span><span class="es-service-card__dot"></span></div>' ),
					$b::wid_heading( 'Steuerberatung', 'h3', 'es-service-card__title' ),
					$b::wid_text( '<p>Fortlaufende Steuerberatung, Gestaltungsberatung und herausfordernde Neustrukturierungen für Versorger und Kommunen.</p>', 'es-service-card__body' ),
					$b::wid_html( '<a class="es-link" href="/steuerberatung/">Mehr erfahren →</a>' ),
				),
				array(
					$b::wid_html( '<div class="es-service-card__head"><span class="es-service-card__num">03 / 03</span><span class="es-service-card__dot"></span></div>' ),
					$b::wid_heading( 'Unternehmensberatung', 'h3', 'es-service-card__title' ),
					$b::wid_text( '<p>Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen der Transformation.</p>', 'es-service-card__body' ),
					$b::wid_html( '<a class="es-link" href="/unternehmensberatung/">Mehr erfahren →</a>' ),
				),
			),
		) );

		// 4. GF Quote (bleibt als gf_quote-Helper — enthält bereits Image+Blockquote)
		$s[] = $b::gf_quote( array(
			'quote' => 'Wir wollen mit unserer langjährigen Beratungs- und Netzwerkerfahrung unsere Mandantschaft bei den anstehenden Transformationen begleiten — und mit den individuellen Persönlichkeiten unseres Teams Mehrwert bieten.',
		) );

		// 5. News Teaser (shortcode widget)
		$s[] = $b::section_native( array(
			'css_classes' => 'es-home-news',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Aktuelles · Neu', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Fachbeiträge.', 'h2', 'es-home-news__title' ),
				$b::wid_shortcode( '[es_news limit="3" columns="3"]' ),
				$b::wid_html( '<p style="margin-top:32px;"><a class="es-link" href="/news/">Alle Beiträge ansehen →</a></p>' ),
			) ),
		) );

		// 6. LinkedIn-Feed (warmer Streifen), passt visuell ins News-Raster
		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'css_classes' => 'es-home-linkedin',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				$b::wid_shortcode( '[es_linkedin_posts limit="3" title="Aus unserem LinkedIn" eyebrow="LinkedIn"]' ),
			) ),
		) );

		// 7. CTA
		return $s;
	}


	/* ==========================================================================
	 * Philosophie
	 * ========================================================================== */
	/* ========================== Philosophie ========================== */
	protected static function philosophie() {
		$b = 'ESC_Elementor_Builder';
		$s = array();

		$s[] = $b::hero_native( array(
			'eyebrow' => 'Philosophie',
			'headline_html' => 'Transformation ist eine<br><span class="text-bg-green">Mammutaufgabe.</span>',
			'lead' => '<p>Dafür braucht es viele. Das schafft niemand allein. Wir — das Team der Energiesozietät — verstehen uns als einer der vielen.</p>',
			'padding' => 'tall',
		) );

		// Split: "Wie wir arbeiten — Fokussiert/Ergebnisorientiert/Kreativ"
		$s[] = $b::split_native( array(
			'eyebrow' => 'Wie wir arbeiten',
			'title_html' => 'Fokussiert.<br>Ergebnis&shy;orientiert.<br><span class="text-bg-green">Kreativ.</span>',
			'paragraphs' => array(
				'<p>Wir sind Experten. Wir arbeiten hoch spezialisiert. Wir entwickeln ganzheitliche Lösungen, die strategische, betriebs- und finanzwirtschaftliche, juristische sowie steuerliche Fragen immer im Gesamtkontext betrachten.</p>',
				'<p>Das erfordert interdisziplinäres Denken, langjährige Erfahrung und die Bereitschaft, neu zu denken — gerade dort, wo sich Energiemärkte, regulatorische Rahmen und politische Entscheidungsprozesse gleichzeitig verändern.</p>',
			),
			'variant' => 'warm',
			'padding' => 'default',
		) );

		// 3 Pillar Cards
		$s[] = $b::cards_native( array(
			array( '01', 'Fokussiert',          'Wir arbeiten hoch spezialisiert an den Themen unserer Zeit. Kein Bauchladen, sondern vertiefte Expertise dort, wo die Transformation entschieden wird.' ),
			array( '02', 'Ergebnisorientiert',  'Wir denken Lösungen vom Ende her. Strategische, technische, betriebs- und finanzwirtschaftliche, juristische sowie steuerliche Lösungen stellen wir in den Gesamtkontext.' ),
			array( '03', 'Kreativ',             'Neue Fragestellungen verlangen neue Antworten. Wir verbinden langjährige Erfahrung aus Big-Four-Gesellschaften, spezialisierten Kanzleien und der Industrie mit einem agilen, pragmatischen Arbeitsrahmen.' ),
		), 'warm' );

		// Pullquote (bleibt als gf-Helper gerendert im Panel)
		$s[] = $b::pullquote(
			'„Unsere Mandanten bewegen sich in einem <span class="text-bg-green">hochkomplexen</span> und <span class="text-bg-green">sich kontinuierlich wandelnden</span> Umfeld. In diesem Umfeld treffen sie Entscheidungen, die ihre Unternehmen und Kommunen über Jahrzehnte prägen werden."',
			'Unsere Überzeugung'
		);

		// 4 Perspektiven
		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'padding' => array( '120', '0', '40', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Interdisziplinär', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Ein Team aus vier Perspektiven.', 'h2', 'es-split__title' ),
				$b::wid_text( '<p>Ingenieure, Kaufleute, Rechtsanwälte und Steuerberater erarbeiten mit unseren Mandanten ganzheitliche, tragfähige Lösungen als Grundlage für gut abgewogene Entscheidungen.</p>' ),
			) ),
		) );
		$s[] = $b::cards_native( array(
			array( '', 'Ingenieure',    'Technische Lösungen und Machbarkeit im Energiesystem.' ),
			array( '', 'Kaufleute',     'Betriebs- und finanzwirtschaftliche Bewertung und Strukturierung.' ),
			array( '', 'Rechtsanwälte', 'Juristische Gestaltung im regulatorischen Gesamtkontext.' ),
			array( '', 'Steuerberater', 'Steuerliche Struktur, Compliance und Gestaltungsspielräume.' ),
		), 'warm', 'es-cards-grid--quads' );

		// Mandantschaft — Liste als Shortcode, im Elementor-Editor anpassbar
		$s[] = $b::split_native( array(
			'eyebrow' => 'Mandantschaft',
			'title_html' => 'Wen wir beraten.',
			'paragraphs' => array(
				'<p>Mit unserem Beratungsangebot adressieren wir Energieversorgungsunternehmen, Bund, Länder und Kommunen sowie deren Einrichtungen und Unternehmen. Hinzu kommen private und öffentliche Infrastrukturdienstleister und Investoren.</p>',
			),
			'extra_after' => array(
				$b::wid_shortcode( '[es_mandanten items="Energieversorgungsunternehmen|Stadtwerke &amp; kommunale Unternehmen|Bund &amp; Länder|Kommunen &amp; Einrichtungen der öffentlichen Hand|Infrastrukturdienstleister|Investoren"]' ),
			),
			'padding' => 'default',
		) );

		// Politikberatung (Split ink)
		$s[] = $b::split_native( array(
			'eyebrow' => 'Politikberatung',
			'title_html' => 'Lösungen, die im<br>politischen Prozess<br><span class="text-bg-green">Akzeptanz finden.</span>',
			'paragraphs' => array(
				'<p>Wir haben ein ausgeprägtes Verständnis von Energiemärkten, Verwaltungsorganisationen und ihren politischen Entscheidungsprozessen. Politikberatung ist integraler Bestandteil unserer Tätigkeit, um die Projekte unserer Mandanten zum Erfolg zu führen.</p>',
				'<p>Wir entwickeln Konzepte für die Kommunikation von Lösungen im politischen Entscheidungsprozess — mit dem Ziel, bestmögliche Lösungen zu präsentieren, die eine breite Akzeptanz finden.</p>',
			),
			'variant' => 'ink',
			'padding' => 'default',
		) );
		return $s;
	}


	/* ==========================================================================
	 * Leistungen (Übersicht) + Beratungsfeld-Detail
	 * ========================================================================== */
	/* ========================== Leistungen (Übersicht) + Beratungsfeld-Detail ========================== */
	protected static function leistungen( $data ) {
		$b = 'ESC_Elementor_Builder';
		$s = array();

		$s[] = $b::hero_native( array(
			'eyebrow' => 'Leistungen',
			'headline_html' => 'Interdisziplinäre<br><span class="text-bg-green">Beratung.</span>',
			'lead' => '<p>Wir denken Lösungen vom Ende her: strategische, technische, betriebs- und finanzwirtschaftliche, juristische und steuerliche Lösungen stellen wir dafür in den Gesamtkontext.</p>',
			'padding' => 'short',
		) );

		$s[] = $b::split_native( array(
			'eyebrow' => 'Unser Anspruch',
			'title_html' => 'Experten für Ihre Beratung.',
			'paragraphs' => array(
				'<p>Die Energiesozietät ist seit ihrer Gründung Ende 2023 dynamisch gewachsen und verfügt heute über ein Team mit großem Erfahrungsschatz. Viele Teammitglieder haben jahrelang erfolgreich in Big-Four-Gesellschaften, spezialisierten Beratungsgesellschaften und Kanzleien gearbeitet.</p>',
				'<p>Sollten wir selbst einmal nicht die geeigneten Spezialisten im Team haben, um Ihre Fragen bestmöglich zu beantworten, kooperieren wir mit namhaften Beratungsgesellschaften, mit denen wir seit vielen Jahren vertrauensvoll zusammenarbeiten.</p>',
			),
			'variant' => 'warm',
			'padding' => 'short',
		) );

		// 3 Bereichs-Blöcke mit dynamischen Einzelleistungen (auto-Permalink-Links)
		$bf = self::beratungsfelder();
		$titles_html = array(
			'rechtsberatung'       => 'Rechts-<br>beratung',
			'steuerberatung'       => 'Steuer-<br>beratung',
			'unternehmensberatung' => 'Unternehmens-<br>beratung',
		);
		$order = array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' );
		$count = count( $order );
		foreach ( $order as $i => $slug ) {
			$cfg = $bf[ $slug ];
			// Farbe der FOLGENDEN Sektion für die Ecken-Überlappung: nächster
			// Block (odd=weiß/paper, even=warm) bzw. Footer (ink) beim letzten.
			if ( $i + 1 < $count ) {
				$next_bg = ( ( $i + 1 ) % 2 === 0 ) ? 'paper' : 'warm';
			} else {
				$next_bg = 'ink';
			}
			$s[] = $b::bereich( array(
				'n' => $cfg['n'],
				'title' => $cfg['title'],
				'title_html' => $titles_html[ $slug ],
				'sub' => $cfg['sub'],
				'lede' => $cfg['lede'],
				'link' => '/' . $slug . '/',
				'field' => $slug,        // dynamische Einzelleistungen, Links auf Permalink
				'topics' => array(),     // nicht hartcodiert — wird zur Laufzeit geholt
				'stripe' => ( $i % 2 === 0 ) ? 'odd' : 'even',
				'next_bg' => $next_bg,
			) );
		}
		return $s;
	}

	protected static function beratungsfeld_detail( $slug, $data ) {
		$b = 'ESC_Elementor_Builder';
		$bf = self::beratungsfelder();
		$d = $bf[ $slug ];
		$s = array();

		// Hero mit Breadcrumb (H1 + Lead)
		$crumb_html = '<div class="es-article__crumb" style="margin-bottom:28px;"><a href="/leistungen/" style="color:inherit;">Leistungen</a>  /  ' . esc_html( $d['title'] ) . '</div>';
		$s[] = $b::section_native( array(
			'variant' => 'ink',
			'css_classes' => 'es-hero',
			'padding' => array( '80', '0', '110', '0' ),
			'cols' => array( array(
				$b::wid_html( $crumb_html ),
				$b::wid_heading( $d['n'] . ' · ' . $d['title'], 'p', 'es-eyebrow es-eyebrow--accent' ),
				$b::wid_heading( $d['long_title'], 'h1', 'es-hero__title' ),
				$b::wid_text( '<p>' . esc_html( $d['lede'] ) . '</p>', 'es-hero__lead' ),
			) ),
		) );

		// Ansprechpartner-Balken (Shortcode → im Elementor-Editor via
		// Shortcode-Widget editierbar: members="slug1,slug2")
		$ansprech_slugs = array();
		foreach ( $d['ansprechpartner'] as $p ) { $ansprech_slugs[] = $p[0]; }
		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'padding' => array( '28', '0', '28', '0' ),
			'cols' => array( array(
				$b::wid_shortcode( '[es_ansprechpartner members="' . esc_attr( implode( ',', $ansprech_slugs ) ) . '" cta_url="/kontakt/" cta_label="Termin anfragen"]' ),
			) ),
		) );

		// Content Split — Section mit 2 Columns (40/60), native Widgets links
		// damit der Text in Elementor direkt editierbar ist. Mobile-Umbruch
		// auf 1 Column 100% per CSS-Override mit hoher Spezifitaet.
		$s[] = $b::section_native( array(
			'css_classes' => 'es-bereich-detail__split-section',
			'padding' => array( '120', '0', '120', '0' ),
			'gap' => 'wider',
			'column_settings' => array(
				array( '_column_size' => 40, '_inline_size_tablet' => 100, '_inline_size_mobile' => 100 ),
				array( '_column_size' => 60, '_inline_size_tablet' => 100, '_inline_size_mobile' => 100 ),
			),
			'cols' => array(
				array(
					$b::wid_heading( 'Was wir für Sie tun', 'p', 'es-eyebrow' ),
					$b::wid_heading( $d['long_title'], 'h2', 'es-split__title' ),
					$b::wid_text( '<p>' . esc_html( $d['long_copy'][0] ) . '</p>' ),
					$b::wid_text( '<p>' . esc_html( $d['long_copy'][1] ) . '</p>' ),
				),
				array(
					$b::wid_heading( 'Beratungsfelder', 'p', 'es-eyebrow' ),
					$b::wid_shortcode( '[es_einzelleistungen beratungsfeld="' . esc_attr( $slug ) . '" columns="2"]', 'es-bereich-detail__split-tiles' ),
				),
			),
		) );

		// Publikationen & Fachbeiträge — 3 neueste getaggt mit Beratungsfeld
		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Aus diesem Feld', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Publikationen & Fachbeiträge', 'h2', 'es-home-news__title' ),
				$b::wid_shortcode( '[es_pub_teaser field="' . esc_attr( $slug ) . '" limit="3"]' ),
				$b::wid_html( '<p style="margin-top:32px;"><a class="es-link" href="/publikationen/?feld=' . esc_attr( $slug ) . '">Alle Publikationen →</a></p>' ),
			) ),
		) );
		return $s;
	}

	/* ========================== Team ========================== */
	protected static function team_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Team',
			'headline_html' => 'Beratung mit<br>Gesicht.',
			'lead' => '<p>In der Energiesozietät hat jahrzehntelange Erfahrung einen agilen und pragmatischen Rahmen bekommen.</p>',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'padding' => array( '40', '0', '80', '0' ),
			'cols' => array( array(
				$b::wid_shortcode( '[es_team columns="2" filter="1"]' ),
			) ),
		) );
		$s[] = $b::split_native( array(
			'eyebrow' => 'Netzwerk',
			'title_html' => 'Kooperationen mit namhaften Beratungshäusern.',
			'paragraphs' => array(
				'<p>Sollten wir selbst einmal nicht die geeigneten Spezialisten im Team haben, kooperieren wir mit namhaften Beratungsgesellschaften, mit denen wir seit vielen Jahren vertrauensvoll zusammenarbeiten. Sprechen Sie uns gerne direkt an.</p>',
			),
			'variant' => 'warm', 'padding' => 'short',
		) );
		return $s;
	}

	/* ========================== Publikationen ========================== */
	protected static function publikationen_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Publikationen',
			'headline_html' => 'Bücher,<br>Kommentare<br><span style="color:rgba(255,255,255,0.55);">&amp; Fachaufsätze.</span>',
			'lead' => '<p>Unser Team veröffentlicht regelmäßig in Fachzeitschriften, Kommentaren und Handbüchern zum Energiewirtschafts-, Vergabe-, Kommunal- und Steuerrecht. Eine Auswahl aus den letzten Jahren.</p>',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '140', '0' ),
			'cols' => array( array( $b::wid_shortcode( '[es_publikationen]' ) ) ),
		) );
		return $s;
	}

	/* ========================== Karriere ========================== */
	protected static function karriere_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();

		// Split-Hero: H1 links, Lead rechts (beide Text-Editor + Heading Widgets)
		$s[] = $b::section_native( array(
			'variant' => 'ink', 'css_classes' => 'es-hero',
			'padding' => array( '100', '0', '140', '0' ),
			'gap' => 'wider',
			'column_settings' => array( array( '_column_size' => 60 ), array( '_column_size' => 40 ) ),
			'cols' => array(
				array(
					$b::wid_heading( 'Karriere', 'p', 'es-eyebrow es-eyebrow--paper' ),
					$b::wid_heading( 'Starte gemeinsam<br>mit uns <span class="text-bg-green">durch.</span>', 'h1', 'es-hero__title' ),
				),
				array(
					$b::wid_text( '<p>Entfalte Dich selbst in einem jungen, schnell wachsenden Beratungsunternehmen. Wir suchen Persönlichkeiten, die Verantwortung übernehmen wollen.</p>', 'es-hero__lead' ),
				),
			),
		) );

		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '100', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Offene Positionen', 'h2', 'es-section__title' ),
				$b::wid_shortcode( '[es_karriere columns="1"]' ),
				$b::wid_html( '<p style="margin-top:32px;"><a class="es-link" href="mailto:info@energiesozietaet.de?subject=Initiativbewerbung">Initiativbewerbung →</a></p>' ),
			) ),
		) );

		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'padding' => array( '120', '0', '40', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Warum wir', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Was Dich bei uns erwartet.', 'h2', 'es-split__title' ),
			) ),
		) );
		$s[] = $b::cards_native( array(
			array( '01', 'Echte Verantwortung', 'Du arbeitest von Tag eins direkt am Mandat, mit Mandantenkontakt und eigener Themenverantwortung.' ),
			array( '02', 'Interdisziplinarität', 'Kein Silodenken. Wir bringen Recht, Steuern und Unternehmensberatung zusammen — und Dich mittendrin.' ),
			array( '03', 'Persönliche Entwicklung', 'Fortbildung, Promotion, Fachanwaltschaften — wir fördern gezielt und individuell.' ),
		), 'warm' );
		return $s;
	}

	/* ========================== News ========================== */
	protected static function news_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'News',
			'headline_html' => 'Aus der<br><span class="text-bg-green">Energiebranche.</span>',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '140', '0' ),
			'cols' => array( array( $b::wid_shortcode( '[es_news_featured limit="9"]' ) ) ),
		) );
		return $s;
	}

	/* ========================== Veranstaltungen ========================== */
	protected static function veranstaltungen_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Veranstaltungen',
			'headline_html' => 'Wo wir<br>sprechen.',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '140', '0' ),
			'cols' => array( array( $b::wid_shortcode( '[es_veranstaltungen layout="row"]' ) ) ),
		) );
		return $s;
	}

	/* ========================== Kontakt ========================== */
	protected static function kontakt_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Kontakt',
			'headline_html' => 'Sprechen Sie<br>mit uns.',
			'padding' => 'short',
		) );

		// 2 Spalten: Form links (größer), Standorte rechts (schmaler)
		// Das Formular kommt aus dem [es_kontakt_form]-Shortcode; Felder, Themen
		// und Empfänger sind unter Einstellungen → Kontaktformular pflegbar.

		// Standorte-Modul: Karte + Standortkarten in EINEM Container, über
		// data-loc gekoppelt (Hover Standort ↔ Punkt auf der Karte). Punkt-Labels
		// dauerhaft sichtbar, damit die Zuordnung Karte↔Adresse sofort klar ist.
		$offices = array(
			array( 'slug' => 'duesseldorf', 'city' => 'Düsseldorf', 'lines' => array( 'Roßstraße 92 / Kennedyhaus', '40476 Düsseldorf' ), 'tel' => '+49 211 159232-0', 'hq' => true,  'pos' => 'left:13%;top:46%' ),
			array( 'slug' => 'hamburg',     'city' => 'Hamburg',    'lines' => array( 'Caffamacherreihe 8', '20355 Hamburg' ),           'tel' => '+49 211 159232-0',     'hq' => false, 'pos' => 'left:43%;top:18%' ),
			array( 'slug' => 'mannheim',    'city' => 'Mannheim',   'lines' => array( 'Jungbuschstraße 6', '68159 Mannheim' ),           'tel' => '+49 211 159232-0',    'hq' => false, 'pos' => 'left:30%;top:71%' ),
		);
		$map_uri = function_exists( 'get_template_directory_uri' ) ? get_template_directory_uri() : '';

		$std_html  = '<div class="es-kontakt-standorte">';
		$std_html .= '<div class="es-germany-map">';
		$std_html .= '<img src="' . esc_url( $map_uri . '/assets/img/germany.png' ) . '" alt="Unsere Standorte in Deutschland" loading="lazy" />';
		foreach ( $offices as $o ) {
			$std_html .= '<span class="es-germany-dot" data-loc="' . esc_attr( $o['slug'] ) . '" style="' . esc_attr( $o['pos'] ) . '"><span class="es-germany-dot__label">' . esc_html( $o['city'] ) . '</span></span>';
		}
		$std_html .= '</div>';
		$std_html .= '<div class="es-kontakt-locations">';
		foreach ( $offices as $o ) {
			$std_html .= '<div class="es-kontakt-location" data-loc="' . esc_attr( $o['slug'] ) . '">';
			$std_html .= '<div class="es-kontakt-location__head">' . esc_html( $o['city'] );
			if ( $o['hq'] ) { $std_html .= ' <span class="es-kontakt-location__badge">Hauptsitz</span>'; }
			$std_html .= '</div>';
			foreach ( $o['lines'] as $ln ) { $std_html .= '<div class="es-kontakt-location__line">' . esc_html( $ln ) . '</div>'; }
			$std_html .= '<a class="es-kontakt-location__tel" href="tel:' . esc_attr( str_replace( ' ', '', $o['tel'] ) ) . '">' . esc_html( $o['tel'] ) . '</a>';
			$std_html .= '</div>';
		}
		$std_html .= '</div>';
		$std_html .= '<div class="es-kontakt-general"><div class="es-eyebrow">Allgemeine Anfragen</div><a href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a><div>Für allgemeine und organisatorische Anfragen.</div></div>';
		$std_html .= '</div>';

		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '140', '0' ),
			'gap' => 'wider',
			'css_classes' => 'es-kontakt-grid-wrap',
			'column_settings' => array( array( '_column_size' => 58 ), array( '_column_size' => 42 ) ),
			'cols' => array(
				array(
					$b::wid_heading( 'Schreiben Sie uns', 'p', 'es-eyebrow' ),
					$b::wid_heading( 'Wir antworten innerhalb eines Werktags.', 'h2', 'es-split__title' ),
					$b::wid_shortcode( '[es_kontakt_form]' ),
				),
				array(
					$b::wid_heading( 'Unsere Standorte', 'p', 'es-eyebrow' ),
					$b::wid_html( $std_html ),
				),
			),
		) );
		return $s;
	}

	protected static function field( $label, $name, $type = 'text' ) {
		$out  = '<div>';
		$out .= '<div style="font-size:13px;color:#899092;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:8px;">' . esc_html( $label ) . '</div>';
		if ( 'textarea' === $type ) {
			$out .= '<textarea name="' . esc_attr( $name ) . '" rows="5" style="width:100%;border:0;border-bottom:1px solid #122023;background:transparent;padding:10px 0;font:inherit;font-size:16px;resize:vertical;outline:none;"></textarea>';
		} else {
			$out .= '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" style="width:100%;border:0;border-bottom:1px solid #122023;background:transparent;padding:10px 0;font:inherit;font-size:16px;outline:none;" />';
		}
		$out .= '</div>';
		return $out;
	}

}
