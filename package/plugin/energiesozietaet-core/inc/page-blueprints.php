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
			'buttons' => array(
				array( 'Leistungen entdecken', '/leistungen/', 'paper' ),
				array( 'Unser Team kennenlernen', '/team/', 'ghost-paper' ),
			),
			'claims' => array(
				array( '20+', 'Berufsträger & Mitarbeiter' ),
				array( '3', 'Beratungsfelder' ),
				array( '2023', 'Gegründet' ),
				array( '3', 'Standorte · DUS · HH · MA' ),
			),
			'padding' => 'tall',
		) );

		// 2. Intro (Split Text warm)
		$s[] = $b::split_native( array(
			'eyebrow' => 'Unser Anspruch',
			'paragraphs' => array(
				'<p style="font-size:26px;line-height:1.35;letter-spacing:-0.015em;color:#0E1A2B;">Wir arbeiten <em>hoch spezialisiert</em>, fachübergreifend und fokussiert an den Themen unserer Zeit — unaufgeregt, ergebnisorientiert und mit individuellen Persönlichkeiten.</p>',
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
					$b::wid_heading( 'Interdisziplinäre Beratung.<br><span style="color:#5A6577;">Drei Felder, ein Gedanke.</span>', 'h2', 'es-home-services-head__title' ),
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

		// 6. CTA
		$s[] = $b::cta_dark_native();
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
		), 'warm' );

		// Mandantschaft (Split mit Liste)
		$items_html  = '<ul class="es-mandanten">';
		foreach ( array(
			'Energieversorgungsunternehmen','Stadtwerke & kommunale Unternehmen','Bund & Länder',
			'Kommunen & Einrichtungen der öffentlichen Hand','Infrastrukturdienstleister','Investoren',
		) as $i => $m ) {
			$items_html .= '<li><span class="es-mandanten__num">' . str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) . '</span> ' . esc_html( $m ) . '</li>';
		}
		$items_html .= '</ul>';
		$s[] = $b::split_native( array(
			'eyebrow' => 'Mandantschaft',
			'title_html' => 'Wen wir beraten.',
			'paragraphs' => array(
				'<p>Mit unserem Beratungsangebot adressieren wir Energieversorgungsunternehmen, Bund, Länder und Kommunen sowie deren Einrichtungen und Unternehmen. Hinzu kommen private und öffentliche Infrastrukturdienstleister und Investoren.</p>',
			),
			'extra_after' => array( $b::wid_html( $items_html ) ),
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

		$s[] = $b::cta_dark_native( array(
			'eyebrow' => 'Kontakt',
			'title_html' => 'Haben wir Ihr Interesse geweckt?',
			'sub' => 'Möchten Sie uns kennenlernen?',
			'buttons' => array(
				array( 'Kontakt aufnehmen', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
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
			'rechtsberatung'       => 'Rechts&shy;<br>beratung',
			'steuerberatung'       => 'Steuer&shy;<br>beratung',
			'unternehmensberatung' => 'Unternehmens&shy;<br>beratung',
		);
		$i = 0;
		foreach ( array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' ) as $slug ) {
			$cfg = $bf[ $slug ];
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
			) );
			$i++;
		}

		$s[] = $b::cta_dark_native( array(
			'eyebrow' => 'Kontakt',
			'title_html' => 'Haben wir Ihr Interesse geweckt?',
			'sub' => 'Möchten Sie uns kennenlernen?',
			'buttons' => array(
				array( 'Kontakt aufnehmen', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
		) );
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

		// Ansprechpartner-Balken (zwei Personen + CTA)
		$ap_html  = '<div class="es-ansprech"><div class="es-eyebrow" style="margin:0 40px 0 0;flex-shrink:0;">Ihre Ansprechpartner</div>';
		$ap_html .= '<div class="es-ansprech__people">';
		foreach ( $d['ansprechpartner'] as $p ) {
			$ap_html .= '<a href="/teammitglied/' . esc_attr( $p[0] ) . '/">';
			$ap_html .= '<div class="es-ansprech__photo">[es_team_photo slug="' . esc_attr( $p[0] ) . '" size=52]</div>';
			$ap_html .= '<div><div class="es-ansprech__name">' . esc_html( $p[1] ) . '</div><div class="es-ansprech__role">' . esc_html( $p[2] ) . '</div></div>';
			$ap_html .= '</a>';
		}
		$ap_html .= '</div>';
		$ap_html .= '<a class="es-btn es-btn--primary" href="/kontakt/">Termin anfragen →</a></div>';
		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'padding' => array( '28', '0', '28', '0' ),
			'cols' => array( array( $b::wid_html( $ap_html ) ) ),
		) );

		// Content Split: Text links (native Widgets), Einzelleistungen rechts (2 Spalten, mehr Padding)
		$s[] = $b::section_native( array(
			'css_classes' => 'es-bereich-detail__content',
			'padding' => array( '120', '0', '120', '0' ),
			'gap' => 'wider',
			'column_settings' => array(
				array( '_column_size' => 40 ),
				array( '_column_size' => 60 ),
			),
			'cols' => array(
				array(
					$b::wid_heading( 'Was wir für Sie tun', 'p', 'es-eyebrow' ),
					$b::wid_heading( $d['long_title'], 'h2', 'es-split__title' ),
					$b::wid_text( '<p>' . esc_html( $d['long_copy'][0] ) . '</p>' ),
					$b::wid_text( '<p>' . esc_html( $d['long_copy'][1] ) . '</p>' ),
				),
				array(
					$b::wid_heading( 'Einzelleistungen · ' . $d['title'], 'p', 'es-eyebrow' ),
					$b::wid_shortcode( '[es_einzelleistungen beratungsfeld="' . esc_attr( $slug ) . '" columns="2" wrapper="es-bereich-detail__einzel"]', 'es-bereich-detail__einzel' ),
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

		$s[] = $b::cta_dark_native( array(
			'title_html' => 'Ihre Fragestellung — unsere Expertise.',
			'sub' => 'Wir freuen uns auf Ihre Anfrage.',
		) );
		return $s;
	}

	protected static function team_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Team',
			'headline_html' => 'Beratung mit<br/>Gesicht.',
			'lead' => 'In der Energiesozietät hat jahrzehntelange Erfahrung einen agilen und pragmatischen Rahmen bekommen.',
			'padding' => 'short',
		) );

		// Filter + Grid (beides vom Shortcode gerendert, nutzt es_field Meta + ?feld=...)
		$grid_html = '<div class="es-wrap" style="padding:40px 0 80px;">[es_team columns="4" filter="1"]</div>';
		$sections[] = $b::section_html( $grid_html );

		// Bottom anchor — keinen harten Cut, sondern weicher Übergang zur CTA
		$anchor_html  = '<div class="es-wrap" style="padding:80px 0;border-top:1px solid #E4E7EC;">';
		$anchor_html .= '<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:64px;align-items:center;">';
		$anchor_html .= '<div><div class="es-eyebrow">Netzwerk</div><h3 style="font-size:32px;font-weight:400;letter-spacing:-0.02em;margin:16px 0 0;">Kooperationen mit namhaften Beratungshäusern.</h3></div>';
		$anchor_html .= '<div><p style="font-size:17px;line-height:1.65;color:#5A6577;margin:0;">Sollten wir selbst einmal nicht die geeigneten Spezialisten im Team haben, kooperieren wir mit namhaften Beratungsgesellschaften, mit denen wir seit vielen Jahren vertrauensvoll zusammenarbeiten. Sprechen Sie uns gerne direkt an.</p></div>';
		$anchor_html .= '</div></div>';
		$sections[] = $b::section_html( $anchor_html, 'warm' );

		$sections[] = $b::cta_dark();

		return $sections;
	}

	protected static function publikationen_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Publikationen',
			'headline_html' => 'Bücher,<br/>Kommentare<br/><span style="color:rgba(255,255,255,0.55);">&amp; Fachaufsätze.</span>',
			'lead' => 'Unser Team veröffentlicht regelmäßig in Fachzeitschriften, Kommentaren und Handbüchern zum Energiewirtschafts-, Vergabe-, Kommunal- und Steuerrecht. Eine Auswahl aus den letzten Jahren.',
			'padding' => 'short',
		) );

		$list_html = '<div class="es-wrap" style="padding:80px 0 140px;">[es_publikationen]</div>';
		$sections[] = $b::section_html( $list_html );

		$sections[] = $b::cta_dark();

		return $sections;
	}

	protected static function karriere_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		// Hero — Split-Variante mit Lead rechts
		$hero_html  = '<div class="es-wrap" style="padding:100px 0 140px;">';
		$hero_html .= '<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:64px;align-items:end;">';
		$hero_html .= '<div>';
		$hero_html .= '<div class="es-eyebrow es-eyebrow--paper">Karriere</div>';
		$hero_html .= '<h1 style="font-size:clamp(44px,6vw,80px);line-height:1;font-weight:300;letter-spacing:-0.04em;margin:0;max-width:900px;">Starte gemeinsam<br/>mit uns <span class="text-bg-green">durch.</span></h1>';
		$hero_html .= '</div>';
		$hero_html .= '<p style="font-size:19px;line-height:1.55;color:rgba(255,255,255,0.78);font-weight:300;margin:0;">Entfalte Dich selbst in einem jungen, schnell wachsenden Beratungsunternehmen. Wir suchen Persönlichkeiten, die Verantwortung übernehmen wollen.</p>';
		$hero_html .= '</div></div>';
		$sections[] = $b::section_html( $hero_html, 'ink' );

		// Offene Positionen — via angepasster es_karriere shortcode (kept as-is, grid 3)
		$pos_html  = '<div class="es-wrap" style="padding:80px 0 100px;">';
		$pos_html .= '<div style="display:flex;align-items:end;justify-content:space-between;margin-bottom:40px;flex-wrap:wrap;gap:16px;">';
		$pos_html .= '<h2 style="font-size:clamp(28px,3vw,36px);font-weight:400;letter-spacing:-0.025em;margin:0;">Offene Positionen</h2>';
		$pos_html .= '<a class="es-link" href="mailto:info@energiesozietaet.de?subject=Initiativbewerbung">Initiativbewerbung →</a>';
		$pos_html .= '</div>';
		$pos_html .= '[es_karriere columns="3"]';
		$pos_html .= '</div>';
		$sections[] = $b::section_html( $pos_html );

		// Benefits
		$ben_html  = '<div class="es-wrap" style="padding:120px 0;">';
		$ben_html .= '<div class="es-eyebrow">Warum wir</div>';
		$ben_html .= '<h2 style="font-size:clamp(32px,4.2vw,52px);line-height:1.05;font-weight:400;letter-spacing:-0.03em;margin:0 0 56px;max-width:720px;">Was Sie bei uns erwartet.</h2>';
		$ben_html .= '<div class="esc-grid esc-grid--cols-3" style="gap:24px;">';
		$benefits = array(
			array( 'Echte Verantwortung', 'Sie arbeiten von Tag eins direkt am Mandat, mit Mandantenkontakt und eigener Themenverantwortung.' ),
			array( 'Interdisziplinarität', 'Kein Silodenken. Wir bringen Recht, Steuern und Unternehmensberatung zusammen — und Sie mittendrin.' ),
			array( 'Persönliche Entwicklung', 'Fortbildung, Promotion, Fachanwaltschaften — wir fördern gezielt und individuell.' ),
		);
		foreach ( $benefits as $i => $bn ) {
			$ben_html .= '<div style="padding:36px;background:#FFFFFF;border:1px solid #E4E7EC;">';
			$ben_html .= '<div style="font-family:var(--es-font-mono);font-size:12px;color:#95D708;letter-spacing:0.1em;margin-bottom:24px;">0' . ( $i + 1 ) . '</div>';
			$ben_html .= '<h3 style="font-size:22px;font-weight:500;letter-spacing:-0.015em;margin-bottom:14px;">' . esc_html( $bn[0] ) . '</h3>';
			$ben_html .= '<p style="font-size:15px;color:#5A6577;line-height:1.55;margin:0;">' . esc_html( $bn[1] ) . '</p>';
			$ben_html .= '</div>';
		}
		$ben_html .= '</div></div>';
		$sections[] = $b::section_html( $ben_html, 'warm' );

		$sections[] = $b::cta_dark( array(
			'eyebrow' => 'Noch Fragen?',
			'title_html' => 'Ein persönliches Gespräch sagt mehr als eine Anzeige.',
			'sub' => 'Schreiben Sie uns kurz — wir melden uns binnen eines Werktags.',
		) );

		return $sections;
	}

	protected static function news_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'News',
			'headline_html' => 'Aus der<br/><span class="text-bg-green">Energiebranche.</span>',
			'padding' => 'short',
		) );

		$list_html = '<div class="es-wrap" style="padding:80px 0 140px;">[es_news_featured limit="9"]</div>';
		$sections[] = $b::section_html( $list_html );

		$sections[] = $b::cta_dark();
		return $sections;
	}

	protected static function veranstaltungen_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Veranstaltungen',
			'headline_html' => 'Wo wir<br/>sprechen.',
			'padding' => 'short',
		) );

		$list_html = '<div class="es-wrap" style="padding:80px 0 140px;">[es_veranstaltungen layout="row"]</div>';
		$sections[] = $b::section_html( $list_html );

		$sections[] = $b::cta_dark();
		return $sections;
	}

	protected static function kontakt_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Kontakt',
			'headline_html' => 'Sprechen Sie<br/>mit uns.',
			'padding' => 'short',
		) );

		// Form + Standorte nebeneinander
		$html  = '<div class="es-wrap" style="padding:80px 0 140px;">';
		$html .= '<div style="display:grid;grid-template-columns:1.2fr 1fr;gap:96px;align-items:start;">';
		// Form
		$html .= '<div>';
		$html .= '<div class="es-eyebrow">Schreiben Sie uns</div>';
		$html .= '<h2 style="font-size:clamp(28px,3.2vw,36px);font-weight:400;letter-spacing:-0.025em;margin:0 0 40px;">Wir antworten innerhalb eines Werktags.</h2>';
		$html .= '<form action="mailto:info@energiesozietaet.de" method="post" enctype="text/plain" style="display:grid;gap:24px;">';
		$html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">';
		$html .= self::field( 'Vorname', 'vorname' );
		$html .= self::field( 'Nachname', 'nachname' );
		$html .= '</div>';
		$html .= self::field( 'E-Mail', 'email', 'email' );
		$html .= self::field( 'Unternehmen / Organisation', 'org' );
		$html .= '<div>';
		$html .= '<div style="font-size:12px;color:#5A6577;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:12px;">Betrifft</div>';
		$html .= '<div style="display:flex;gap:8px;flex-wrap:wrap;">';
		foreach ( array( 'Rechtsberatung', 'Steuerberatung', 'Unternehmensberatung', 'Karriere', 'Presse', 'Sonstiges' ) as $j => $opt ) {
			$html .= '<label style="padding:10px 16px;font-size:13px;border-radius:2px;border:1px solid #E4E7EC;cursor:pointer;">';
			$html .= '<input type="radio" name="thema" value="' . esc_attr( $opt ) . '" style="display:none;" ' . ( 0 === $j ? 'checked' : '' ) . '/> ' . esc_html( $opt );
			$html .= '</label>';
		}
		$html .= '</div></div>';
		$html .= self::field( 'Ihre Nachricht', 'nachricht', 'textarea' );
		$html .= '<div style="display:flex;align-items:center;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:16px;">';
		$html .= '<div style="font-size:12px;color:#5A6577;max-width:400px;">Mit dem Absenden stimmen Sie unserer <a href="/datenschutzerklaerung/">Datenschutzerklärung</a> zu.</div>';
		$html .= '<button class="es-btn es-btn--primary" type="submit">Nachricht senden →</button>';
		$html .= '</div>';
		$html .= '</form></div>';

		// Standorte
		$html .= '<div>';
		$html .= '<div class="es-eyebrow">Unsere Standorte</div>';
		$offices = array(
			array( 'Düsseldorf', array( 'Roßstraße 92 / Kennedyhaus', '40476 Düsseldorf' ), '+49 211 159232-0', true ),
			array( 'Hamburg', array( 'Caffamacherreihe 8', '20355 Hamburg' ), '+49 211 159232-0', false ),
			array( 'Mannheim', array( 'Jungbuschstraße 6', '68159 Mannheim' ), '+49 211 159232-0', false ),
		);
		foreach ( $offices as $i => $o ) {
			$border = $i < count( $offices ) - 1 ? 'border-bottom:1px solid #E4E7EC;' : '';
			$html .= '<div style="padding:28px 0;' . $border . '">';
			$html .= '<div style="font-size:24px;font-weight:500;letter-spacing:-0.02em;margin-bottom:12px;display:flex;align-items:center;gap:10px;">' . esc_html( $o[0] );
			if ( $o[3] ) {
				$html .= '<span style="font-size:10px;color:#95D708;letter-spacing:0.14em;text-transform:uppercase;padding:3px 8px;border:1px solid #95D708;border-radius:999px;">Hauptsitz</span>';
			}
			$html .= '</div>';
			foreach ( $o[1] as $ln ) { $html .= '<div style="font-size:15px;color:#5A6577;line-height:1.5;">' . esc_html( $ln ) . '</div>'; }
			$html .= '<div style="font-size:15px;color:#0E1A2B;margin-top:10px;font-family:var(--es-font-mono);"><a href="tel:' . esc_attr( str_replace( ' ', '', $o[2] ) ) . '">' . esc_html( $o[2] ) . '</a></div>';
			$html .= '</div>';
		}
		$html .= '<div style="margin-top:40px;padding:28px;background:#F6F4EF;border:1px solid #E4E7EC;">';
		$html .= '<div class="es-eyebrow" style="margin-bottom:12px;">Allgemeine Anfragen</div>';
		$html .= '<div style="font-size:17px;font-weight:500;margin-bottom:4px;"><a href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a></div>';
		$html .= '<div style="font-size:14px;color:#5A6577;">Für allgemeine und organisatorische Anfragen.</div>';
		$html .= '</div></div>';

		$html .= '</div></div>';
		$sections[] = $b::section_html( $html );

		return $sections;
	}

	/** Kontakt-Form field */
	protected static function field( $label, $name, $type = 'text' ) {
		$out  = '<div>';
		$out .= '<div style="font-size:12px;color:#5A6577;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:8px;">' . esc_html( $label ) . '</div>';
		if ( 'textarea' === $type ) {
			$out .= '<textarea name="' . esc_attr( $name ) . '" rows="5" style="width:100%;border:0;border-bottom:1px solid #0E1A2B;background:transparent;padding:10px 0;font:inherit;font-size:16px;resize:vertical;outline:none;"></textarea>';
		} else {
			$out .= '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" style="width:100%;border:0;border-bottom:1px solid #0E1A2B;background:transparent;padding:10px 0;font:inherit;font-size:16px;outline:none;" />';
		}
		$out .= '</div>';
		return $out;
	}

}
