<?php
/**
 * Page blueprints – Elementor-JSON für jede Top-Level-Seite.
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
	 * Hilfsdaten – Beratungsfelder, Ansprechpartner, Topic-Texte aus Mockup.
	 * ========================================================================== */

	protected static function beratungsfelder() {
		return array(
			'rechtsberatung' => array(
				'teaser' => 'Wir stellen juristische Lösungen in den Gesamtkontext.',
				'n' => '01',
				'title' => 'Rechtsberatung',
				'sub' => 'Juristische Lösungen im Gesamtkontext',
				'lede' => 'Wir haben ein ausgeprägtes Verständnis von Energiemärkten, Verwaltungsorganisationen und ihren politischen Entscheidungsprozessen.',
				'ansprechpartner' => array(
					array( 'prof-dr-sven-joachim-otto', 'Prof. Dr. Sven-Joachim Otto', 'Partner · Rechtsanwalt' ),
					array( 'dr-bernhardine-kleinhenz-jeannot', 'Dr. Bernhardine Kleinhenz-Jeannot', 'Partnerin · Rechtsanwältin' ),
				),
				'long_title' => 'Wir stellen juristische Lösungen in den Gesamtkontext.',
				'long_copy' => array(
					'Mit unserem Beratungsangebot adressieren wir Energieversorgungsunternehmen, Bund, Länder und Kommunen sowie deren Einrichtungen und Unternehmen sowie private und öffentliche Infrastrukturdienstleister und Investoren.',
					'Wir haben ein ausgeprägtes Verständnis von Energiemärkten, Verwaltungsorganisationen und ihren politischen Entscheidungsprozessen. Politikberatung ist integraler Bestandteil unserer Tätigkeit, um Ihre Projekte zum Erfolg zu führen.',
				),
				'topics' => array(
					array( 'Energie', 'Die Transformation unserer Energieversorgung ist eine der größten Herausforderungen unserer Zeit.' ),
					array( 'Kommunalwirtschaft', 'Die wirtschaftliche Betätigung von Kommunen unterliegt besonderen Spielregeln.' ),
					array( 'Vergaberecht', 'Öffentliche Beschaffung unterliegt umfangreichen, inhaltlich anspruchsvollen, rechtlichen Anforderungen. Zudem sind personelle Ressourcen hierfür in der öffentlichen Verwaltung häufig knapp.' ),
					array( 'Gesellschaftsrecht', 'Strukturelle Veränderungen benötigen einen gesellschaftsrechtlichen Rahmen.' ),
					array( 'Bau- und Planungsrecht', 'Komplexe Vorhaben sind unsere Stärke.' ),
					array( 'Umweltrecht', 'Rechtsberatung für eine nachhaltige und innovative Zukunft.' ),
				),
			),
			'steuerberatung' => array(
				'teaser' => 'Fortlaufende Steuerberatung, Gestaltungsberatung oder herausfordernde Neustrukturierungen?',
				'n' => '02',
				'title' => 'Steuerberatung',
				'sub' => 'Fortlaufend · Gestaltend · Strukturierend',
				'lede' => 'Wir begleiten unsere Mandanten umfassend bei allen steuerlichen Fragen.',
				'ansprechpartner' => array(
					array( 'torsten-stockem', 'Torsten Stockem', 'Partner · Steuerberater' ),
					array( 'jana-fengler', 'Jana Fengler', 'Steuerberaterin' ),
				),
				'long_title' => 'Fortlaufende Steuerberatung, Gestaltungsberatung oder herausfordernde Neustrukturierungen?',
				'long_copy' => array(
					'Wir begleiten unsere Mandanten umfassend bei allen steuerlichen Fragen: Unternehmen der Privat- und Kommunalwirtschaft, juristische Personen des öffentlichen Rechts, Privatpersonen und Existenzgründer.',
					'Besonderes Know-how haben wir in der Betreuung der öffentlichen Hand und ihrer Unternehmen – dieses Wissen setzen wir ebenso erfolgreich in der Privatwirtschaft ein.',
				),
				'topics' => array(
					array( 'Gründungs- und Strukturberatung', 'Etwas Neues zu schaffen oder Bestehendes grundlegend zu verändern ist eine besondere Herausforderung.' ),
					array( 'Steuererklärungen und Deklaration', 'Steuern sind ein Thema, mit dem man sich fortlaufend befassen muss.' ),
					array( 'Strom- und Energiesteuern', 'Hohe administrative Aufgaben richtig managen.' ),
					array( 'Tax Compliance Management System', 'Risiken managen.' ),
					array( 'Finanzbuchhaltung', 'Ordnungsgemäß, digital, effizient.' ),
					array( 'Lohn- und Gehaltsabrechnung', 'Komplexität im Griff, fehlerhafte Abrechnungen vermeiden, Compliance sichern.' ),
				),
			),
			'unternehmensberatung' => array(
				'teaser' => 'Wir navigieren Sie durch die Anforderungen neuer Energieträger und -erzeugungsarten sowie ihrer wirtschaftlichen und finanziellen Fragestellungen.',
				'n' => '03',
				'title' => 'Unternehmensberatung',
				'sub' => 'Strategie · Wirtschaft · Finanzen',
				'lede' => 'In der Energiebranche erzeugt das Erfordernis, den Klimazielen gerecht zu werden, einen enormen Druck – traditionellen Geschäftsmodellen werden Grenzen aufgezeigt.',
				'ansprechpartner' => array(
					array( 'elke-beermann', 'Elke Beermann', 'Partnerin · Steuerberaterin' ),
					array( 'hartmut-muller', 'Hartmut Müller', 'Senior Advisor' ),
				),
				'long_title' => 'Wir navigieren Sie durch die Anforderungen neuer Energieträger und Erzeugungsarten sowie ihrer wirtschaftlichen und finanziellen Fragestellungen.',
				'long_copy' => array(
					'Neue Energieträger, innovative Erzeugungstechnologien und leistungsfähige Netze sollen die Energieversorgung von Morgen sicherstellen. Unternehmen müssen Geschäftsmodelle entwickeln und ihre Organisationen weiterentwickeln, um wegfallendes Bestandsgeschäft zu kompensieren und neue Wachstumsstrategien zu erschließen.',
					'Unsere Experten unterstützen Sie bei diesen herausfordernden Fragestellungen von Beginn an: als Sparringspartner in allerersten Überlegungen, bei der Entwicklung von Strategieoptionen, bei deren Konkretisierung und Überprüfung in Business Plänen, bei der Finanzierung sowie bei der Suche nach strategischen Partnern für die Umsetzung.',
					'Investier- und Finanzierbarkeit von Transformation erreichen: Erfolgreiche Energiewendeprojekte benötigen fundierte Planung, solide Finanzierung und nachhaltige Investorenmodelle. Wir unterstützen Unternehmen und Stadtwerke dabei, Energiewendeprojekte investierbar und finanzierbar aufzubauen – durch professionelle Geschäftsmodellentwicklung, moderne Finanzierungsstrukturen, die Optimierung der Kapitalstruktur, das Durchleuchten regulatorischer Rahmenbedingungen und die Begleitung beim Zugang zu Investoren und Kapitalmärkten.',
				),
				'topics' => array(
					array( 'Erneuerbare Energien', 'Fundament für Versorgungssicherheit und Klimaschutz.' ),
					array( 'Wärme', 'Effizienter Weg zur Wärmewende.' ),
					array( 'Wasserstoff', 'Schlüsseltechnologie für eine klimaneutrale Zukunft.' ),
					array( 'Regulierung', 'Sicherheit in einem dynamischen Energiemarkt.' ),
					array( 'Wasserwirtschaft', 'Nachhaltigkeit trifft Wirtschaftlichkeit.' ),
					array( 'Kooperation und Transaktion', 'Stärken zusammenbringen – Chancen steigern.' ),
					array( 'Projektmanagement', 'Transformation anstoßen, entwickeln und umsetzen.' ),
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
			// Zahlen-Stats (Claims-Grid) ebenfalls standardmäßig auf allen
			// Bildschirmgrößen ausgeblendet (Elementor Erweitert → Responsiv).
			'claims_settings' => array(
				'hide_desktop' => 'hidden-desktop',
				'hide_tablet'  => 'hidden-tablet',
				'hide_mobile'  => 'hidden-phone',
			),
			// Rechte Spalte: die zwei Stat-Cards ersetzen die früheren Buttons
			// und sind die interaktiven Elemente (Team / Leistungen) – sichtbar.
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

		// 2. Intro (Split Text warm) – H2 als eigentliche Überschrift, Eyebrow klein
		$s[] = $b::split_native( array(
			'eyebrow' => 'Unsere Philosophie',
			'title_html' => 'Interdisziplinäre Beratung in komplexen Fragestellungen: <span class="text-bg-green">fokussiert, ergebnisorientiert und kreativ.</span>',
			'paragraphs' => array(
				'<p>Transformation ist eine Mammutaufgabe: dafür braucht es viele, das schafft niemand allein. Wir – das Team der Energiesozietät – verstehen uns als einer der vielen. Wir werden Teil sein und möchten Verantwortung übernehmen. Unser Ziel ist, Lösungen zu entwickeln für die überaus komplexen Transformationsfragen unserer Zeit. Wir unterstützen unsere Mandanten dabei, Rahmenbedingungen zu schaffen, Geschäftsmodelle zu entwickeln und Organisationen dafür zu verändern, dass sie die Herausforderungen der Zeit meistern.</p>',
				'<p><a class="es-link" href="/philosophie/">Mehr erfahren</a> · <a class="es-link" href="/team/">Unser Team</a></p>',
			),
			'variant' => 'cool',
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
					$b::wid_heading( 'Interdisziplinäre Beratung.', 'h2', 'es-home-services-head__title' ),
					$b::wid_text( '<p>Wir denken Lösungen vom Ende her: strategische, technische, betriebs- und finanzwirtschaftliche, juristische und steuerliche Lösungen stellen wir dafür in den Gesamtkontext.</p>' ),
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
					$b::wid_text( '<p>Wir stellen juristische Lösungen in den Gesamtkontext.</p>', 'es-service-card__body' ),
					$b::wid_text( '<p><a class="es-link" href="/rechtsberatung/">Mehr erfahren →</a></p>' ),
				),
				array(
					$b::wid_html( '<div class="es-service-card__head"><span class="es-service-card__num">02 / 03</span><span class="es-service-card__dot"></span></div>' ),
					$b::wid_heading( 'Steuerberatung', 'h3', 'es-service-card__title' ),
					$b::wid_text( '<p>Fortlaufende Steuerberatung, Gestaltungsberatung oder herausfordernde Neustrukturierungen?</p>', 'es-service-card__body' ),
					$b::wid_text( '<p><a class="es-link" href="/steuerberatung/">Mehr erfahren →</a></p>' ),
				),
				array(
					$b::wid_html( '<div class="es-service-card__head"><span class="es-service-card__num">03 / 03</span><span class="es-service-card__dot"></span></div>' ),
					$b::wid_heading( 'Unternehmensberatung', 'h3', 'es-service-card__title' ),
					$b::wid_text( '<p>Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen.</p>', 'es-service-card__body' ),
					$b::wid_text( '<p><a class="es-link" href="/unternehmensberatung/">Mehr erfahren →</a></p>' ),
				),
			),
		) );

		// 4. GF Quote (bleibt als gf_quote-Helper – enthält bereits Image+Blockquote)
		$s[] = $b::gf_quote( array(
			'quote' => 'Wir wollen mit unserer langjährigen Beratungs- und Netzwerkerfahrung unsere Mandantschaft bei den anstehenden Transformationen begleiten und mit den individuellen Persönlichkeiten unseres Teams Mehrwert bieten.',
			'role' => 'Partner',
		) );

		// 5. News Teaser (shortcode widget)
		$s[] = $b::section_native( array(
			'css_classes' => 'es-home-news',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Aktuelles', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Neues aus der Energiebranche.', 'h2', 'es-home-news__title' ),
				$b::wid_shortcode( '[es_news limit="3" columns="3"]' ),
				$b::wid_text( '<p style="margin-top:32px;"><a class="es-link" href="/news/">Alle Beiträge ansehen →</a></p>' ),
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
			'eyebrow' => 'Unsere Philosophie',
			'headline_html' => 'Ergebnisse,<br><span class="text-bg-green">die weitertragen.</span>',
			'lead' => '<p>Transformation ist eine Mammutaufgabe: dafür braucht es viele, das schafft niemand allein. Wir – das Team der Energiesozietät – verstehen uns als einer der vielen.</p>',
			'padding' => 'tall',
		) );

		// Split: "Wir wissen, wie komplex Ihre Aufgabe ist."
		$s[] = $b::split_native( array(
			'eyebrow' => 'Ihre Aufgabe',
			'title_html' => 'Wir wissen, wie komplex<br><span class="text-bg-green">Ihre Aufgabe ist.</span>',
			'paragraphs' => array(
				'<p>Entscheidungsträger und Gestalter in der öffentlichen Wirtschaft und der Energieversorgung bewegen sich in einem extrem komplexen Umfeld.</p>',
				'<p>Wir sind uns dieser Einflussfaktoren sehr bewusst. Deshalb beziehen wir Sie in unsere Lösungen stets mit ein, so dass unsere Beratungsergebnisse bestmöglich Ihren gestalterischen oder unternehmerischen Zielen dienen.</p>',
				'<p>Wir denken unsere Projekte vom Ende her. Unsere Beratung endet nicht mit der Beantwortung Ihrer konkreten Fragestellung. Vielmehr beinhaltet sie, dass wir berücksichtigen, wie Sie mit dem Endprodukt unserer Beratung weiterarbeiten, und bezieht auch eine adressatengerechte Ergebnisdarstellung und -kommunikation mit ein.</p>',
			),
			'variant' => 'cool',
			'padding' => 'default',
		) );

		// 4 Einflussfaktoren (Original der Live-Seite)
		$s[] = $b::cards_native( array(
			array( '', 'Wachsende Aufgaben', '' ),
			array( '', 'Steigende regulatorische Anforderungen', '' ),
			array( '', 'Komplexe Entscheidungsprozesse', '' ),
			array( '', 'Multi-Stakeholder', '' ),
		), 'cool', 'es-cards-grid--quads' );

		// Überleitung + die 7 Punkte "Was uns besonders macht" (Original Live-Seite)
		$s[] = $b::section_native( array(
			'variant' => '',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Was uns besonders macht', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Langjährige interdisziplinäre Beratungserfahrung, vielfach erprobt.', 'h2', 'es-split__title' ),
				$b::wid_text( '<p>Die Teammitglieder der Energiesozietät haben in unzähligen Projekten Erfahrungen gesammelt. Jeder zeichnet sich durch besondere Fähigkeiten aus. Was uns in der Energiesozietät gemeinsam besonders macht:</p>' ),
				$b::wid_shortcode( '[es_mandanten items="Was wir tun, tun wir mit Leidenschaft.|Als Energiesozietät vereinen wir jahrzehntelange Großkanzleierfahrung mit den Vorteilen einer schlagkräftigen, kleinen Einheit („Boutique“).|Wir sind klein, schnell und unkompliziert.|Wir sind Experten, in dem was wir tun.|Jeder Experte in unserem Team ist ein Stück weit ein Generalist, denn wir arbeiten interdisziplinär. Dies erlaubt es uns, den Blick zu weiten auf der Suche nach der besten Lösung.|Wir haben einen hohen Qualitätsanspruch an die Ergebnisse unserer Arbeit."]' ),
			) ),
		) );

		// Pullquote (bleibt als gf-Helper gerendert im Panel) – dunkler
		// Abschnitt, das weiße Zitat-Panel schwebt darauf
		$s[] = $b::pullquote(
			'„Unseren Mandanten ist eines gemein: Sie bewegen sich alle in einem <span class="text-bg-green">hochkomplexen</span> und <span class="text-bg-green">sich kontinuierlich weiterentwickelnden</span> Umfeld."',
			'Unsere Überzeugung',
			'warm'
		);

		// 4 Perspektiven – nächster Farbzyklus: hellgrau
		$s[] = $b::section_native( array(
			'variant' => 'cool',
			'padding' => array( '120', '0', '40', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Interdisziplinär', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Ein Team aus vier Perspektiven.', 'h2', 'es-split__title' ),
				$b::wid_text( '<p>Unser Team von Ingenieuren, Kaufleuten, Rechtsanwälten und Steuerberatern erarbeitet mit Ihnen ganzheitliche, tragfähige Lösungen als Grundlage für eine gut abgewogene Entscheidung.</p>' ),
			) ),
		) );
		$s[] = $b::cards_native( array(
			array( '', 'Rechtsanwälte', 'Juristische Gestaltung im regulatorischen Gesamtkontext.' ),
			array( '', 'Steuerberater', 'Steuerliche Struktur, Compliance und Gestaltungsspielräume.' ),
			array( '', 'Wirtschaftsingenieure', 'Technische Lösungen und Machbarkeit im Energiesystem.' ),
			array( '', 'Kaufleute',     'Betriebs- und finanzwirtschaftliche Bewertung und Strukturierung.' ),
		), 'cool', 'es-cards-grid--quads' );

		// Mandantschaft – Liste als Shortcode, im Elementor-Editor anpassbar
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
				'<p>Wir entwickeln für Sie Konzepte für die Kommunikation der Lösungen im politischen Entscheidungsprozess. Unser Ziel ist es, bestmögliche Lösungen zu präsentieren, die eine breite Akzeptanz finden.</p>',
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
				'<p>Unseren Mandanten ist eines gemein: Sie bewegen sich alle in einem hochkomplexen und sich kontinuierlich weiterentwickelnden Umfeld. Der politische Rahmen, regulatorische Anforderungen und technisch-wirtschaftliche Lösungsoptionen verändern sich fortwährend. Entscheidungsträger sind gefordert, in diesem dynamischen Umfeld gute Entscheidungen zu treffen.</p>',
				'<p>Die Energiesozietät ist seit ihrer Gründung Ende 2023 dynamisch gewachsen und verfügt heute über ein Team mit großem Erfahrungsschatz. Viele Teammitglieder haben jahrelang erfolgreich in Big-Four-Gesellschaften, spezialisierten Beratungsgesellschaften und Kanzleien gearbeitet.</p>',
				'<p>Sollten wir selbst einmal nicht die geeigneten Spezialisten im Team haben, um Ihre Fragen bestmöglich zu beantworten, kooperieren wir mit namhaften Beratungsgesellschaften, mit denen wir seit vielen Jahren vertrauensvoll zusammenarbeiten.</p>',
			),
			'variant' => 'cool',
			'padding' => 'short',
		) );

		// 3 Bereichs-Blöcke NATIV (Heading/Text/Button-Widgets, Bild aus dem
		// Beitragsbild der Beratungsfeld-Seite, Einzelleistungen dynamisch)
		$bf = self::beratungsfelder();
		$titles_html = array(
			'rechtsberatung'       => 'Rechtsberatung',
			'steuerberatung'       => 'Steuerberatung',
			'unternehmensberatung' => 'Unternehmensberatung',
		);
		$order = array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' );
		foreach ( $order as $i => $slug ) {
			$cfg = $bf[ $slug ];
			foreach ( $b::bereich_native( array(
				'n' => $cfg['n'],
				'title' => $cfg['title'],
				'title_html' => $titles_html[ $slug ],
				'sub' => $cfg['sub'],
				'lede' => isset( $cfg['teaser'] ) ? $cfg['teaser'] : $cfg['lede'],
				'link' => '/' . $slug . '/',
				'field' => $slug,
				'stripe' => ( $i % 2 === 0 ) ? 'odd' : 'even',
			) ) as $sec ) { $s[] = $sec; }
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
				$b::wid_text( $crumb_html ),
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
			'variant' => 'cool',
			'padding' => array( '28', '0', '28', '0' ),
			'cols' => array( array(
				$b::wid_shortcode( '[es_ansprechpartner members="' . esc_attr( implode( ',', $ansprech_slugs ) ) . '" cta_url="/kontakt/" cta_label="Termin anfragen"]' ),
			) ),
		) );

		// Content Split – Section mit 2 Columns (40/60), native Widgets links
		// damit der Text in Elementor direkt editierbar ist. Mobile-Umbruch
		// auf 1 Column 100% per CSS-Override mit hoher Spezifitaet.
		$s[] = $b::section_native( array(
			'css_classes' => 'es-bereich-detail__split-section',
			'padding' => array( '120', '0', '120', '0' ),
			'gap' => 'wider',
			'column_settings' => array(
				array( '_column_size' => 46, '_inline_size_tablet' => 100, '_inline_size_mobile' => 100 ),
				array( '_column_size' => 54, '_inline_size_tablet' => 100, '_inline_size_mobile' => 100 ),
			),
			'cols' => array(
				array(
					$b::wid_heading( 'Was wir für Sie tun', 'p', 'es-eyebrow' ),
					$b::wid_heading( $d['title'] . '.', 'h2', 'es-split__title' ),
					...array_map( function( $p ) use ( $b ) { return $b::wid_text( '<p>' . esc_html( $p ) . '</p>' ); }, $d['long_copy'] ),
				),
				array(
					$b::wid_heading( 'Beratungsfelder', 'p', 'es-eyebrow' ),
					$b::wid_shortcode( '[es_einzelleistungen beratungsfeld="' . esc_attr( $slug ) . '" columns="2"]', 'es-bereich-detail__split-tiles' ),
				),
			),
		) );

		// Publikationen & Fachbeiträge – 3 neueste getaggt mit Beratungsfeld
		$s[] = $b::section_native( array(
			'variant' => 'warm',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Aus diesem Feld', 'p', 'es-eyebrow' ),
				$b::wid_heading( 'Publikationen & Fachbeiträge', 'h2', 'es-home-news__title' ),
				$b::wid_shortcode( '[es_pub_teaser field="' . esc_attr( $slug ) . '" limit="3"]' ),
				$b::wid_text( '<p style="margin-top:32px;"><a class="es-link" href="/publikationen/?feld=' . esc_attr( $slug ) . '">Alle Publikationen →</a></p>' ),
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
			'headline_html' => 'Vielleicht kennen<br>Sie uns <span class="text-bg-green">schon?</span>',
			'lead' => '<p>Die Energiesozietät gibt einem sehr erfahrenen Team einen agilen und pragmatischen Rahmen. Die Teammitglieder arbeiten seit vielen Jahren zusammen.</p>',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'variant' => 'cool',
			'padding' => array( '40', '0', '80', '0' ),
			'cols' => array( array(
				$b::wid_shortcode( '[es_team columns="2" filter="1"]' ),
			) ),
		) );
		$s[] = $b::split_native( array(
			'eyebrow' => 'Netzwerk',
			'title_html' => 'Kooperationen mit namhaften Beratungshäusern.',
			'paragraphs' => array(
				'<p>Wir sind seit vielen Jahren verlässliche Berater für die Kommunalwirtschaft, Länder, Bund und private Unternehmen. Wir freuen uns auf ein kontinuierlich wachsendes Team, um Ihre Fragestellungen interdisziplinär und ganzheitlich betrachten zu können.</p>',
				'<p>Sollten wir selbst einmal nicht die geeigneten Spezialisten im Team haben, kooperieren wir mit namhaften Beratungsgesellschaften, mit denen wir seit vielen Jahren vertrauensvoll zusammenarbeiten. Sprechen Sie uns gerne direkt an.</p>',
			),
			'variant' => 'warm', 'padding' => 'short',
		) ); // Team: Grid hellgrau → Netzwerk-Split dunkel (weiße Phase entfällt)
		return $s;
	}

	/* ========================== Publikationen ========================== */
	protected static function publikationen_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Publikationen',
			'headline_html' => 'Unsere<br><span class="text-bg-green">Veröffentlichungen.</span>',
			'lead' => '<p>Energie- und Kommunalwirtschaft befinden sich in einem fortwährenden Wandel. Deswegen lernen wir ständig hinzu. Manche unserer neuen Erkenntnisse lohnt es sich zu teilen.</p>',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'variant' => 'cool',
			'padding' => array( '80', '0', '80', '0' ),
			'cols' => array( array(
				$b::wid_text( '<p>Die Herausforderungen unserer Mandanten, die Transformation der öffentlichen Hand und der Energiewirtschaft voranzutreiben, stellen uns tagtäglich vor neue Herausforderungen. Lösungen für die Fragen unserer Zeit zu finden, erfordert es, sich auf seine Erfahrungen und Kompetenzen zu verlassen und erlernte Methoden in neuen Aufgabenstellungen anzuwenden. Gleichzeitig verändert sich der Handlungsrahmen, in dem wir uns bewegen, fortwährend.</p><p>Es gibt also zahlreiche Anlässe, Wissen und Sichtweisen zu teilen, um am Puls der Zeit zu sein.</p>' ),
			) ),
		) );
		$s[] = $b::section_native( array(
			'padding' => array( '40', '0', '140', '0' ),
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
			'variant' => 'ink', 'css_classes' => 'es-hero es-hero--karriere',
			'padding' => array( '100', '0', '140', '0' ),
			'gap' => 'wider',
			'column_settings' => array( array( '_column_size' => 60 ), array( '_column_size' => 40 ) ),
			'cols' => array(
				array(
					$b::wid_heading( 'Karriere', 'p', 'es-eyebrow es-eyebrow--paper' ),
					$b::wid_heading( 'Starte gemeinsam<br>mit uns <span class="text-bg-green">durch!</span>', 'h1', 'es-hero__title' ),
				),
				array(
					$b::wid_text( '<p>Entfalte Dich selbst in einem jungen, schnell wachsenden Beratungsunternehmen.</p>', 'es-hero__lead' ),
				),
			),
		) );

		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '100', '0' ),
			'cols' => array( array(
				$b::wid_heading( 'Offene Positionen', 'h2', 'es-section__title' ),
				$b::wid_shortcode( '[es_karriere columns="1"]' ),
				$b::wid_text( '<p style="margin-top:32px;"><a class="es-link" href="mailto:info@energiesozietaet.de?subject=Initiativbewerbung">Initiativbewerbung →</a></p>' ),
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
			array( '02', 'Interdisziplinarität', 'Kein Silodenken. Wir bringen Recht, Steuern und Unternehmensberatung zusammen – und Dich mittendrin.' ),
			array( '03', 'Persönliche Entwicklung', 'Fortbildung, Promotion, Fachanwaltschaften – wir fördern gezielt und individuell.' ),
		), 'warm' );
		return $s;
	}

	/* ========================== News ========================== */
	protected static function news_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'News',
			'headline_html' => 'Aktuelles aus<br>der <span class="text-bg-green">Kanzlei.</span>',
			'padding' => 'short',
		) );
		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '100', '0' ),
			'cols' => array( array( $b::wid_shortcode( '[es_news_featured limit="9"]' ) ) ),
		) );
		$s[] = $b::gf_quote( array(
			'quote' => 'Wir möchten unsere Mandanten mit exzellenten, pragmatischen Lösungen unterstützen.',
			'role'  => 'Partner',
			'eyebrow' => 'Unser Anspruch',
		) );
		return $s;
	}

	/* ========================== Veranstaltungen ========================== */
	protected static function veranstaltungen_page() {
		$b = 'ESC_Elementor_Builder';
		$s = array();
		$s[] = $b::hero_native( array(
			'eyebrow' => 'Veranstaltungen',
			'headline_html' => 'Aktuelle<br><span class="text-bg-green">Termine.</span>',
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
			'headline_html' => 'Wir freuen uns auf Ihre<br><span class="text-bg-green">Kontaktaufnahme.</span>',
			'lead' => '<p>Bitte zögern Sie nicht, uns anzusprechen.</p>',
			'padding' => 'short',
		) );

		// 2 Spalten: Form links (größer), Standorte rechts (schmaler)
		// Das Formular kommt aus dem [es_kontakt_form]-Shortcode; Felder, Themen
		// und Empfänger sind unter Einstellungen → Kontaktformular pflegbar.

		// Standorte-Modul: Karte + Standortkarten in EINEM Container, über
		// data-loc gekoppelt (Hover Standort ↔ Punkt auf der Karte). Punkt-Labels
		// dauerhaft sichtbar, damit die Zuordnung Karte↔Adresse sofort klar ist.
		$offices = array(
			// Punkt-Positionen: aus echten Koordinaten via Mercator auf die
			// Karten-Boundingbox gerechnet (Punkt sitzt jeweils im Stadtgebiet).
			array( 'slug' => 'duesseldorf', 'city' => 'Düsseldorf', 'lines' => array( 'Roßstraße 92 / Kennedyhaus', '40476 Düsseldorf' ), 'tel' => '+49 211 159232-0', 'hq' => true,  'pos' => 'left:11.1%;top:51.4%' ),
			array( 'slug' => 'hamburg',     'city' => 'Hamburg',    'lines' => array( 'Caffamacherreihe 8', '20355 Hamburg' ),           'tel' => '+49 211 159232-0',     'hq' => false, 'pos' => 'left:45.1%;top:21.4%' ),
			array( 'slug' => 'mannheim',    'city' => 'Mannheim',   'lines' => array( 'Jungbuschstraße 6', '68159 Mannheim' ),           'tel' => '+49 211 159232-0',    'hq' => false, 'pos' => 'left:29%;top:73%' ),
		);
		$map_uri = function_exists( 'get_template_directory_uri' ) ? get_template_directory_uri() : '';

		$std_html  = '<div class="es-kontakt-standorte">';
		$std_html .= '<div class="es-germany-map">';
		$std_html .= '<img src="' . esc_url( $map_uri . '/assets/img/germany.png' ) . '" alt="Unsere Standorte in Deutschland" loading="lazy" />';
		foreach ( $offices as $o ) {
			$std_html .= '<span class="es-germany-dot" data-loc="' . esc_attr( $o['slug'] ) . '" style="' . esc_attr( $o['pos'] ) . '"><span class="es-germany-dot__label">' . esc_html( $o['city'] ) . '</span></span>';
		}
		$std_html .= '</div>';
		// Anfragen-Box VOR den Standort-Cards: Mannheim ist das letzte Element
		// der Spalte, damit das Formular exakt auf dessen Unterkante enden kann.
		$std_html .= '<div class="es-kontakt-general"><div class="es-eyebrow">Allgemeine Anfragen</div><a href="mailto:info@energiesozietaet.de">info@energiesozietaet.de</a><div>Für allgemeine und organisatorische Anfragen.</div></div>';
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
		$std_html .= '</div>';

		$s[] = $b::section_native( array(
			'padding' => array( '80', '0', '140', '0' ),
			'gap' => 'wider',
			'css_classes' => 'es-kontakt-grid-wrap',
			'column_settings' => array( array( '_column_size' => 58 ), array( '_column_size' => 42 ) ),
			'cols' => array(
				array(
					$b::wid_heading( 'Schreiben Sie uns', 'p', 'es-eyebrow' ),
					$b::wid_heading( 'Kontaktformular', 'h2', 'es-split__title' ),
					$b::wid_text( '<p>Sie haben ein konkretes Anliegen oder wissen schon, mit wem Sie bei der Energiesozietät sprechen möchten? Dann nutzen Sie gerne in der Rubrik <a href="/team/">Team</a> die Möglichkeit zur direkten Kontaktaufnahme mit dem jeweiligen Experten.</p><p>Sie möchten uns kennenlernen? Dann hinterlassen Sie uns gerne eine Nachricht mit Ihren Kontaktdaten. Wir melden uns umgehend bei Ihnen, um einen Termin zu vereinbaren.</p><p>Sie wünschen eine Einladung zu einem Event oder sind an Publikationen interessiert? Lassen Sie es uns bitte wissen.</p>' ),
					$b::wid_shortcode( '[es_kontakt_form]' ),
				),
				array(
					$b::wid_heading( 'Unsere Büros', 'p', 'es-eyebrow' ),
					$b::wid_html( $std_html ),
				),
			),
		) );
		return $s;
	}

	protected static function field( $label, $name, $type = 'text' ) {
		$out  = '<div>';
		$out .= '<div style="font-size:var(--es-fs-meta);color:#899092;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:8px;">' . esc_html( $label ) . '</div>';
		if ( 'textarea' === $type ) {
			$out .= '<textarea name="' . esc_attr( $name ) . '" rows="5" style="width:100%;border:0;border-bottom:1px solid #122023;background:transparent;padding:10px 0;font:inherit;font-size:var(--es-fs-body);resize:vertical;outline:none;"></textarea>';
		} else {
			$out .= '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $name ) . '" style="width:100%;border:0;border-bottom:1px solid #122023;background:transparent;padding:10px 0;font:inherit;font-size:var(--es-fs-body);outline:none;" />';
		}
		$out .= '</div>';
		return $out;
	}

}
