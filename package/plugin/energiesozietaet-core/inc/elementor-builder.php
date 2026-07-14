<?php
/**
 * Programmatic Elementor-JSON builder.
 *
 * Generates Elementor-compatible page data using only standard Elementor widgets
 * (heading, text-editor, button, html, shortcode, image, spacer, divider, icon-list,
 * icon-box, accordion). Most visual styling lives in the theme's style.css via
 * CSS class names (es-*). The builder's job is to produce sections that carry
 * the right markup + classes.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Elementor_Builder {

	public static function id() {
		return substr( md5( uniqid( '', true ) . wp_generate_password( 8, false ) ), 0, 7 );
	}

	/** Raw section wrapper. */
	public static function section( $columns, $section_settings = array() ) {
		$col_count = count( $columns );
		$size      = (int) floor( 100 / max( 1, $col_count ) );
		$out_cols  = array();
		foreach ( $columns as $cfg ) {
			$col_widgets = isset( $cfg['widgets'] ) ? $cfg['widgets'] : $cfg;
			$col_settings = isset( $cfg['settings'] ) ? $cfg['settings'] : array();
			$settings = array_merge( array( '_column_size' => $size, '_inline_size' => null ), $col_settings );
			// Elementor liefert nur für Preset-Größen (25/33/50/66/…) CSS-Klassen
			// mit Breite aus. Für krumme Werte (46, 52, 58, …) MUSS _inline_size
			// gesetzt sein, sonst kollabiert die Column auf Inhaltsbreite.
			if ( null === $settings['_inline_size'] && isset( $settings['_column_size'] ) ) {
				$settings['_inline_size'] = (int) $settings['_column_size'];
			}
			$out_cols[] = array(
				'id'       => self::id(),
				'elType'   => 'column',
				'settings' => $settings,
				'elements' => array_values( array_filter( $col_widgets ) ),
				'isInner'  => false,
			);
		}
		return array(
			'id'       => self::id(),
			'elType'   => 'section',
			'settings' => $section_settings,
			'elements' => $out_cols,
			'isInner'  => false,
		);
	}

	/** Raw HTML block – most layout work happens here, styled by theme CSS classes.
	 *  $settings: zusätzliche Elementor-Widget-Settings (z. B. hide_desktop). */
	public static function html( $html, $settings = array() ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'html',
			'settings'   => array_merge( array( 'html' => $html ), $settings ),
		);
	}

	/** Full-width container holding an HTML widget; optional background variant. */
	public static function section_html( $html, $variant = '' ) {
		// Overlap-Mechanik (siehe section_native): eigene Farbe via ::before
		// (es-own-*) + gerundete Unterkante; Untergrund (= nächste Sektion) via CSS.
		$own     = $variant ? $variant : 'paper';
		$overlap = 'es-stage-sec es-own-' . $own . ' es-round-bottom';
		$settings = array(
			'layout'        => 'full_width',
			'content_width' => array( 'unit' => 'px', 'size' => 1280 ),
			'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
			'gap'           => 'no',
			'css_classes'   => $overlap,
			'_css_classes'  => $overlap,
		);
		// Stage-Wrapper (innen) damit CSS-Selektoren wie .es-stage--ink h1 greifen
		$stage_class = $variant ? 'es-stage es-stage--' . $variant : 'es-stage';
		$wrapped = '<div class="' . esc_attr( $stage_class ) . '">' . $html . '</div>';
		return self::section( array( array( 'widgets' => array( self::html( $wrapped ) ) ) ), $settings );
	}

	/** Heading widget (kept for legacy call sites). */
	public static function heading( $title, $size = 'xl', $extra = array() ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'heading',
			'settings'   => array_merge( array(
				'title'      => $title,
				'size'       => $size,
				'header_size'=> in_array( $size, array( 'xxl','xl' ), true ) ? 'h1' : ( $size === 'large' ? 'h2' : 'h3' ),
			), $extra ),
		);
	}

	public static function text( $html, $extra = array() ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'text-editor',
			'settings'   => array_merge( array( 'editor' => wpautop_safe( $html ) ), $extra ),
		);
	}

	public static function button( $label, $url, $style = 'primary', $align = 'left' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'button',
			'settings'   => array(
				'text'  => $label,
				'link'  => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
				'align' => $align,
				// Theme CSS styles .elementor-widget-button .elementor-button generically.
			),
		);
	}

	public static function spacer( $height_px = 40 ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'spacer',
			'settings'   => array( 'space' => array( 'unit' => 'px', 'size' => $height_px ) ),
		);
	}

	public static function shortcode( $shortcode, $extra = array() ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array_merge( array( 'shortcode' => $shortcode ), $extra ),
		);
	}

	public static function accordion( $items ) {
		$tabs = array();
		foreach ( $items as $it ) {
			$tabs[] = array(
				'_id'         => self::id(),
				'tab_title'   => isset( $it['title'] ) ? $it['title'] : '',
				'tab_content' => isset( $it['content'] ) ? wpautop_safe( $it['content'] ) : '',
			);
		}
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'accordion',
			'settings'   => array(
				'tabs'                          => $tabs,
				'title_html_tag'                => 'h4',
				'border_width'                  => array( 'unit' => 'px', 'size' => 0 ),
				'title_color'                   => '#122023',
				'tab_content_color'             => '#899092',
			),
		);
	}

	/* =========================================================================
	 * Compound builders – whole sections returned as HTML blocks.
	 * These use the theme's CSS classes (es-*) for layout.
	 * ========================================================================= */

	/**
	 * Editorial hero (dark). Matches mockup H1/H2:
	 *   eyebrow, multi-line headline (accent spans), lead, optional buttons,
	 *   optional claim-grid under.
	 *
	 * $args = [
	 *   'eyebrow' => string,
	 *   'headline_html' => string (may contain <br> + <span class="text-bg-green">)
	 *   'lead' => string (plain or HTML),
	 *   'buttons' => [[label, url, style], ...],  style = paper|ghost-paper|accent
	 *   'claims' => [[wert, label], ...] (optional, triggers bottom grid)
	 *   'padding' => 'default'|'tall'|'short'
	 * ]
	 */
	public static function hero_editorial( $args ) {
		$args = array_merge( array(
			'eyebrow' => '', 'headline_html' => '', 'lead' => '',
			'buttons' => array(), 'claims' => array(), 'padding' => 'default',
		), $args );

		$pad = '120px 0 110px';
		if ( 'tall' === $args['padding'] )  { $pad = '140px 0 140px'; }
		if ( 'short' === $args['padding'] ) { $pad = '100px 0 100px'; }

		$html  = '<div class="es-wrap" style="padding-top:' . explode( ' ', $pad )[0] . ';padding-bottom:' . explode( ' ', $pad )[2] . ';">';
		if ( $args['eyebrow'] ) {
			$html .= '<div class="es-eyebrow es-eyebrow--paper">' . esc_html( $args['eyebrow'] ) . '</div>';
		}
		if ( $args['headline_html'] ) {
			$html .= '<h1 style="max-width:1100px;margin:0 0 28px;">' . $args['headline_html'] . '</h1>';
		}
		if ( $args['lead'] ) {
			$html .= '<p style="max-width:780px;font-size:var(--es-fs-body);line-height:1.55;color:rgba(255,255,255,0.78);font-weight:300;margin:0 0 36px;">' . $args['lead'] . '</p>';
		}
		if ( ! empty( $args['buttons'] ) ) {
			$html .= '<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:40px;">';
			foreach ( $args['buttons'] as $b ) {
				$style = isset( $b[2] ) ? $b[2] : 'paper';
				$html .= '<a class="es-btn es-btn--' . esc_attr( $style ) . '" href="' . esc_url( $b[1] ) . '">' . esc_html( $b[0] ) . ' →</a>';
			}
			$html .= '</div>';
		}
		if ( ! empty( $args['claims'] ) ) {
			$html .= '<div class="es-hero-claims">';
			foreach ( $args['claims'] as $c ) {
				$html .= '<div><div class="es-hero-claims__wert">' . esc_html( $c[0] ) . '</div><div class="es-hero-claims__label">' . esc_html( $c[1] ) . '</div></div>';
			}
			$html .= '</div>';
		}
		$html .= '</div>';

		return self::section_html( $html, 'ink' );
	}

	/**
	 * Split-Text section (C1 per mockup): eyebrow+title left, paragraphs right.
	 * $args = [eyebrow, title_html, paragraphs (array), variant (paper|warm|cool|ink), padding]
	 */
	public static function split_text( $args ) {
		$args = array_merge( array(
			'eyebrow' => '', 'title_html' => '', 'paragraphs' => array(),
			'variant' => 'paper', 'padding' => 'default',
			'extra_after' => '',
		), $args );

		$pad = ( 'short' === $args['padding'] ) ? '80px 0' : ( 'tall' === $args['padding'] ? '140px 0' : '120px 0' );
		$eyebrow_class = 'ink' === $args['variant'] ? 'es-eyebrow es-eyebrow--paper' : 'es-eyebrow';

		$right_html = '';
		foreach ( (array) $args['paragraphs'] as $i => $p ) {
			$size  = 0 === $i ? '20px' : '17px';
			$color = 'ink' === $args['variant'] ? ( 0 === $i ? 'rgba(255,255,255,0.82)' : 'rgba(255,255,255,0.6)' ) : ( 0 === $i ? '#122023' : '#899092' );
			$right_html .= '<p style="font-size:' . $size . ';line-height:1.65;color:' . $color . ';margin:0 0 24px;">' . $p . '</p>';
		}

		$html  = '<div class="es-wrap" style="padding:' . $pad . ';">';
		$html .= '<div style="display:grid;grid-template-columns:1fr 1.6fr;gap:96px;align-items:start;">';
		$html .= '<div>';
		if ( $args['eyebrow'] ) {
			$html .= '<div class="' . esc_attr( $eyebrow_class ) . '">' . esc_html( $args['eyebrow'] ) . '</div>';
		}
		if ( $args['title_html'] ) {
			$html .= '<h2 style="font-size:clamp(32px,4vw,52px);line-height:1.05;font-weight:300;letter-spacing:-0.035em;margin:24px 0 0;">' . $args['title_html'] . '</h2>';
		}
		$html .= '</div>';
		$html .= '<div style="padding-top:8px;">' . $right_html . $args['extra_after'] . '</div>';
		$html .= '</div>';
		$html .= '</div>';

		return self::section_html( $html, 'paper' === $args['variant'] ? '' : $args['variant'] );
	}

	/**
	 * Bereichs-Block (C3): big service area with image placeholder + topic grid.
	 * $args = [n, title, sub, lede, link, topics (array of [t, d]), stripe (odd|even), image_html?]
	 */
	public static function bereich( $args ) {
		$args = array_merge( array(
			'n' => '01', 'title' => '', 'title_html' => '', 'sub' => '', 'lede' => '', 'link' => '',
			'field' => '',                 // Wenn gesetzt, zieht bereich() die Einzelleistungen dynamisch
			'topics' => array(), 'stripe' => 'odd', 'image_html' => '',
			'next_bg' => 'ink',            // Farbe der FOLGENDEN Sektion (paper|warm|ink) → Ecken-Überlappung
		), $args );
		$warm = 'even' === $args['stripe'] ? ' es-bereich--warm' : '';
		$next = in_array( $args['next_bg'], array( 'paper', 'warm', 'ink' ), true ) ? ' es-bereich--next-' . $args['next_bg'] : '';
		$h2_content = $args['title_html'] ? $args['title_html'] : esc_html( $args['title'] );

		// Dynamik: wenn $field gesetzt und keine Topics vorgegeben, ruft der
		// Shortcode [es_einzelleistungen] bei Render-Zeit die aktuellen Einzel-
		// leistungen ab. Neue Einträge erscheinen automatisch.
		$use_shortcode = ( $args['field'] && empty( $args['topics'] ) );
		$topics_html = '';
		if ( ! $use_shortcode ) {
			foreach ( $args['topics'] as $topic ) {
				$topics_html .= '<a class="es-bereich__topic" href="#' . esc_attr( sanitize_title( $topic[0] ) ) . '">';
				$topics_html .= '<div class="es-bereich__topic-name"><span></span><h3>' . esc_html( $topic[0] ) . '</h3></div>';
				$topics_html .= '<p class="es-bereich__topic-desc">' . esc_html( $topic[1] ) . '</p>';
				$topics_html .= '</a>';
			}
		}

		$img_html = $args['image_html'];
		if ( ! $img_html ) {
			$img_html = '<div class="es-bereich__img"><div class="es-ph-cat"><span>' . esc_html( $args['title'] ) . '</span></div></div>';
		}

		$html  = '<section class="es-bereich' . $warm . $next . '"><div class="es-wrap"><div class="es-bereich__inner">';
		$html .= '<div class="es-bereich__top">';
		$html .= '<div>';
		$html .= '<div class="es-bereich__meta"><span class="es-bereich__num">' . esc_html( $args['n'] ) . ' / 03</span><span class="es-bereich__sep"></span><span class="es-bereich__sub">' . esc_html( $args['sub'] ) . '</span></div>';
		$html .= '<h2 class="es-bereich__title">' . $h2_content . '</h2>';
		$html .= '<p class="es-bereich__lede">' . esc_html( $args['lede'] ) . '</p>';
		if ( $args['link'] ) {
			// Button als es-btn-Klasse, aber via CSS wirkt visuell wie ein
			// Primary-Button. (Native Button-Widgets passen hier schlecht, da
			// die ganze Section HTML-basiert ist – Umbau auf section_native
			// wäre ein grösserer Umbau, den der User explizit bewahren will.)
			$html .= '<a class="es-btn es-btn--primary" href="' . esc_url( $args['link'] ) . '">Zur ' . esc_html( $args['title'] ) . ' →</a>';
		}
		$html .= '</div>';
		$html .= '<div>' . $img_html . '</div>';
		$html .= '</div>';

		$html .= '<div class="es-bereich__topics-label">Beratungsfelder</div>';
		if ( $use_shortcode ) {
			$html .= '[es_einzelleistungen beratungsfeld="' . esc_attr( $args['field'] ) . '" columns="3" link="1"]';
		} else {
			$html .= '<div class="es-bereich__topics">' . $topics_html . '</div>';
		}
		$html .= '</div></div></section>';

		return self::section_html( $html );
	}

	/**
	 * Dark CTA band (G3).
	 */
	public static function cta_dark( $args = array() ) {
		$args = array_merge( array(
			'eyebrow' => 'Kontakt',
			'title_html' => 'Haben wir Ihr Interesse geweckt?',
			'sub' => 'Möchten Sie uns kennenlernen?',
			'buttons' => array(
				array( 'Kontakt aufnehmen', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
		), $args );

		$html  = '<div class="es-wrap" style="padding:120px 0;">';
		$html .= '<div style="display:grid;grid-template-columns:1fr auto;align-items:end;gap:48px;">';
		$html .= '<div>';
		$html .= '<div class="es-eyebrow es-eyebrow--accent">' . esc_html( $args['eyebrow'] ) . '</div>';
		$html .= '<h2 style="font-size:clamp(36px,4.6vw,64px);line-height:1.02;font-weight:400;letter-spacing:-0.035em;margin:0;max-width:820px;">' . $args['title_html'];
		if ( $args['sub'] ) {
			$html .= '<br/><span style="color:rgba(255,255,255,0.55);">' . esc_html( $args['sub'] ) . '</span>';
		}
		$html .= '</h2></div>';
		$html .= '<div style="display:flex;gap:12px;flex-wrap:wrap;">';
		foreach ( $args['buttons'] as $b ) {
			$style = isset( $b[2] ) ? $b[2] : 'paper';
			$html .= '<a class="es-btn es-btn--' . esc_attr( $style ) . '" href="' . esc_url( $b[1] ) . '">' . esc_html( $b[0] ) . ' →</a>';
		}
		$html .= '</div></div></div>';

		return self::section_html( $html, 'ink' );
	}

	/**
	 * Pullquote section – huge centered quote on warm background.
	 */
	public static function pullquote( $quote_html, $attribution = '' ) {
		// Native Version: Wrapper-Section mit Klasse es-pullquote-panel, Inhalt
		// aus Heading-Widget (quote) + Text-Widget (attribution). Komplett im
		// Elementor-Editor klickbar.
		$widgets = array(
			self::wid_heading( $quote_html, 'h2', 'es-pullquote__quote' ),
		);
		if ( $attribution ) {
			$widgets[] = self::wid_text( '<p>' . esc_html( $attribution ) . '</p>', 'es-pullquote__attr' );
		}
		return self::section_native( array(
			'cols' => array( $widgets ),
			'css_classes' => 'es-pullquote-panel-wrap',
			'padding' => array( '120', '0', '120', '0' ),
		) );
	}

	/**
	 * GF Quote with portrait placeholder (Home page).
	 */
	public static function gf_quote( $args ) {
		// Native Version: 2-Col Section, Photo-HTML links (nicht editierbarer
		// Decor), Eyebrow + Zitat (Heading) + Name/Rolle (Text) rechts –
		// alle textlichen Inhalte via Elementor-Widget editierbar.
		$args = array_merge( array(
			'quote'           => '',
			'name'            => 'Prof. Dr. Sven-Joachim Otto',
			'role'            => 'Partner · Geschäftsführer',
			'photo_slug'      => 'prof-dr-sven-joachim-otto',
			'eyebrow'         => 'Unser Anspruch',
		), $args );
		// Layout: Eyebrow + Zitat oben (volle Breite) – darunter Portrait links
		// neben Name/Rolle. Portrait via HTML-Widget (rundes Bild), Name/Rolle
		// als native Text-Widgets editierbar.
		$photo_html = '<div class="es-gf-quote__photo">' . do_shortcode( '[es_team_photo slug="' . esc_attr( $args['photo_slug'] ) . '" size=88]' ) . '</div>';
		$author_html = '<div class="es-gf-quote__author">'
			. $photo_html
			. '<div class="es-gf-quote__author-text">'
			. '<p class="es-gf-quote__name">' . esc_html( $args['name'] ) . '</p>'
			. '<p class="es-gf-quote__role">' . esc_html( $args['role'] ) . '</p>'
			. '</div>'
			. '</div>';
		return self::section_native( array(
			'variant' => 'warm',
			'css_classes' => 'es-gf-quote',
			'padding' => array( '120', '0', '120', '0' ),
			'cols' => array( array(
				self::wid_heading( $args['eyebrow'], 'p', 'es-eyebrow' ),
				self::wid_heading( '„' . $args['quote'] . '"', 'h2', 'es-gf-quote__text' ),
				self::wid_html( $author_html ),
			) ),
		) );
	}

	/** Section head (eyebrow + h2 + lede), centered or left. */
	public static function section_head( $eyebrow, $title_html, $lede = '', $align = 'left', $variant = '' ) {
		$text_align = 'center' === $align ? 'text-align:center;' : '';
		$eyebrow_class = 'ink' === $variant ? 'es-eyebrow es-eyebrow--paper' : 'es-eyebrow';
		$title_color = 'ink' === $variant ? '#E1FCAD' : '#122023';
		$lede_color  = 'ink' === $variant ? '#CAD0BE' : '#899092';
		$max = 'center' === $align ? 'margin-inline:auto;' : '';

		$html  = '<div class="es-wrap" style="padding-top:120px;padding-bottom:48px;' . $text_align . '">';
		$html .= '<div class="' . esc_attr( $eyebrow_class ) . '" style="margin-bottom:20px;">' . esc_html( $eyebrow ) . '</div>';
		$html .= '<h2 style="font-size:clamp(32px,4.2vw,52px);line-height:1.05;font-weight:400;letter-spacing:-0.03em;max-width:720px;' . $max . 'color:' . $title_color . ';margin:0;">' . $title_html . '</h2>';
		if ( $lede ) {
			$html .= '<p style="font-size:var(--es-fs-body);line-height:1.55;margin-top:24px;color:' . $lede_color . ';max-width:620px;' . $max . '">' . $lede . '</p>';
		}
		$html .= '</div>';
		return self::section_html( $html, $variant );
	}

	/** Shortcode inside a boxed wrap with optional background. */
	public static function wrap_shortcode( $shortcode, $variant = '', $padding = '0 0 120px 0' ) {
		$html = '<div class="es-wrap" style="padding:' . $padding . ';">' . do_shortcode( $shortcode ) . '</div>';
		// Do not expand shortcode at build-time – let the page evaluate it on render.
		$html = '<div class="es-wrap" style="padding:' . $padding . ';">' . $shortcode . '</div>';
		return self::section_html( $html, $variant );
	}
	/* ======================================================================
	 * NATIVE ELEMENTOR WIDGET HELPERS
	 * Erzeugen echte heading/text-editor/button/image-Widgets in Elementor-
	 * Spalten. Zweck: im Editor klick- und änderbar ohne HTML-Kenntnisse.
	 * ====================================================================== */

	/** Elementor heading widget. */
	public static function wid_heading( $title, $tag = 'h2', $css_class = '', $align = 'left' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'heading',
			'settings'   => array(
				'title'       => $title,
				'header_size' => $tag,
				'align'       => $align,
				'_css_classes'=> $css_class,
			),
		);
	}

	/** Elementor text-editor widget (content is WYSIWYG-editable). */
	public static function wid_text( $html, $css_class = '' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'text-editor',
			'settings'   => array(
				'editor'      => wpautop_safe( $html ),
				'_css_classes'=> $css_class,
			),
		);
	}

	/** Elementor button widget. */
	public static function wid_button( $label, $url, $css_class = '' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'button',
			'settings'   => array(
				'text'         => $label,
				'link'         => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
				'_css_classes' => $css_class, // stylen wir via Theme-CSS (.es-btn-*)
			),
		);
	}

	/** Elementor image widget. */
	public static function wid_image( $src_or_id = '', $alt = '' ) {
		$img = array( 'url' => is_string( $src_or_id ) ? $src_or_id : '', 'id' => is_numeric( $src_or_id ) ? (int) $src_or_id : '' );
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'image',
			'settings'   => array(
				'image'      => $img,
				'image_size' => 'full',
				'align'      => 'center',
				'caption_source' => 'none',
			),
		);
	}

	/** Elementor inline html widget (for purely decorative markup without editable text). */
	public static function wid_html( $html, $css_class = '' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'html',
			'settings'   => array(
				'html'        => $html,
				'_css_classes'=> $css_class,
			),
		);
	}

	/** Elementor shortcode widget. */
	public static function wid_shortcode( $shortcode, $css_class = '' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'shortcode',
			'settings'   => array(
				'shortcode'   => $shortcode,
				'_css_classes'=> $css_class,
			),
		);
	}

	/**
	 * Native section wrapper with css_classes + variant-based background.
	 * $args = [
	 *   'cols'        => [ [...widgets], [...widgets] ],
	 *   'variant'     => ''|'ink'|'warm'|'cool',
	 *   'css_classes' => 'extra classes',
	 *   'padding'     => [top, right, bottom, left] px (default 120/0/120/0 on desktop),
	 *   'content_width' => 1280,
	 *   'gap'         => 'default'|'wider'|'no',
	 *   'column_settings' => optional per-column settings array of arrays
	 *   'column_classes'  => string (added to every column),
	 * ]
	 */
	public static function section_native( $args ) {
		$args = array_merge( array(
			'cols' => array( array() ),
			'variant' => '',
			'css_classes' => '',
			'padding' => array( '120', '0', '120', '0' ),
			'content_width' => 1280,
			'gap' => 'default',
			'column_settings' => array(),
			'column_classes' => '',
		), $args );

		// Overlap-Mechanik: eigene Farbe (es-own-*) + gerundete Unterkante.
		// Der Untergrund (= Farbe der nächsten Sektion) kommt per CSS :has().
		$own     = $args['variant'] ? $args['variant'] : 'paper';
		$overlap = 'es-stage-sec es-own-' . $own . ' es-round-bottom';
		$classes = 'es-stage ' . ( $args['variant'] ? 'es-stage--' . $args['variant'] . ' ' : '' ) . $overlap . ' ' . $args['css_classes'];
		$classes = trim( $classes );

		$settings = array(
			'layout'        => 'boxed',
			'content_width' => array( 'unit' => 'px', 'size' => (int) $args['content_width'] ),
			'padding'       => array( 'unit' => 'px', 'top' => (string) $args['padding'][0], 'right' => (string) $args['padding'][1], 'bottom' => (string) $args['padding'][2], 'left' => (string) $args['padding'][3], 'isLinked' => false ),
			'gap'           => $args['gap'],
			// Elementor's Advanced tab: "CSS Classes" field. Elementor rendert
			// beide Schlüssel – _css_classes wird zuverlässig an die section
			// weitergereicht, css_classes ist für ältere Versionen.
			'css_classes'  => $classes,
			'_css_classes' => $classes,
		);
		// Kein Inline-Hintergrund: Füllung via ::before (es-own-*), Untergrund
		// (= nächste Sektion) via CSS :has(). Sonst würde Inline-BG das :has-CSS
		// überschreiben und die Ecken-Überlappung ginge verloren.

		$col_cfgs = array();
		foreach ( $args['cols'] as $i => $widgets ) {
			$col_settings = isset( $args['column_settings'][ $i ] ) ? (array) $args['column_settings'][ $i ] : array();
			if ( ! empty( $args['column_classes'] ) ) {
				$cc = trim( ( $col_settings['css_classes'] ?? '' ) . ' ' . $args['column_classes'] );
				$col_settings['css_classes']  = $cc;
				$col_settings['_css_classes'] = $cc;
			}
			$col_cfgs[] = array( 'widgets' => $widgets, 'settings' => $col_settings );
		}

		return self::section( $col_cfgs, $settings );
	}

	/**
	 * Native editorial hero – heading + lead + buttons (+ optional html claims grid)
	 * $args = [
	 *   eyebrow, headline_html (mit <br>, <em>, <span class=text-bg-green>),
	 *   lead, buttons [[label, url, style]], claims [[wert, label]], padding
	 * ]
	 */
	public static function hero_native( $args ) {
		$args = array_merge( array(
			'eyebrow' => '', 'headline_html' => '', 'lead' => '',
			'buttons' => array(), 'claims' => array(), 'padding' => 'default',
			'claims_settings' => array(),
			'side_html' => '',
			'side_settings' => array(),
		), $args );

		$pad = ( 'tall' === $args['padding'] ) ? array( '140', '0', '140', '0' ) : ( ( 'short' === $args['padding'] ) ? array( '100', '0', '100', '0' ) : array( '120', '0', '120', '0' ) );

		$widgets = array();
		if ( $args['eyebrow'] ) {
			$widgets[] = self::wid_heading( $args['eyebrow'], 'p', 'es-eyebrow es-eyebrow--paper' );
		}
		if ( $args['headline_html'] ) {
			$widgets[] = self::wid_heading( $args['headline_html'], 'h1', 'es-hero__title' );
		}
		if ( $args['lead'] ) {
			$widgets[] = self::wid_text( $args['lead'], 'es-hero__lead' );
		}
		if ( ! empty( $args['buttons'] ) ) {
			foreach ( $args['buttons'] as $b ) {
				$style = isset( $b[2] ) ? $b[2] : 'paper';
				$widgets[] = self::wid_button( $b[0] . ' →', $b[1], 'es-hero__button es-btn--' . $style );
			}
		}
		// Claims-Grid als native widgets pro Spalte – editierbar im Elementor.
		if ( ! empty( $args['claims'] ) ) {
			$claim_cols = array();
			foreach ( $args['claims'] as $c ) {
				$claim_cols[] = array(
					self::wid_heading( $c[0], 'div', 'es-hero-claims__wert' ),
					self::wid_heading( $c[1], 'div', 'es-hero-claims__label' ),
				);
			}
			$widgets[] = self::section( array_map( function( $cw ) { return array( 'widgets' => $cw ); }, $claim_cols ), array_merge( array(
				'structure'     => '40',
				'layout'        => 'boxed',
				'gap'           => 'default',
				'css_classes'   => 'es-hero-claims-inner',
				'_css_classes'  => 'es-hero-claims-inner',
				'padding'       => array( 'unit' => 'px', 'top' => '40', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
			), $args['claims_settings'] ) );
			// Mark it as inner section
			$last_idx = count( $widgets ) - 1;
			$widgets[ $last_idx ]['isInner'] = true;
		}
		// Mit rechter Spalte (z.B. Stat-Cards) → 2-Spalten-Hero; sonst einspaltig.
		if ( $args['side_html'] ) {
			return self::section_native( array(
				'cols' => array( $widgets, array( self::html( $args['side_html'], $args['side_settings'] ) ) ),
				'variant' => 'ink',
				'css_classes' => 'es-hero es-hero--split',
				'padding' => $pad,
				'content_width' => 1280,
				'gap' => 'wider',
				'column_settings' => array(
					array( '_column_size' => 42, 'css_classes' => 'es-hero__main',  '_css_classes' => 'es-hero__main' ),
					array( '_column_size' => 58, 'css_classes' => 'es-hero__side',  '_css_classes' => 'es-hero__side' ),
				),
			) );
		}

		return self::section_native( array(
			'cols' => array( $widgets ),
			'variant' => 'ink',
			'css_classes' => 'es-hero',
			'padding' => $pad,
			'content_width' => 1280,
		) );
	}

	/**
	 * Native split-text (C1): 2 cols, col1 eyebrow+H2, col2 paragraphs.
	 * $args = [ eyebrow, title, paragraphs[], variant, padding ]
	 */
	public static function split_native( $args ) {
		$args = array_merge( array(
			'eyebrow' => '', 'title' => '', 'title_html' => '',
			'paragraphs' => array(), 'variant' => '', 'padding' => 'default',
			'extra_after' => null, // optional widget(s) to append after paragraphs
		), $args );

		$pad = ( 'short' === $args['padding'] ) ? array( '80', '0', '80', '0' ) : array( '120', '0', '120', '0' );
		$title_class = 'es-split__title';

		$col1 = array();
		if ( $args['eyebrow'] ) {
			$col1[] = self::wid_heading( $args['eyebrow'], 'p', 'es-eyebrow' . ( 'ink' === $args['variant'] ? ' es-eyebrow--paper' : '' ) );
		}
		if ( $args['title_html'] ) {
			$col1[] = self::wid_heading( $args['title_html'], 'h2', $title_class );
		} elseif ( $args['title'] ) {
			$col1[] = self::wid_heading( $args['title'], 'h2', $title_class );
		}

		$col2 = array();
		foreach ( (array) $args['paragraphs'] as $i => $p ) {
			$col2[] = self::wid_text( $p, 0 === $i ? 'es-split__lead' : 'es-split__body' );
		}
		if ( ! empty( $args['extra_after'] ) ) {
			foreach ( (array) $args['extra_after'] as $w ) { $col2[] = $w; }
		}

		return self::section_native( array(
			'cols' => array( $col1, $col2 ),
			'variant' => $args['variant'],
			'css_classes' => 'es-split',
			'padding' => $pad,
			'gap' => 'wider',
			'column_settings' => array(
				array( '_column_size' => 52 ),
				array( '_column_size' => 48 ),
			),
		) );
	}

	/**
	 * Native dark CTA (G3) – 2 cols, col1 eyebrow+H2, col2 buttons.
	 */
	public static function cta_dark_native( $args = array() ) {
		$args = array_merge( array(
			'eyebrow' => 'Kontakt',
			'title_html' => 'Haben wir Ihr Interesse geweckt?',
			'sub' => 'Möchten Sie uns kennenlernen?',
			'buttons' => array(
				array( 'Kontakt aufnehmen', '/kontakt/', 'paper' ),
				array( 'Unser Team', '/team/', 'ghost-paper' ),
			),
		), $args );

		$title_full = $args['title_html'];
		if ( $args['sub'] ) {
			$title_full .= '<br><span class="es-cta__sub">' . $args['sub'] . '</span>';
		}

		$col1 = array(
			self::wid_heading( $args['eyebrow'], 'p', 'es-eyebrow es-eyebrow--accent' ),
			self::wid_heading( $title_full, 'h2', 'es-cta__title' ),
		);

		// Native Elementor Button-Widgets statt HTML
		$col2 = array();
		foreach ( $args['buttons'] as $b ) {
			$style = isset( $b[2] ) ? $b[2] : 'paper';
			$col2[] = self::wid_button( $b[0] . ' →', $b[1], 'es-cta__button es-btn--' . $style );
		}

		return self::section_native( array(
			'cols' => array( $col1, $col2 ),
			'variant' => 'ink',
			'css_classes' => 'es-cta',
			'padding' => array( '120', '0', '120', '0' ),
			'gap' => 'wider',
			'column_settings' => array(
				array( '_column_size' => 66, 'align_self' => 'flex-end' ),
				array( '_column_size' => 34, 'align_self' => 'flex-end' ),
			),
		) );
	}

	/**
	 * Native cards grid (C2/pillars): n Spalten, jede hat number+heading+text.
	 * $items = [ [number, title, desc], ... ]
	 */
	public static function cards_native( $items, $variant = '', $extra_classes = '' ) {
		$cols = array();
		$col_settings = array();
		foreach ( $items as $it ) {
			$num   = isset( $it[0] ) ? $it[0] : '';
			$title = isset( $it[1] ) ? $it[1] : '';
			$desc  = isset( $it[2] ) ? $it[2] : '';
			$widgets = array();
			if ( $num ) {
				$widgets[] = self::wid_html( '<div class="es-card-pillar__num">' . esc_html( $num ) . ' –</div>' );
			}
			$widgets[] = self::wid_heading( $title, 'h3', 'es-card-pillar__title' );
			$widgets[] = self::wid_text( $desc, 'es-card-pillar__desc' );
			$cols[] = $widgets;
			$col_settings[] = array();
		}
		return self::section_native( array(
			'cols' => $cols,
			'variant' => $variant,
			'css_classes' => trim( 'es-cards-grid ' . $extra_classes ),
			'column_classes' => 'es-card-pillar',
			'padding' => array( '40', '0', '120', '0' ),
			'gap' => 'default',
			'column_settings' => $col_settings,
		) );
	}

}

/** Safe wpautop-like helper. */
if ( ! function_exists( 'wpautop_safe' ) ) {
	function wpautop_safe( $text ) {
		if ( function_exists( 'wpautop' ) ) {
			if ( preg_match( '/<(p|ul|ol|h[1-6]|div|blockquote)/i', $text ) ) { return $text; }
			return wpautop( $text );
		}
		return $text;
	}
}
