<?php
/**
 * Meta boxes for CPTs.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_MetaBoxes {

	const FIELDS = array(
		'rechtsberatung'       => 'Rechtsberatung',
		'steuerberatung'       => 'Steuerberatung',
		'unternehmensberatung' => 'Unternehmensberatung',
		'kanzlei'              => 'Kanzlei',
		'management'           => 'Management / Büroleitung',
	);

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
		add_action( 'save_post',      array( __CLASS__, 'save' ), 10, 2 );
	}

	public static function register() {
		add_meta_box( 'esc_team',          'Teammitglied-Details',    array( __CLASS__, 'box_team' ),         'es_team',           'normal', 'default' );
		add_meta_box( 'esc_einzel',        'Einzelleistung-Details',  array( __CLASS__, 'box_einzel' ),       'es_einzelleistung', 'normal', 'default' );
		add_meta_box( 'esc_karriere',      'Karriere-Details',        array( __CLASS__, 'box_karriere' ),     'es_karriere',       'normal', 'default' );
		add_meta_box( 'esc_veranstaltung', 'Veranstaltungs-Details',  array( __CLASS__, 'box_veranst' ),      'es_veranstaltung',  'normal', 'default' );
		add_meta_box( 'esc_publikation',   'Publikations-Details',    array( __CLASS__, 'box_publikation' ),  'es_publikation',    'normal', 'default' );
		add_meta_box( 'esc_news',          'Beitrags-Details',        array( __CLASS__, 'box_news' ),         'es_news',           'normal', 'default' );
		// Englische Fassung – für alle Inhaltstypen
		foreach ( array( 'es_news', 'es_team', 'es_einzelleistung', 'es_karriere', 'es_veranstaltung', 'es_publikation' ) as $pt ) {
			add_meta_box( 'esc_lang_en', 'Englische Fassung (EN)', array( __CLASS__, 'box_lang_en' ), $pt, 'normal', 'default' );
		}
	}

	/** Textarea, die zeilenweise in ein Array-Meta gespeichert wird (EN-Varianten). */
	protected static function lines_field( $label, $name, $post_id, $meta_key, $rows = 5 ) {
		$val = get_post_meta( $post_id, $meta_key, true );
		$txt = is_array( $val ) ? implode( "\n", $val ) : (string) $val;
		echo '<p><label><strong>' . esc_html( $label ) . '</strong></label>';
		echo '<textarea name="' . esc_attr( $name ) . '" rows="' . (int) $rows . '" style="width:100%;">' . esc_textarea( $txt ) . '</textarea></p>';
	}

	/**
	 * EN-Felder je Inhaltstyp. Leere Felder = deutsche Fassung wird angezeigt
	 * (Fallback); News ohne „Titel (EN)" erscheinen nicht in den englischen Listen.
	 */
	public static function box_lang_en( $post ) {
		self::nonce();
		$pt = $post->post_type;
		echo '<p class="description" style="font-style:normal;margin-top:0;">Leere Felder fallen im englischen Bereich (<code>/en/…</code>) auf die deutsche Fassung zurück.</p>';

		if ( 'es_team' === $pt ) {
			echo '<p class="description" style="font-style:normal;">Name und Kontaktdaten bleiben in beiden Sprachen gleich.</p>';
			self::field( 'Rolle / Position (EN)', 'es_role_en', get_post_meta( $post->ID, 'es_role_en', true ) );
		} else {
			self::field( 'Titel (EN)', 'es_title_en', get_post_meta( $post->ID, 'es_title_en', true ) );
		}

		if ( in_array( $pt, array( 'es_news', 'es_team', 'es_einzelleistung', 'es_karriere', 'es_veranstaltung' ), true ) ) {
			$content_label = array(
				'es_news'           => 'Beitragstext (EN)',
				'es_team'           => 'Kurzvita (EN)',
				'es_einzelleistung' => 'Beschreibung (EN)',
				'es_karriere'       => 'Über die Rolle (EN)',
				'es_veranstaltung'  => 'Beschreibung (EN)',
			);
			echo '<p style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;"><label for="esc_es_content_en"><strong>' . esc_html( $content_label[ $pt ] ) . '</strong></label></p>';
			wp_editor( (string) get_post_meta( $post->ID, 'es_content_en', true ), 'esc_es_content_en', array(
				'textarea_name' => 'es_content_en',
				'textarea_rows' => 10,
				'media_buttons' => false,
			) );
			echo '<div style="height:14px;"></div>';
		}

		switch ( $pt ) {
			case 'es_news':
				self::field( 'Teaser / Auszug (EN, optional)', 'es_excerpt_en', get_post_meta( $post->ID, 'es_excerpt_en', true ), 'textarea' );
				break;
			case 'es_team':
				self::field( 'Erweiterte Vita (EN)', 'es_more_bio_en', get_post_meta( $post->ID, 'es_more_bio_en', true ), 'textarea' );
				self::lines_field( 'Ausgewählte Schwerpunkte (EN) – ein Schwerpunkt pro Zeile', 'es_focus_areas_en_raw', $post->ID, 'es_focus_areas_en', 6 );
				$career_en = get_post_meta( $post->ID, 'es_career_en', true );
				$career_en_lines = array();
				if ( is_array( $career_en ) ) {
					foreach ( $career_en as $c ) {
						$career_en_lines[] = trim( ( is_array( $c ) ? (string) ( $c['when'] ?? '' ) : '' ) . ' ||| ' . ( is_array( $c ) ? (string) ( $c['what'] ?? '' ) : (string) $c ) );
					}
				}
				echo '<p><label><strong>Werdegang (EN) – eine Station pro Zeile, Format <code>Zeit ||| Beschreibung</code></strong></label>';
				echo '<textarea name="es_career_en_raw" rows="6" style="width:100%;">' . esc_textarea( implode( "\n", $career_en_lines ) ) . '</textarea></p>';
				break;
			case 'es_einzelleistung':
				self::field( 'Untertitel / Hero (EN)', 'es_subtitle_en', get_post_meta( $post->ID, 'es_subtitle_en', true ), 'textarea' );
				self::lines_field( 'Schwerpunkte unserer Beratung (EN) – eine Zeile = ein Punkt', 'es_bullets_en_raw', $post->ID, 'es_bullets_en', 6 );
				self::accordion_repeater(
					'es_acc_en',
					get_post_meta( $post->ID, 'es_accordion_en', true ),
					'Aufklapp-Rubriken (EN)',
					'Englische Fassung der Rubriken (Bedienung wie im deutschen Kasten). Leer = die Seite zeigt die deutschen Rubriken.'
				);
				self::field( 'Abschluss-Absatz (EN)', 'es_closing_en', get_post_meta( $post->ID, 'es_closing_en', true ), 'textarea' );
				break;
			case 'es_karriere':
				self::field( 'Rolle / Titel-Kürzel (EN)', 'es_department_en', get_post_meta( $post->ID, 'es_department_en', true ) );
				self::field( 'Anstellungsart (EN, z. B. Full-time)', 'es_employment_type_en', get_post_meta( $post->ID, 'es_employment_type_en', true ) );
				self::lines_field( 'Aufgaben (EN) – eine pro Zeile', 'es_tasks_en_raw', $post->ID, 'es_tasks_en', 5 );
				self::lines_field( 'Profil (EN) – eine Anforderung pro Zeile', 'es_profile_en_raw', $post->ID, 'es_profile_en', 5 );
				self::lines_field( 'Was wir Dir bieten (EN) – eine pro Zeile', 'es_offer_en_raw', $post->ID, 'es_offer_en', 5 );
				self::field( 'Abschlusstext (EN)', 'es_closing_en', get_post_meta( $post->ID, 'es_closing_en', true ), 'textarea' );
				break;
			case 'es_veranstaltung':
				self::field( 'Ort (EN, z. B. Online)', 'es_location_en', get_post_meta( $post->ID, 'es_location_en', true ) );
				self::field( 'Art (EN, z. B. Seminar)', 'es_kind_en', get_post_meta( $post->ID, 'es_kind_en', true ) );
				break;
			case 'es_publikation':
				self::field( 'Beschreibung (EN) – Pendant zum Beschreibungstext im Editor oben', 'es_content_en', get_post_meta( $post->ID, 'es_content_en', true ), 'textarea' );
				self::field( 'Kategorie (EN, z. B. Article, Book)', 'es_cat_en', get_post_meta( $post->ID, 'es_cat_en', true ) );
				break;
		}

		self::field( 'URL-Kürzel (EN, optional – nur Kleinbuchstaben und Bindestriche)', 'es_slug_en', get_post_meta( $post->ID, 'es_slug_en', true ) );
	}

	protected static function nonce() {
		wp_nonce_field( 'esc_meta', 'esc_meta_nonce' );
	}

	protected static function field( $label, $name, $value, $type = 'text', $opts = array() ) {
		$id = 'esc_' . $name;
		echo '<p style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">';
		echo '<label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label>';
		if ( $type === 'textarea' ) {
			echo '<textarea id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" rows="4" style="width:100%;">' . esc_textarea( $value ) . '</textarea>';
		} elseif ( $type === 'date' ) {
			echo '<input type="date" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
		} elseif ( $type === 'url' ) {
			echo '<input type="url" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" />';
		} elseif ( $type === 'select' ) {
			echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" style="max-width:360px;">';
			echo '<option value="">– bitte wählen –</option>';
			foreach ( (array) ( $opts['options'] ?? array() ) as $k => $v ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $value, $k, false ) . '>' . esc_html( $v ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" style="width:100%;" />';
		}
		echo '</p>';
	}

	/** Returns Team posts as [post_id => "Vorname Nachname (Rolle)"] for selects. */
	protected static function team_options() {
		$q = new WP_Query( array( 'post_type' => 'es_team', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC', 'fields' => 'all' ) );
		$out = array();
		foreach ( $q->posts as $p ) {
			$role = (string) get_post_meta( $p->ID, 'es_role', true );
			$out[ $p->ID ] = $p->post_title . ( $role ? ' · ' . $role : '' );
		}
		return $out;
	}

	public static function box_team( $post ) {
		self::nonce();
		self::field( 'Rolle / Position', 'es_role',     get_post_meta( $post->ID, 'es_role', true ) );
		self::field(
			'Geschlecht (Anrede „Ihr/e Ansprechpartner/in")',
			'es_gender',
			get_post_meta( $post->ID, 'es_gender', true ),
			'select',
			array( 'options' => array(
				'm' => 'männlich – „Ihr Ansprechpartner"',
				'w' => 'weiblich – „Ihre Ansprechpartnerin"',
				'd' => 'divers – „Ihr:e Ansprechpartner:in"',
			) )
		);
		self::field( 'E-Mail',           'es_email',    get_post_meta( $post->ID, 'es_email', true ) );
		self::field( 'Telefon',          'es_phone',    get_post_meta( $post->ID, 'es_phone', true ) );
		self::field( 'LinkedIn-URL',     'es_linkedin', get_post_meta( $post->ID, 'es_linkedin', true ), 'url' );
		self::field( 'Standort',         'es_location', get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Nachname für Sortierung (optional)', 'es_sort_name', get_post_meta( $post->ID, 'es_sort_name', true ) );
		echo '<p class="description" style="margin:-8px 0 16px;font-style:normal;">Leer lassen – dann wird automatisch nach dem letzten Namensteil sortiert (z.&nbsp;B. „Otto" bei „Prof. Dr. Sven-Joachim Otto"). Nur bei mehrteiligen Nachnamen (z.&nbsp;B. „van der Berg") hier den Sortier-Nachnamen eintragen.</p>';
		self::field(
			'Beratungsfeld',
			'es_field',
			get_post_meta( $post->ID, 'es_field', true ),
			'select',
			array( 'options' => self::FIELDS )
		);
		self::field( 'Erweiterte Vita (optional)', 'es_more_bio', get_post_meta( $post->ID, 'es_more_bio', true ), 'textarea' );

		// Schwerpunkte – eine Zeile pro Stichpunkt
		$focus = get_post_meta( $post->ID, 'es_focus_areas', true );
		$focus_lines = array();
		if ( is_array( $focus ) ) {
			foreach ( $focus as $f ) {
				if ( is_array( $f ) ) {
					$focus_lines[] = (string) ( $f['title'] ?? '' );
				} else {
					$focus_lines[] = (string) $f;
				}
			}
		}
		echo '<p><label><strong>Ausgewählte Schwerpunkte</strong><br /><span style="color:#667;font-weight:400;">Ein Schwerpunkt pro Zeile.</span></label>';
		echo '<textarea name="es_focus_areas_raw" rows="6" style="width:100%;">' . esc_textarea( implode( "\n", $focus_lines ) ) . '</textarea></p>';

		// Werdegang – Zeile je Station: "Zeit ||| Text"
		$career = get_post_meta( $post->ID, 'es_career', true );
		$career_lines = array();
		if ( is_array( $career ) ) {
			foreach ( $career as $c ) {
				$when = is_array( $c ) ? (string) ( $c['when'] ?? '' ) : '';
				$what = is_array( $c ) ? (string) ( $c['what'] ?? '' ) : (string) $c;
				$career_lines[] = trim( $when . ' ||| ' . $what );
			}
		}
		echo '<p><label><strong>Werdegang</strong><br /><span style="color:#667;font-weight:400;">Eine Station pro Zeile, Format <code>Zeit ||| Beschreibung</code> (z.B. <code>seit 2023 ||| Partner, Energiesozietät</code>)</span></label>';
		echo '<textarea name="es_career_raw" rows="6" style="width:100%;">' . esc_textarea( implode( "\n", $career_lines ) ) . '</textarea></p>';
	}

	public static function box_einzel( $post ) {
		self::nonce();
		self::field( 'Untertitel (Hero)',  'es_subtitle', get_post_meta( $post->ID, 'es_subtitle', true ), 'textarea' );

		// Reihenfolge auf der Seite (und hier im Backend identisch):
		// Einleitung (normaler Editor oben) → Schwerpunkte → Rubriken → Abschluss.
		$bullets = get_post_meta( $post->ID, 'es_bullets', true );
		$txt = is_array( $bullets ) ? implode( "\n", $bullets ) : '';
		echo '<p><label><strong>Schwerpunkte unserer Beratung (eine Zeile = ein Punkt, stehen mit grüner Überschrift „Schwerpunkte unserer Beratung" zwischen Einleitung und Rubriken)</strong></label>';
		echo '<textarea name="es_bullets_raw" rows="6" style="width:100%;">' . esc_textarea( $txt ) . '</textarea></p>';

		self::accordion_repeater(
			'es_acc',
			get_post_meta( $post->ID, 'es_accordion', true ),
			'Aufklapp-Rubriken',
			'Jede Rubrik erscheint als aufklappbarer Punkt unter Einleitung und Schwerpunkten; der optionale Kurztext steht sichtbar unter dem Titel. Absätze mit einer Leerzeile trennen, Aufzählungspunkte als eigene Zeilen mit „- " beginnen; Formatierungs-Codes sind nicht nötig.'
		);

		self::field( 'Abschluss-Absatz (steht als Letztes auf der Seite)', 'es_closing', get_post_meta( $post->ID, 'es_closing', true ), 'textarea' );

		self::field( 'Ansprechpartner (Kontaktkarte)', 'es_ansprechpartner', get_post_meta( $post->ID, 'es_ansprechpartner', true ), 'select', array( 'options' => self::team_options() ) );
	}

	/**
	 * Rubrik-Inhalt (gespeichertes HTML) in die Klartext-Schreibweise der
	 * Eingabefelder wandeln: Absätze durch Leerzeilen getrennt, Listenpunkte
	 * als Zeilen mit "- ". Enthält der Inhalt andere Formatierung als einfache
	 * Absätze/Listen (z. B. Links), bleibt er unverändert stehen, damit beim
	 * Speichern nichts verloren geht.
	 */
	public static function acc_html_to_text( $html ) {
		$html = (string) $html;
		if ( '' === trim( $html ) ) { return ''; }
		if ( preg_match_all( '/<\s*\/?\s*([a-z0-9]+)/i', $html, $m ) ) {
			foreach ( $m[1] as $tag ) {
				if ( ! in_array( strtolower( $tag ), array( 'p', 'ul', 'li', 'br' ), true ) ) { return $html; }
			}
		}
		$txt = preg_replace( '/<br\s*\/?\s*>/i', "\n", $html );
		$txt = preg_replace( '/<li[^>]*>\s*/i', '- ', $txt );
		$txt = preg_replace( '/\s*<\/li>/i', "\n", $txt );
		$txt = preg_replace( '/<\/(p|ul)>/i', "\n\n", $txt );
		$txt = wp_strip_all_tags( $txt );
		$txt = html_entity_decode( $txt, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		$txt = preg_replace( "/\n{3,}/", "\n\n", $txt );
		return trim( $txt );
	}

	/**
	 * Gegenstück zu acc_html_to_text: Klartext aus den Eingabefeldern in das
	 * gespeicherte HTML wandeln (Zeile = Absatz, "- "-Zeilen = Liste,
	 * Leerzeilen trennen Blöcke). Enthält die Eingabe bereits HTML-Tags,
	 * wird sie unverändert übernommen.
	 */
	public static function acc_text_to_html( $text ) {
		$text = trim( str_replace( "\r\n", "\n", (string) $text ) );
		if ( '' === $text ) { return ''; }
		if ( preg_match( '/<[a-z][^>]*>/i', $text ) ) { return $text; }
		$out  = '';
		$list = array();
		$flush = function () use ( &$out, &$list ) {
			if ( $list ) {
				$out .= '<ul><li>' . implode( '</li><li>', $list ) . '</li></ul>';
				$list = array();
			}
		};
		foreach ( explode( "\n", $text ) as $line ) {
			$line = trim( $line );
			if ( '' === $line ) { $flush(); continue; }
			if ( preg_match( '/^[-*•]\s+(.*)$/u', $line, $m ) ) { $list[] = trim( $m[1] ); continue; }
			$flush();
			$out .= '<p>' . $line . '</p>';
		}
		$flush();
		return $out;
	}

	/**
	 * Wiederholfeld für Aufklapp-Rubriken: je Rubrik ein Überschrift-Feld und
	 * ein Inhalts-Textfeld, mit Hinzufügen/Entfernen/Umsortieren.
	 * $prefix 'es_acc' (DE) bzw. 'es_acc_en' (EN).
	 */
	protected static function accordion_repeater( $prefix, $rows, $label, $hint ) {
		$rows = is_array( $rows ) ? array_values( $rows ) : array();
		$id = esc_attr( $prefix ) . '_rep';
		echo '<div class="esc-acc-rep" id="' . $id . '" style="margin:16px 0;padding:12px;border:1px solid #dcdcde;border-radius:4px;background:#fafafa;">';
		echo '<p style="margin-top:0;"><strong>' . esc_html( $label ) . '</strong><br /><span style="color:#667;">' . esc_html( $hint ) . '</span></p>';
		echo '<div class="esc-acc-rows">';
		foreach ( $rows as $r ) {
			self::accordion_row( $prefix, (string) ( $r['title'] ?? '' ), (string) ( $r['teaser'] ?? '' ), (string) ( $r['content'] ?? '' ) );
		}
		echo '</div>';
		echo '<p style="margin-bottom:0;"><button type="button" class="button esc-acc-add">+ Rubrik hinzufügen</button></p>';
		// Leere Vorlagenzeile für das JS (ohne name-Attribute, damit sie beim
		// Speichern nicht mitkommt; JS setzt die Namen beim Einfügen).
		echo '<template class="esc-acc-tpl">';
		self::accordion_row( $prefix, '', '', '', true );
		echo '</template>';
		echo '</div>';
		self::accordion_repeater_js();
	}

	protected static function accordion_row( $prefix, $title, $teaser, $content, $tpl = false ) {
		$nt = $tpl ? '' : ' name="' . esc_attr( $prefix ) . '_title[]"';
		$nz = $tpl ? '' : ' name="' . esc_attr( $prefix ) . '_teaser[]"';
		$nc = $tpl ? '' : ' name="' . esc_attr( $prefix ) . '_content[]"';
		echo '<div class="esc-acc-row" style="border:1px solid #e2e4e7;border-radius:4px;background:#fff;padding:10px;margin-bottom:10px;">';
		echo '<div style="display:flex;gap:8px;align-items:center;margin-bottom:6px;">';
		echo '<input type="text"' . $nt . ' data-name="' . esc_attr( $prefix ) . '_title[]" value="' . esc_attr( $title ) . '" placeholder="Überschrift der Rubrik" style="flex:1;" />';
		echo '<button type="button" class="button esc-acc-up" title="Nach oben">↑</button>';
		echo '<button type="button" class="button esc-acc-down" title="Nach unten">↓</button>';
		echo '<button type="button" class="button esc-acc-del" title="Rubrik entfernen">✕</button>';
		echo '</div>';
		echo '<textarea' . $nz . ' data-name="' . esc_attr( $prefix ) . '_teaser[]" rows="2" style="width:100%;margin-bottom:6px;" placeholder="Kurztext/Teaser (optional, steht unter dem Titel und ist ohne Aufklappen sichtbar)">' . esc_textarea( $teaser ) . '</textarea>';
		echo '<textarea' . $nc . ' data-name="' . esc_attr( $prefix ) . '_content[]" rows="5" style="width:100%;" placeholder="Inhalt der Rubrik">' . esc_textarea( self::acc_html_to_text( $content ) ) . '</textarea>';
		echo '</div>';
	}

	protected static function accordion_repeater_js() {
		static $done = false;
		if ( $done ) { return; }
		$done = true;
		?>
		<script>
		document.addEventListener('click', function (e) {
			var rep = e.target.closest && e.target.closest('.esc-acc-rep');
			if (!rep) { return; }
			var rowsBox = rep.querySelector('.esc-acc-rows');
			if (e.target.classList.contains('esc-acc-add')) {
				var tpl = rep.querySelector('.esc-acc-tpl');
				var node = tpl.content.firstElementChild.cloneNode(true);
				node.querySelectorAll('[data-name]').forEach(function (el) { el.setAttribute('name', el.getAttribute('data-name')); });
				rowsBox.appendChild(node);
				e.preventDefault();
			}
			var row = e.target.closest('.esc-acc-row');
			if (!row) { return; }
			if (e.target.classList.contains('esc-acc-del')) {
				if (confirm('Diese Rubrik entfernen?')) { row.remove(); }
				e.preventDefault();
			} else if (e.target.classList.contains('esc-acc-up') && row.previousElementSibling) {
				row.parentNode.insertBefore(row, row.previousElementSibling);
				e.preventDefault();
			} else if (e.target.classList.contains('esc-acc-down') && row.nextElementSibling) {
				row.parentNode.insertBefore(row.nextElementSibling, row);
				e.preventDefault();
			}
		});
		</script>
		<?php
	}

	public static function box_karriere( $post ) {
		self::nonce();
		self::field( 'Rolle / Titel-Kürzel', 'es_department',      get_post_meta( $post->ID, 'es_department', true ) );
		self::field(
			'Beratungsbereich',
			'es_field',
			get_post_meta( $post->ID, 'es_field', true ),
			'select',
			array( 'options' => self::FIELDS )
		);
		self::field( 'Standort',           'es_location',        get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Anstellungsart',     'es_employment_type', get_post_meta( $post->ID, 'es_employment_type', true ) );
		self::field( 'Eintrittsdatum',     'es_start_date',      get_post_meta( $post->ID, 'es_start_date', true ), 'date' );

		// Aufgaben (Was erwarten Dich für Aufgaben?) – Legacy-Fallback: es_bullets
		$tasks = get_post_meta( $post->ID, 'es_tasks', true );
		if ( empty( $tasks ) ) {
			$legacy = get_post_meta( $post->ID, 'es_bullets', true );
			if ( is_array( $legacy ) ) { $tasks = $legacy; }
		}
		$tasks_txt = is_array( $tasks ) ? implode( "\n", $tasks ) : '';
		echo '<p><label><strong>Was erwarten Dich für Aufgaben?</strong><br /><span style="color:#667;font-weight:400;">Eine Aufgabe pro Zeile – wird als Bullet-Liste ausgegeben.</span></label>';
		echo '<textarea name="es_tasks_raw" rows="6" style="width:100%;">' . esc_textarea( $tasks_txt ) . '</textarea></p>';

		// Profil (Dein Profil)
		$profile = get_post_meta( $post->ID, 'es_profile', true );
		$profile_txt = is_array( $profile ) ? implode( "\n", $profile ) : '';
		echo '<p><label><strong>Dein Profil</strong><br /><span style="color:#667;font-weight:400;">Eine Anforderung pro Zeile – wird als Bullet-Liste ausgegeben.</span></label>';
		echo '<textarea name="es_profile_raw" rows="6" style="width:100%;">' . esc_textarea( $profile_txt ) . '</textarea></p>';

		// Was wir Dir bieten (pro Stelle; die Benefits-Kacheln darunter kommen
		// aus Theme Options → Globale Informationen)
		$offer = get_post_meta( $post->ID, 'es_offer', true );
		$offer_txt = is_array( $offer ) ? implode( "\n", $offer ) : '';
		echo '<p><label><strong>Was wir Dir bieten</strong><br /><span style="color:#667;font-weight:400;">Eine Zeile = ein Punkt. Leer = globaler Standardtext aus Karriere → Globale Informationen; ein Eintrag hier überschreibt ihn für diese Stelle.</span></label>';
		echo '<textarea name="es_offer_raw" rows="5" style="width:100%;">' . esc_textarea( $offer_txt ) . '</textarea></p>';

		// Abschlusstext (pro Stelle, unter den Listen)
		echo '<p><label><strong>Abschlusstext</strong><br /><span style="color:#667;font-weight:400;">Leer = globaler Standardtext aus Karriere → Globale Informationen; ein Eintrag hier überschreibt ihn für diese Stelle.</span></label>';
		echo '<textarea name="es_closing" rows="4" style="width:100%;">' . esc_textarea( (string) get_post_meta( $post->ID, 'es_closing', true ) ) . '</textarea></p>';
	}

	public static function box_veranst( $post ) {
		self::nonce();
		self::field( 'Startdatum', 'es_start_date', get_post_meta( $post->ID, 'es_start_date', true ), 'date' );
		self::field( 'Enddatum',   'es_end_date',   get_post_meta( $post->ID, 'es_end_date', true ),   'date' );
		self::field( 'Ort',        'es_location',   get_post_meta( $post->ID, 'es_location', true ) );
		self::field( 'Art',        'es_kind',       get_post_meta( $post->ID, 'es_kind', true ) );
		self::field( 'Anmelde-URL','es_registration_url', get_post_meta( $post->ID, 'es_registration_url', true ), 'url' );
	}

	public static function box_publikation( $post ) {
		self::nonce();
		self::field( 'Kategorie (z.B. Aufsatz, Buch, Kommentar)', 'es_cat',    get_post_meta( $post->ID, 'es_cat', true ) );
		self::field( 'Fundstelle / Quelle',                        'es_source', get_post_meta( $post->ID, 'es_source', true ) );
		self::field( 'Veröffentlichungsdatum',                     'es_publication_date', get_post_meta( $post->ID, 'es_publication_date', true ), 'date' );
		echo '<p class="description" style="margin:-8px 0 16px;font-style:normal;">Steuert die Jahres-Gruppierung und Reihenfolge der Publikationsliste. Das WordPress-Beitragsdatum (rechte Seitenleiste) wird beim Speichern automatisch angeglichen.</p>';
		self::field( 'Externer Link (Zur Publikation)',            'es_link',   get_post_meta( $post->ID, 'es_link', true ), 'url' );
		self::field( 'Autor:innen (Freitext, Fallback)',           'es_author', get_post_meta( $post->ID, 'es_author', true ) );

		// Team-Autoren (Relation, mehrere)
		$selected = (array) get_post_meta( $post->ID, 'es_author_ids', true );
		$options  = self::team_options();
		echo '<p style="margin-bottom:4px;"><label for="es_author_ids"><strong>Autor:innen aus Team</strong></label><br /><span style="color:#667;font-weight:400;">Strg/Cmd-Klick für Mehrfachauswahl. Diese Zuordnung führt dazu, dass die Publikation auf der jeweiligen Team-Einzelseite und im Beratungsfeld erscheint.</span></p>';
		echo '<select id="es_author_ids" name="es_author_ids[]" multiple size="8" style="display:block;width:100%;max-width:480px;margin:0 0 16px;">';
		foreach ( $options as $id => $label ) {
			echo '<option value="' . (int) $id . '"' . ( in_array( (string) $id, array_map( 'strval', $selected ), true ) ? ' selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';

		// Beratungsfelder (Mehrfach via es_beratungsfeld-Taxonomie existiert schon für Einzelleistung – hier nutzen wir zusätzlich ein Meta für Publikation, da Taxonomie auf es_einzelleistung limitiert ist)
		$field_selected = (array) get_post_meta( $post->ID, 'es_fields', true );
		echo '<p style="margin-bottom:4px;"><strong>Beratungsfelder</strong><br /><span style="color:#667;font-weight:400;">Diese Zuordnung führt dazu, dass die Publikation auf der jeweiligen Beratungsfeld-Seite als Fachbeitrag erscheint.</span></p>';
		echo '<p style="margin-top:0;">';
		foreach ( self::FIELDS as $k => $v ) {
			echo '<label style="display:inline-flex;align-items:center;gap:6px;margin-right:20px;margin-top:6px;">';
			echo '<input type="checkbox" name="es_fields[]" value="' . esc_attr( $k ) . '"' . ( in_array( $k, $field_selected, true ) ? ' checked' : '' ) . ' /> ' . esc_html( $v );
			echo '</label>';
		}
		echo '</p>';
	}

	public static function box_news( $post ) {
		self::nonce();
		$val = ( 'auto-draft' === $post->post_status ) ? '' : substr( $post->post_date, 0, 10 );
		self::field( 'Veröffentlichungsdatum', 'es_news_date', $val, 'date' );
		echo '<p class="description" style="margin:-8px 0 4px;font-style:normal;">Steuert Datumsanzeige und Reihenfolge des Beitrags. Das WordPress-Beitragsdatum (rechte Seitenleiste) wird beim Speichern automatisch angeglichen; leer = heutiges Datum.</p>';
	}

	public static function save( $post_id, $post ) {
		if ( ! isset( $_POST['esc_meta_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['esc_meta_nonce'] ), 'esc_meta' ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$scalars = array(
			'es_role','es_gender','es_email','es_phone','es_linkedin','es_location','es_sort_name','es_field','es_more_bio',
			'es_subtitle','es_closing','es_ansprechpartner',
			'es_department','es_employment_type','es_start_date',
			'es_end_date','es_kind','es_registration_url',
			'es_cat','es_source','es_publication_date','es_link','es_author',
			'es_title_en','es_content_en','es_excerpt_en','es_slug_en',
			'es_role_en','es_more_bio_en','es_subtitle_en','es_closing_en',
			'es_department_en','es_employment_type_en','es_location_en','es_kind_en','es_cat_en',
		);
		foreach ( $scalars as $k ) {
			if ( array_key_exists( $k, $_POST ) ) {
				$v = wp_unslash( $_POST[ $k ] );
				if ( in_array( $k, array( 'es_link', 'es_linkedin', 'es_registration_url' ), true ) ) {
					$v = esc_url_raw( $v );
				} elseif ( in_array( $k, array( 'es_more_bio', 'es_closing', 'es_subtitle', 'es_content_en', 'es_excerpt_en', 'es_more_bio_en', 'es_closing_en', 'es_subtitle_en' ), true ) ) {
					$v = wp_kses_post( $v );
				} elseif ( 'es_slug_en' === $k ) {
					$v = sanitize_title( $v );
				} else {
					$v = sanitize_text_field( $v );
				}
				update_post_meta( $post_id, $k, $v );
			}
		}

		if ( isset( $_POST['es_bullets_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_bullets_raw'] ) );
			$bullets = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $bullets[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_bullets', $bullets );
		}

		if ( isset( $_POST['es_tasks_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_tasks_raw'] ) );
			$arr = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_tasks', $arr );
		}

		if ( isset( $_POST['es_profile_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_profile_raw'] ) );
			$arr = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_profile', $arr );
		}

		if ( isset( $_POST['es_focus_areas_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_focus_areas_raw'] ) );
			$focus = array();
			foreach ( $lines as $l ) {
				$l = trim( $l );
				if ( ! $l ) { continue; }
				// Support both "Titel ||| Inhalt" legacy and plain "Titel"
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				$focus[] = array( 'title' => wp_kses_post( $parts[0] ), 'content' => isset( $parts[1] ) ? wp_kses_post( $parts[1] ) : '' );
			}
			update_post_meta( $post_id, 'es_focus_areas', $focus );
		}

		if ( isset( $_POST['es_career_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_career_raw'] ) );
			$career = array();
			foreach ( $lines as $l ) {
				$l = trim( $l );
				if ( ! $l ) { continue; }
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				$career[] = array( 'when' => sanitize_text_field( $parts[0] ), 'what' => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '' );
			}
			update_post_meta( $post_id, 'es_career', $career );
		}

		// DE: "Was wir Dir bieten" pro Stelle
		if ( isset( $_POST['es_offer_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_offer_raw'] ) );
			$arr = array();
			foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
			update_post_meta( $post_id, 'es_offer', $arr );
		}

		// EN-Zeilenfelder: einfache String-Arrays
		foreach ( array( 'es_focus_areas_en', 'es_bullets_en', 'es_tasks_en', 'es_profile_en', 'es_offer_en' ) as $arr_key ) {
			if ( isset( $_POST[ $arr_key . '_raw' ] ) ) {
				$lines = preg_split( '/\r?\n/', wp_unslash( $_POST[ $arr_key . '_raw' ] ) );
				$arr = array();
				foreach ( $lines as $l ) { $l = trim( $l ); if ( $l ) { $arr[] = wp_kses_post( $l ); } }
				update_post_meta( $post_id, $arr_key, $arr );
			}
		}
		// Aufklapp-Rubriken (Einzelleistung): parallele Titel-/Teaser-/Inhalts-Arrays
		foreach ( array( 'es_acc' => 'es_accordion', 'es_acc_en' => 'es_accordion_en' ) as $prefix => $meta_key ) {
			if ( ! isset( $_POST[ $prefix . '_title' ] ) || ! is_array( $_POST[ $prefix . '_title' ] ) ) { continue; }
			$titles   = array_map( 'wp_unslash', (array) $_POST[ $prefix . '_title' ] );
			$teasers  = array_map( 'wp_unslash', (array) ( $_POST[ $prefix . '_teaser' ] ?? array() ) );
			$contents = array_map( 'wp_unslash', (array) ( $_POST[ $prefix . '_content' ] ?? array() ) );
			$rows = array();
			foreach ( $titles as $i => $t ) {
				$t = sanitize_text_field( $t );
				$z = sanitize_text_field( (string) ( $teasers[ $i ] ?? '' ) );
				$c = wp_kses_post( self::acc_text_to_html( (string) ( $contents[ $i ] ?? '' ) ) );
				if ( '' === $t && '' === $z && '' === $c ) { continue; }
				$row = array( 'title' => $t );
				// Optionaler Kurztext: nur speichern, wenn befüllt, damit Rubriken
				// ohne Teaser identisch zu den Import-Daten bleiben.
				if ( '' !== $z ) { $row['teaser'] = $z; }
				$row['content'] = $c;
				$rows[] = $row;
			}
			if ( $rows ) { update_post_meta( $post_id, $meta_key, $rows ); }
			else { delete_post_meta( $post_id, $meta_key ); }
		}
		// EN-Werdegang im selben Format wie die deutsche Fassung ({when, what})
		if ( isset( $_POST['es_career_en_raw'] ) ) {
			$lines = preg_split( '/\r?\n/', wp_unslash( $_POST['es_career_en_raw'] ) );
			$career = array();
			foreach ( $lines as $l ) {
				$l = trim( $l );
				if ( ! $l ) { continue; }
				$parts = array_map( 'trim', explode( '|||', $l, 2 ) );
				$career[] = array( 'when' => sanitize_text_field( $parts[0] ), 'what' => isset( $parts[1] ) ? sanitize_text_field( $parts[1] ) : '' );
			}
			update_post_meta( $post_id, 'es_career_en', $career );
		}

		if ( isset( $_POST['es_author_ids'] ) && is_array( $_POST['es_author_ids'] ) ) {
			$ids = array_values( array_filter( array_map( 'intval', $_POST['es_author_ids'] ) ) );
			update_post_meta( $post_id, 'es_author_ids', $ids );
		} elseif ( 'es_publikation' === $post->post_type ) {
			update_post_meta( $post_id, 'es_author_ids', array() );
		}

		if ( 'es_publikation' === $post->post_type ) {
			$fields = isset( $_POST['es_fields'] ) && is_array( $_POST['es_fields'] ) ? array_values( array_intersect( array_keys( self::FIELDS ), array_map( 'sanitize_text_field', wp_unslash( $_POST['es_fields'] ) ) ) ) : array();
			update_post_meta( $post_id, 'es_fields', $fields );
		}

		// Veröffentlichungsdatum → WordPress-Beitragsdatum: Das Eingabefeld
		// steuert bei Publikationen (Jahres-Gruppierung, Reihenfolge) und News
		// (Datumsanzeige, Reihenfolge) das Beitragsdatum; die rechte
		// Seitenleiste muss nicht mehr separat angepasst werden.
		$sync_date = '';
		if ( 'es_publikation' === $post->post_type ) {
			$sync_date = (string) get_post_meta( $post_id, 'es_publication_date', true );
		} elseif ( 'es_news' === $post->post_type && isset( $_POST['es_news_date'] ) ) {
			$sync_date = sanitize_text_field( wp_unslash( $_POST['es_news_date'] ) );
		}
		if ( $sync_date && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $sync_date ) && substr( $post->post_date, 0, 10 ) !== $sync_date ) {
			$new_date = $sync_date . substr( $post->post_date, 10 ); // Uhrzeit beibehalten
			// Direkt in der Datenbank, um eine save_post-Rekursion zu vermeiden.
			global $wpdb;
			$wpdb->update(
				$wpdb->posts,
				array( 'post_date' => $new_date, 'post_date_gmt' => get_gmt_from_date( $new_date ) ),
				array( 'ID' => $post_id )
			);
			clean_post_cache( $post_id );
		}
	}
}
ESC_MetaBoxes::init();
