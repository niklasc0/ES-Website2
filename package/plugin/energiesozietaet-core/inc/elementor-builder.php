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
			$out_cols[] = array(
				'id'       => self::id(),
				'elType'   => 'column',
				'settings' => array_merge( array( '_column_size' => $size, '_inline_size' => null ), $col_settings ),
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

	/** Raw HTML block — most layout work happens here, styled by theme CSS classes. */
	public static function html( $html ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'html',
			'settings'   => array( 'html' => $html ),
		);
	}

	/** Full-width container holding an HTML widget; optional background variant. */
	public static function section_html( $html, $variant = '' ) {
		$settings = array(
			'layout'        => 'full_width',
			'content_width' => array( 'unit' => 'px', 'size' => 1280 ),
			'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => false ),
			'gap'           => 'no',
		);
		if ( 'ink' === $variant ) {
			$settings['background_background'] = 'classic';
			$settings['background_color']      = '#0E1A2B';
		} elseif ( 'warm' === $variant ) {
			$settings['background_background'] = 'classic';
			$settings['background_color']      = '#F6F4EF';
		} elseif ( 'cool' === $variant ) {
			$settings['background_background'] = 'classic';
			$settings['background_color']      = '#F3F5F8';
		}
		return self::section( array( array( 'widgets' => array( self::html( $html ) ) ) ), $settings );
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
				'title_color'                   => '#0E1A2B',
				'tab_content_color'             => '#5A6577',
			),
		);
	}

	/* =========================================================================
	 * Compound builders — whole sections returned as HTML blocks.
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
			$html .= '<p style="max-width:780px;font-size:20px;line-height:1.55;color:rgba(255,255,255,0.78);font-weight:300;margin:0 0 36px;">' . $args['lead'] . '</p>';
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
			$color = 'ink' === $args['variant'] ? ( 0 === $i ? 'rgba(255,255,255,0.82)' : 'rgba(255,255,255,0.6)' ) : ( 0 === $i ? '#0E1A2B' : '#5A6577' );
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
			'n' => '01', 'title' => '', 'sub' => '', 'lede' => '', 'link' => '',
			'topics' => array(), 'stripe' => 'odd', 'image_html' => '',
		), $args );
		$warm = 'even' === $args['stripe'] ? ' es-bereich--warm' : '';

		$topics_html = '';
		foreach ( $args['topics'] as $j => $topic ) {
			$num = str_pad( (string) ( $j + 1 ), 2, '0', STR_PAD_LEFT );
			$topics_html .= '<a class="es-bereich__topic" href="#' . esc_attr( sanitize_title( $topic[0] ) ) . '">';
			$topics_html .= '<div class="es-bereich__topic-name"><span></span><h3>' . esc_html( $topic[0] ) . '</h3></div>';
			$topics_html .= '<p class="es-bereich__topic-desc">' . esc_html( $topic[1] ) . '</p>';
			$topics_html .= '</a>';
		}

		$img_html = $args['image_html'];
		if ( ! $img_html ) {
			$img_html = '<div class="es-bereich__img"><div class="es-ph-cat"><span>' . esc_html( $args['title'] ) . '</span></div></div>';
		}

		$html  = '<section class="es-bereich' . $warm . '"><div class="es-wrap"><div class="es-bereich__inner">';
		$html .= '<div class="es-bereich__top">';
		$html .= '<div>';
		$html .= '<div class="es-bereich__meta"><span class="es-bereich__num">' . esc_html( $args['n'] ) . ' / 03</span><span class="es-bereich__sep"></span><span class="es-bereich__sub">' . esc_html( $args['sub'] ) . '</span></div>';
		$html .= '<h2 class="es-bereich__title">' . esc_html( $args['title'] ) . '</h2>';
		$html .= '<p class="es-bereich__lede">' . esc_html( $args['lede'] ) . '</p>';
		if ( $args['link'] ) {
			$html .= '<a class="es-btn es-btn--primary" href="' . esc_url( $args['link'] ) . '">Zu ' . esc_html( $args['title'] ) . ' →</a>';
		}
		$html .= '</div>';
		$html .= '<div>' . $img_html . '</div>';
		$html .= '</div>';

		if ( $topics_html ) {
			$html .= '<div class="es-bereich__topics-label">Themenfelder · ' . esc_html( $args['title'] ) . '</div>';
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
			'title_html' => 'Sprechen Sie mit uns.',
			'sub' => 'Unaufgeregt, direkt, fachlich.',
			'buttons' => array(
				array( 'Termin vereinbaren', '/kontakt/', 'paper' ),
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
	 * Pullquote section — huge centered quote on warm background.
	 */
	public static function pullquote( $quote_html, $attribution = '' ) {
		$html  = '<div class="es-wrap" style="padding:140px 0;text-align:center;">';
		$html .= '<blockquote style="margin:0;font-size:clamp(28px,3.6vw,56px);line-height:1.2;font-weight:300;letter-spacing:-0.025em;max-width:1040px;margin-inline:auto;color:#0E1A2B;">' . $quote_html . '</blockquote>';
		if ( $attribution ) {
			$html .= '<div style="margin-top:48px;font-size:13px;letter-spacing:0.18em;text-transform:uppercase;color:#8591A3;">' . esc_html( $attribution ) . '</div>';
		}
		$html .= '</div>';
		return self::section_html( $html, 'warm' );
	}

	/**
	 * GF Quote with portrait placeholder (Home page).
	 */
	public static function gf_quote( $args ) {
		$args = array_merge( array(
			'quote' => '',
			'name'  => 'Prof. Dr. Sven-Joachim Otto',
			'role'  => 'Partner · Geschäftsführer',
			'photo_shortcode' => '[es_team_photo slug="prof-dr-sven-joachim-otto" size=120]',
		), $args );

		$html  = '<div class="es-wrap" style="padding:140px 0;">';
		$html .= '<div class="es-eyebrow" style="margin-bottom:48px;">Unser Anspruch</div>';
		$html .= '<div style="display:grid;grid-template-columns:auto 1fr;gap:56px;align-items:center;">';
		$html .= '<div style="width:180px;height:180px;border-radius:999px;overflow:hidden;background:#F6F4EF;">' . do_shortcode( $args['photo_shortcode'] ) . '</div>';
		$html .= '<div>';
		$html .= '<blockquote style="margin:0;font-size:clamp(26px,2.8vw,40px);line-height:1.25;font-weight:300;letter-spacing:-0.02em;max-width:900px;color:#0E1A2B;">„' . $args['quote'] . '"</blockquote>';
		$html .= '<div style="margin-top:32px;">';
		$html .= '<div style="font-size:16px;font-weight:500;">' . esc_html( $args['name'] ) . '</div>';
		$html .= '<div style="font-size:13px;color:#5A6577;margin-top:2px;">' . esc_html( $args['role'] ) . '</div>';
		$html .= '</div></div></div></div>';
		return self::section_html( $html, 'warm' );
	}

	/** Section head (eyebrow + h2 + lede), centered or left. */
	public static function section_head( $eyebrow, $title_html, $lede = '', $align = 'left', $variant = '' ) {
		$text_align = 'center' === $align ? 'text-align:center;' : '';
		$eyebrow_class = 'ink' === $variant ? 'es-eyebrow es-eyebrow--paper' : 'es-eyebrow';
		$title_color = 'ink' === $variant ? '#FFFFFF' : '#0E1A2B';
		$lede_color  = 'ink' === $variant ? 'rgba(255,255,255,0.7)' : '#5A6577';
		$max = 'center' === $align ? 'margin-inline:auto;' : '';

		$html  = '<div class="es-wrap" style="padding-top:120px;padding-bottom:48px;' . $text_align . '">';
		$html .= '<div class="' . esc_attr( $eyebrow_class ) . '" style="margin-bottom:20px;">' . esc_html( $eyebrow ) . '</div>';
		$html .= '<h2 style="font-size:clamp(32px,4.2vw,52px);line-height:1.05;font-weight:400;letter-spacing:-0.03em;max-width:720px;' . $max . 'color:' . $title_color . ';margin:0;">' . $title_html . '</h2>';
		if ( $lede ) {
			$html .= '<p style="font-size:17px;line-height:1.55;margin-top:24px;color:' . $lede_color . ';max-width:620px;' . $max . '">' . $lede . '</p>';
		}
		$html .= '</div>';
		return self::section_html( $html, $variant );
	}

	/** Shortcode inside a boxed wrap with optional background. */
	public static function wrap_shortcode( $shortcode, $variant = '', $padding = '0 0 120px 0' ) {
		$html = '<div class="es-wrap" style="padding:' . $padding . ';">' . do_shortcode( $shortcode ) . '</div>';
		// Do not expand shortcode at build-time — let the page evaluate it on render.
		$html = '<div class="es-wrap" style="padding:' . $padding . ';">' . $shortcode . '</div>';
		return self::section_html( $html, $variant );
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
