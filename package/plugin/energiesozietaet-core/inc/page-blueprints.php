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
	protected static function home( $data ) {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		// 1. Hero — editorial dark, headline mit Accent-Span, claim grid
		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Recht · Steuern · Beratung',
			'headline_html' => 'Expertise trifft <span class="text-bg-green">Leidenschaft.</span>',
			'lead' => 'Wir sind ein <em>junges, innovatives Beratungsunternehmen</em>, dessen langjährig erfahrene Rechtsanwälte, Steuer- und Unternehmensberater sich der <em>Transformation der öffentlichen Hand und der Energiewirtschaft</em> verschrieben haben. Wir arbeiten hoch spezialisiert, fachübergreifend und fokussiert an den Themen unserer Zeit.',
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

		// 2. Intro-Strip — "Unser Anspruch"
		$intro_html  = '<div class="es-wrap" style="padding:100px 0;">';
		$intro_html .= '<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:96px;align-items:start;">';
		$intro_html .= '<div><div class="es-eyebrow">Unser Anspruch</div></div>';
		$intro_html .= '<div>';
		$intro_html .= '<p style="font-size:26px;line-height:1.35;font-weight:400;letter-spacing:-0.015em;color:#0E1A2B;margin:0;">Wir arbeiten <em>hoch spezialisiert</em>, fachübergreifend und fokussiert an den Themen unserer Zeit — unaufgeregt, ergebnisorientiert und mit individuellen Persönlichkeiten.</p>';
		$intro_html .= '<div style="margin-top:36px;display:flex;gap:16px;align-items:center;flex-wrap:wrap;">';
		$intro_html .= '<a class="es-link" href="/philosophie/">Unsere Philosophie</a>';
		$intro_html .= '<span style="color:#8591A3;">·</span>';
		$intro_html .= '<a class="es-link" href="/team/">Unser Team</a>';
		$intro_html .= '</div></div></div></div>';
		$sections[] = $b::section_html( $intro_html, 'warm' );

		// 3. Leistungen — 3 Cards (grounded by shared container + top-border + bottom-rule)
		$services_html  = '<div class="es-wrap" style="padding:140px 0 120px;">';
		$services_html .= '<div style="display:flex;justify-content:space-between;align-items:end;margin-bottom:56px;gap:48px;">';
		$services_html .= '<div>';
		$services_html .= '<div class="es-eyebrow">Leistungen</div>';
		$services_html .= '<h2 style="font-size:clamp(36px,4.4vw,52px);line-height:1.05;font-weight:400;letter-spacing:-0.03em;margin:0;">Interdisziplinäre Beratung.<br/><span style="color:#5A6577;">Drei Felder, ein Gedanke.</span></h2>';
		$services_html .= '<p style="font-size:17px;line-height:1.55;margin-top:20px;color:#5A6577;max-width:620px;">Wir denken Lösungen vom Ende her: strategische, betriebswirtschaftliche, juristische und steuerliche Themen stellen wir in den Gesamtkontext.</p>';
		$services_html .= '</div>';
		$services_html .= '<a class="es-link" href="/leistungen/" style="flex-shrink:0;">Alle Leistungen ansehen →</a>';
		$services_html .= '</div>';
		$services_html .= '<div class="esc-grid esc-grid--cols-3" style="border-top:1px solid #E4E7EC;padding-top:0;">';
		$cards = array(
			array( '01', 'Rechtsberatung',       '/rechtsberatung/',       'Energierecht, Vergaberecht, Regulierung, M&A, Gesellschaftsrecht. Wir stellen juristische Lösungen in den Gesamtkontext.' ),
			array( '02', 'Steuerberatung',       '/steuerberatung/',       'Fortlaufende Steuerberatung, Gestaltungsberatung und herausfordernde Neustrukturierungen für Versorger und Kommunen.' ),
			array( '03', 'Unternehmensberatung', '/unternehmensberatung/', 'Wir navigieren Sie durch strategische, wirtschaftliche und finanzielle Fragestellungen der Transformation.' ),
		);
		foreach ( $cards as $c ) {
			$services_html .= '<a class="esc-card" href="' . esc_url( $c[2] ) . '" style="border-top:0;min-height:380px;padding:36px;">';
			$services_html .= '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:100px;"><div style="font-family:var(--es-font-mono);font-size:12px;color:#8591A3;letter-spacing:0.1em;">' . $c[0] . ' / 03</div><div style="width:6px;height:6px;border-radius:999px;background:#95D708;"></div></div>';
			$services_html .= '<h3 style="font-size:28px;line-height:1.1;font-weight:500;letter-spacing:-0.02em;margin-bottom:16px;">' . esc_html( $c[1] ) . '</h3>';
			$services_html .= '<p style="font-size:15px;color:#5A6577;line-height:1.55;margin-bottom:32px;">' . esc_html( $c[3] ) . '</p>';
			$services_html .= '<span class="es-link" style="margin-top:auto;">Mehr erfahren →</span>';
			$services_html .= '</a>';
		}
		$services_html .= '</div></div>';
		$sections[] = $b::section_html( $services_html );

		// 4. GF Quote (mit Portrait) — "Unser Anspruch"
		$sections[] = $b::gf_quote( array(
			'quote' => 'Wir wollen mit unserer langjährigen Beratungs- und Netzwerkerfahrung unsere Mandantschaft bei den anstehenden Transformationen begleiten — und mit den individuellen Persönlichkeiten unseres Teams Mehrwert bieten.',
		) );

		// 5. News teaser — 3 Karten mit Beitragsbild
		$news_html  = '<div class="es-wrap" style="padding:140px 0;">';
		$news_html .= '<div style="display:flex;justify-content:space-between;align-items:end;margin-bottom:56px;gap:48px;flex-wrap:wrap;">';
		$news_html .= '<div><div class="es-eyebrow">Aktuelles · Neu</div><h2 style="font-size:clamp(36px,4.2vw,52px);line-height:1.05;font-weight:400;letter-spacing:-0.03em;margin:0;">Fachbeiträge.</h2></div>';
		$news_html .= '<a class="es-link" href="/news/">Alle Beiträge ansehen →</a></div>';
		$news_html .= '[es_news limit="3" columns="3"]';
		$news_html .= '</div>';
		$sections[] = $b::section_html( $news_html );

		// 6. CTA dark
		$sections[] = $b::cta_dark( array(
			'title_html' => 'Sprechen Sie mit uns.',
			'sub' => 'Unaufgeregt, direkt, fachlich.',
			'buttons' => array(
				array( 'Termin vereinbaren', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
		) );

		return $sections;
	}


	/* ==========================================================================
	 * Philosophie
	 * ========================================================================== */
	protected static function philosophie() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Philosophie',
			'headline_html' => 'Transformation ist eine<br/><span class="text-bg-green">Mammutaufgabe.</span>',
			'lead' => 'Dafür braucht es viele. Das schafft niemand allein. Wir — das Team der Energiesozietät — verstehen uns als einer der vielen.',
			'padding' => 'tall',
		) );

		// Fokussiert · Ergebnisorientiert · Kreativ — 3 Pillar Cards auf warm
		$pillars_html  = '<div class="es-wrap" style="padding:140px 0;">';
		$pillars_html .= '<div style="display:grid;grid-template-columns:1fr 2fr;gap:96px;margin-bottom:120px;">';
		$pillars_html .= '<div><div class="es-eyebrow">Wie wir arbeiten</div>';
		$pillars_html .= '<h2 style="font-size:clamp(32px,4vw,52px);line-height:1.05;font-weight:300;letter-spacing:-0.035em;margin-top:24px;">Fokussiert.<br/>Ergebnis&shy;orientiert.<br/><span class="text-bg-green">Kreativ.</span></h2></div>';
		$pillars_html .= '<div style="padding-top:32px;">';
		$pillars_html .= '<p style="font-size:20px;line-height:1.65;color:#0E1A2B;margin-bottom:28px;font-weight:300;">Wir sind Experten. Wir arbeiten hoch spezialisiert. Wir entwickeln ganzheitliche Lösungen, die strategische, betriebs- und finanzwirtschaftliche, juristische sowie steuerliche Fragen immer im Gesamtkontext betrachten.</p>';
		$pillars_html .= '<p style="font-size:17px;line-height:1.7;color:#5A6577;">Das erfordert interdisziplinäres Denken, langjährige Erfahrung und die Bereitschaft, neu zu denken — gerade dort, wo sich Energiemärkte, regulatorische Rahmen und politische Entscheidungsprozesse gleichzeitig verändern.</p>';
		$pillars_html .= '</div></div>';
		$pillars_html .= '<div class="esc-grid esc-grid--cols-3" style="gap:16px;">';
		$pillars = array(
			array( '01', 'Fokussiert', 'Wir arbeiten hoch spezialisiert an den Themen unserer Zeit. Kein Bauchladen, sondern vertiefte Expertise dort, wo die Transformation entschieden wird: in der Energieversorgung, in der Kommunalwirtschaft, beim Bund und bei den Ländern.' ),
			array( '02', 'Ergebnisorientiert', 'Wir denken Lösungen vom Ende her. Strategische, technische, betriebs- und finanzwirtschaftliche, juristische sowie steuerliche Lösungen stellen wir in den Gesamtkontext — damit unsere Mandanten tragfähige Entscheidungen treffen können.' ),
			array( '03', 'Kreativ', 'Neue Fragestellungen verlangen neue Antworten. Wir verbinden langjährige Erfahrung aus Big-Four-Gesellschaften, spezialisierten Kanzleien und der Industrie mit einem agilen, pragmatischen Arbeitsrahmen.' ),
		);
		foreach ( $pillars as $p ) {
			$pillars_html .= '<div style="padding:48px 36px;background:#FFFFFF;border:1px solid #E4E7EC;">';
			$pillars_html .= '<div style="font-family:var(--es-font-mono);font-size:11px;color:#95D708;letter-spacing:0.16em;margin-bottom:32px;">' . $p[0] . ' —</div>';
			$pillars_html .= '<h3 style="font-size:30px;line-height:1.15;font-weight:400;letter-spacing:-0.025em;margin-bottom:20px;">' . esc_html( $p[1] ) . '</h3>';
			$pillars_html .= '<p style="font-size:15px;color:#5A6577;line-height:1.65;margin:0;">' . esc_html( $p[2] ) . '</p>';
			$pillars_html .= '</div>';
		}
		$pillars_html .= '</div></div>';
		$sections[] = $b::section_html( $pillars_html, 'warm' );

		// Pullquote
		$sections[] = $b::pullquote(
			'„Unsere Mandanten bewegen sich in einem <span class="text-bg-green">hochkomplexen</span> und <span class="text-bg-green">sich kontinuierlich wandelnden</span> Umfeld. In diesem Umfeld treffen sie Entscheidungen, die ihre Unternehmen und Kommunen über Jahrzehnte prägen werden."',
			'Unsere Überzeugung'
		);

		// Interdisziplinär — 4 Cards (Ingenieure/Kaufleute/Rechtsanwälte/Steuerberater)
		$intd_html  = '<div class="es-wrap" style="padding:140px 0;">';
		$intd_html .= '<div class="es-eyebrow">Interdisziplinär</div>';
		$intd_html .= '<h2 style="font-size:clamp(32px,4vw,52px);line-height:1.05;font-weight:400;letter-spacing:-0.03em;margin:0 0 24px;max-width:720px;">Ein Team aus vier Perspektiven.</h2>';
		$intd_html .= '<p style="font-size:17px;line-height:1.55;color:#5A6577;max-width:720px;margin:0 0 64px;">Ingenieure, Kaufleute, Rechtsanwälte und Steuerberater erarbeiten mit unseren Mandanten ganzheitliche, tragfähige Lösungen als Grundlage für gut abgewogene Entscheidungen.</p>';
		$intd_html .= '<div class="esc-grid esc-grid--cols-4" style="gap:16px;">';
		$perspective = array(
			array( 'Ingenieure', 'Technische Lösungen und Machbarkeit im Energiesystem.' ),
			array( 'Kaufleute', 'Betriebs- und finanzwirtschaftliche Bewertung und Strukturierung.' ),
			array( 'Rechtsanwälte', 'Juristische Gestaltung im regulatorischen Gesamtkontext.' ),
			array( 'Steuerberater', 'Steuerliche Struktur, Compliance und Gestaltungsspielräume.' ),
		);
		foreach ( $perspective as $m ) {
			$intd_html .= '<div style="padding:36px 28px;background:#FFFFFF;border:1px solid #E4E7EC;">';
			$intd_html .= '<div style="width:4px;height:28px;background:#95D708;margin-bottom:24px;"></div>';
			$intd_html .= '<div style="font-size:20px;font-weight:500;letter-spacing:-0.015em;margin-bottom:10px;">' . esc_html( $m[0] ) . '</div>';
			$intd_html .= '<div style="font-size:13px;color:#5A6577;line-height:1.6;">' . esc_html( $m[1] ) . '</div>';
			$intd_html .= '</div>';
		}
		$intd_html .= '</div></div>';
		$sections[] = $b::section_html( $intd_html, 'warm' );

		// Mandantschaft
		$mand_html  = '<div class="es-wrap" style="padding:140px 0;">';
		$mand_html .= '<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:96px;align-items:start;">';
		$mand_html .= '<div><div class="es-eyebrow">Mandantschaft</div>';
		$mand_html .= '<h2 style="font-size:clamp(32px,4vw,52px);line-height:1.05;font-weight:300;letter-spacing:-0.035em;margin-top:24px;">Wen wir beraten.</h2></div>';
		$mand_html .= '<div><p style="font-size:19px;line-height:1.65;color:#0E1A2B;margin:0 0 40px;">Mit unserem Beratungsangebot adressieren wir Energieversorgungsunternehmen, Bund, Länder und Kommunen sowie deren Einrichtungen und Unternehmen. Hinzu kommen private und öffentliche Infrastrukturdienstleister und Investoren.</p>';
		$mand_html .= '<div style="display:grid;grid-template-columns:1fr 1fr;border-top:1px solid #E4E7EC;">';
		$mandanten = array( 'Energieversorgungsunternehmen', 'Stadtwerke & kommunale Unternehmen', 'Bund & Länder', 'Kommunen & Einrichtungen der öffentlichen Hand', 'Infrastrukturdienstleister', 'Investoren' );
		foreach ( $mandanten as $i => $m ) {
			$right = ( $i % 2 === 0 ) ? 'border-right:1px solid #E4E7EC;padding-right:32px;' : 'padding-left:32px;';
			$mand_html .= '<div style="padding:22px 0;border-bottom:1px solid #E4E7EC;' . $right . 'font-size:17px;font-weight:400;letter-spacing:-0.01em;display:flex;align-items:center;gap:16px;">';
			$mand_html .= '<span style="font-family:var(--es-font-mono);font-size:11px;color:#8591A3;letter-spacing:0.08em;">' . str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) . '</span>' . esc_html( $m );
			$mand_html .= '</div>';
		}
		$mand_html .= '</div></div></div></div>';
		$sections[] = $b::section_html( $mand_html );

		// Politikberatung — dunkel
		$pol_html  = '<div class="es-wrap" style="padding:140px 0;">';
		$pol_html .= '<div style="display:grid;grid-template-columns:1fr 1.4fr;gap:96px;">';
		$pol_html .= '<div><div class="es-eyebrow es-eyebrow--paper">Politikberatung</div>';
		$pol_html .= '<h2 style="font-size:clamp(32px,4vw,52px);line-height:1.05;font-weight:300;letter-spacing:-0.035em;color:#FFFFFF;margin-top:24px;">Lösungen, die im<br/>politischen Prozess<br/><span class="text-bg-green">Akzeptanz finden.</span></h2></div>';
		$pol_html .= '<div style="padding-top:24px;">';
		$pol_html .= '<p style="font-size:20px;line-height:1.65;color:rgba(255,255,255,0.82);margin-bottom:28px;font-weight:300;">Wir haben ein ausgeprägtes Verständnis von Energiemärkten, Verwaltungsorganisationen und ihren politischen Entscheidungsprozessen. Politikberatung ist integraler Bestandteil unserer Tätigkeit, um die Projekte unserer Mandanten zum Erfolg zu führen.</p>';
		$pol_html .= '<p style="font-size:16px;line-height:1.7;color:rgba(255,255,255,0.6);">Wir entwickeln Konzepte für die Kommunikation von Lösungen im politischen Entscheidungsprozess — mit dem Ziel, bestmögliche Lösungen zu präsentieren, die eine breite Akzeptanz finden.</p>';
		$pol_html .= '</div></div></div>';
		$sections[] = $b::section_html( $pol_html, 'ink' );

		$sections[] = $b::cta_dark( array(
			'eyebrow' => 'Kontakt',
			'title_html' => 'Haben wir Ihr Interesse geweckt?',
			'sub' => 'Möchten Sie uns kennenlernen?',
			'buttons' => array(
				array( 'Kontakt aufnehmen', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
		) );

		return $sections;
	}


	/* ==========================================================================
	 * Leistungen (Übersicht) + Beratungsfeld-Detail
	 * ========================================================================== */
	protected static function leistungen( $data ) {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Leistungen',
			'headline_html' => 'Interdisziplinäre<br/><span class="text-bg-green">Beratung.</span>',
			'lead' => 'Wir denken Lösungen vom Ende her: strategische, technische, betriebs- und finanzwirtschaftliche, juristische und steuerliche Lösungen stellen wir dafür in den Gesamtkontext.',
			'padding' => 'short',
		) );

		// Kontext-Strip: Experten für Ihre Beratung
		$ctx_html  = '<div class="es-wrap" style="padding:100px 0;border-bottom:1px solid #E4E7EC;">';
		$ctx_html .= '<div style="display:grid;grid-template-columns:1fr 2fr;gap:96px;">';
		$ctx_html .= '<div><div class="es-eyebrow">Unser Anspruch</div>';
		$ctx_html .= '<h2 style="font-size:clamp(32px,3.6vw,42px);line-height:1.1;font-weight:300;letter-spacing:-0.03em;margin-top:24px;">Experten für Ihre Beratung.</h2></div>';
		$ctx_html .= '<div style="padding-top:16px;">';
		$ctx_html .= '<p style="font-size:18px;line-height:1.7;color:#0E1A2B;margin-bottom:24px;">Die Energiesozietät ist seit ihrer Gründung Ende 2023 dynamisch gewachsen und verfügt heute über ein Team mit großem Erfahrungsschatz. Viele Teammitglieder haben jahrelang erfolgreich in Big-Four-Gesellschaften, spezialisierten Beratungsgesellschaften und Kanzleien gearbeitet.</p>';
		$ctx_html .= '<p style="font-size:16px;line-height:1.7;color:#5A6577;">Sollten wir selbst einmal nicht die geeigneten Spezialisten im Team haben, um Ihre Fragen bestmöglich zu beantworten, kooperieren wir mit namhaften Beratungsgesellschaften, mit denen wir seit vielen Jahren vertrauensvoll zusammenarbeiten.</p>';
		$ctx_html .= '</div></div></div>';
		$sections[] = $b::section_html( $ctx_html, 'warm' );

		// 3 Bereiche als abgesetzte Sections mit eigener Einleitung
		$bf = self::beratungsfelder();
		$i = 0;
		foreach ( array( 'rechtsberatung', 'steuerberatung', 'unternehmensberatung' ) as $slug ) {
			$cfg = $bf[ $slug ];
			$sections[] = $b::bereich( array(
				'n' => $cfg['n'],
				'title' => $cfg['title'],
				'sub' => $cfg['sub'],
				'lede' => $cfg['lede'],
				'link' => '/' . $slug . '/',
				'topics' => $cfg['topics'],
				'stripe' => ( $i % 2 === 0 ) ? 'odd' : 'even',
			) );
			$i++;
		}

		$sections[] = $b::cta_dark( array(
			'title_html' => 'Haben wir Ihr Interesse geweckt?',
			'sub' => 'Möchten Sie uns kennenlernen?',
			'buttons' => array(
				array( 'Kontakt aufnehmen', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
		) );

		return $sections;
	}

	protected static function beratungsfeld_detail( $slug, $data ) {
		$b = 'ESC_Elementor_Builder';
		$bf = self::beratungsfelder();
		$d = $bf[ $slug ];
		$sections = array();

		// Hero mit Breadcrumb
		$hero_html  = '<div class="es-wrap" style="padding:80px 0 110px;">';
		$hero_html .= '<div style="font-size:12px;color:rgba(255,255,255,0.5);margin-bottom:40px;font-family:var(--es-font-mono);letter-spacing:0.06em;"><a href="/leistungen/" style="color:inherit;">Leistungen</a>  /  ' . esc_html( $d['title'] ) . '</div>';
		$hero_html .= '<div style="display:grid;grid-template-columns:1.3fr 1fr;gap:96px;align-items:end;">';
		$hero_html .= '<div>';
		$hero_html .= '<div class="es-eyebrow" style="color:#95D708;">' . esc_html( $d['n'] ) . ' · ' . esc_html( $d['title'] ) . '</div>';
		$hero_html .= '<h1 style="font-size:clamp(44px,5.6vw,72px);line-height:1.02;font-weight:300;letter-spacing:-0.035em;margin:0;">' . esc_html( $d['long_title'] ) . '</h1>';
		$hero_html .= '</div>';
		$hero_html .= '<p style="font-size:17px;line-height:1.55;color:rgba(255,255,255,0.72);margin:0;">' . esc_html( $d['lede'] ) . '</p>';
		$hero_html .= '</div></div>';
		$sections[] = $b::section_html( $hero_html, 'ink' );

		// Ansprechpartner-Balken
		$ap_html  = '<div class="es-wrap" style="padding:36px 0;border-bottom:1px solid #E4E7EC;">';
		$ap_html .= '<div style="display:grid;grid-template-columns:auto 1fr auto;gap:48px;align-items:center;">';
		$ap_html .= '<div class="es-eyebrow" style="margin:0;">Ihre Ansprechpartner</div>';
		$ap_html .= '<div style="display:flex;gap:40px;align-items:center;flex-wrap:wrap;">';
		foreach ( $d['ansprechpartner'] as $p ) {
			$ap_html .= '<a href="/teammitglied/' . esc_attr( $p[0] ) . '/" style="display:flex;align-items:center;gap:14px;color:#0E1A2B;">';
			$ap_html .= '<div style="width:52px;height:52px;border-radius:999px;overflow:hidden;background:#F6F4EF;flex-shrink:0;">[es_team_photo slug="' . esc_attr( $p[0] ) . '" size=52]</div>';
			$ap_html .= '<div><div style="font-size:15px;font-weight:500;">' . esc_html( $p[1] ) . '</div><div style="font-size:12px;color:#5A6577;margin-top:2px;">' . esc_html( $p[2] ) . '</div></div>';
			$ap_html .= '</a>';
		}
		$ap_html .= '</div>';
		$ap_html .= '<a class="es-btn es-btn--primary" style="padding:12px 18px;font-size:13px;" href="/kontakt/">Termin anfragen →</a>';
		$ap_html .= '</div></div>';
		$sections[] = $b::section_html( $ap_html, 'warm' );

		// Content split — Text links, Topic-Grid rechts
		$cs_html  = '<div class="es-wrap" style="padding:140px 0;">';
		$cs_html .= '<div style="display:grid;grid-template-columns:1fr 1.2fr;gap:96px;align-items:start;">';
		$cs_html .= '<div style="position:sticky;top:40px;">';
		$cs_html .= '<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">';
		$cs_html .= '<div style="aspect-ratio:3/4;background:#F6F4EF;overflow:hidden;">[es_team_photo slug="' . esc_attr( $d['ansprechpartner'][0][0] ) . '" size=400]</div>';
		$cs_html .= '<div style="aspect-ratio:3/4;background:#F6F4EF;overflow:hidden;margin-top:48px;">[es_team_photo slug="' . esc_attr( $d['ansprechpartner'][1][0] ) . '" size=400]</div>';
		$cs_html .= '</div></div>';
		$cs_html .= '<div>';
		$cs_html .= '<div class="es-eyebrow" style="margin-bottom:20px;">Was wir für Sie tun</div>';
		$cs_html .= '<h2 style="font-size:clamp(28px,3.2vw,40px);line-height:1.15;font-weight:400;letter-spacing:-0.025em;margin:0 0 32px;">' . esc_html( $d['long_title'] ) . '</h2>';
		foreach ( $d['long_copy'] as $j => $p ) {
			$mb = $j === count( $d['long_copy'] ) - 1 ? '48' : '24';
			$cs_html .= '<p style="font-size:17px;line-height:1.65;color:#5A6577;margin:0 0 ' . $mb . 'px;">' . esc_html( $p ) . '</p>';
		}
		$cs_html .= '<div class="es-eyebrow" style="margin-bottom:20px;">Einzelleistungen · ' . esc_html( $d['title'] ) . '</div>';
		$cs_html .= '<div class="es-bereich__topics" style="grid-template-columns:1fr 1fr;">';
		$cs_html .= '[es_einzelleistungen beratungsfeld="' . esc_attr( $slug ) . '" columns="2"]';
		$cs_html .= '</div></div></div></div>';
		$sections[] = $b::section_html( $cs_html );

		$sections[] = $b::cta_dark( array(
			'title_html' => 'Ihre Fragestellung — unsere Expertise.',
			'sub' => 'Wir freuen uns auf Ihre Anfrage.',
		) );

		return $sections;
	}


	/* ==========================================================================
	 * Team-Übersicht, Publikationen, Karriere, News, Veranstaltungen, Kontakt
	 * ========================================================================== */

	protected static function team_page() {
		$b = 'ESC_Elementor_Builder';
		$sections = array();

		$sections[] = $b::hero_editorial( array(
			'eyebrow' => 'Team',
			'headline_html' => 'Beratung mit<br/>Gesicht.',
			'lead' => 'In der Energiesozietät hat jahrzehntelange Erfahrung einen agilen und pragmatischen Rahmen bekommen.',
			'padding' => 'short',
		) );

		// Filter-Strip (statisch, zunächst nur "Alle")
		$filter_html  = '<div class="es-wrap" style="padding:20px 0;border-bottom:1px solid #E4E7EC;">';
		$filter_html .= '<div style="display:flex;align-items:center;gap:28px;">';
		$filter_html .= '<div class="es-eyebrow" style="margin:0;">Filter</div>';
		$filter_html .= '<div style="display:flex;gap:6px;">';
		foreach ( array( array( 'Alle', '/team/', true ), array( 'Rechtsberatung', '/rechtsberatung/', false ), array( 'Steuerberatung', '/steuerberatung/', false ), array( 'Unternehmensberatung', '/unternehmensberatung/', false ) ) as $f ) {
			$active = $f[2];
			$filter_html .= '<a href="' . esc_url( $f[1] ) . '" style="padding:8px 14px;font-size:13px;border-radius:2px;border:1px solid ' . ( $active ? '#0E1A2B' : '#E4E7EC' ) . ';background:' . ( $active ? '#0E1A2B' : 'transparent' ) . ';color:' . ( $active ? '#FFFFFF' : '#0E1A2B' ) . ';">' . esc_html( $f[0] ) . '</a>';
		}
		$filter_html .= '</div>';
		$filter_html .= '<div style="margin-left:auto;font-size:13px;color:#5A6577;">Alle Berufsträger &amp; Mitarbeiter</div>';
		$filter_html .= '</div></div>';
		$sections[] = $b::section_html( $filter_html );

		// Grid
		$grid_html = '<div class="es-wrap" style="padding:80px 0 80px;">[es_team columns="4"]</div>';
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
