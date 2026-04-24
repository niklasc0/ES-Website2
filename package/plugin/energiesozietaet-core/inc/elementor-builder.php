<?php
/**
 * Programmatic Elementor-JSON builder.
 *
 * Generates Elementor-compatible page data using only standard Elementor widgets
 * (no third-party addons required). All IDs are generated as 7-char hex.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

class ESC_Elementor_Builder {

	public static function id() {
		return substr( md5( uniqid( '', true ) . wp_generate_password( 8, false ) ), 0, 7 );
	}

	/**
	 * Section wrapper with 1+ columns.
	 *
	 * @param array $columns Array of column configs — each a list of widget arrays.
	 * @param array $section_settings
	 */
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
				'text'               => $label,
				'link'               => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
				'align'              => $align,
				'button_css_id'      => '',
				'background_color'   => 'primary' === $style ? '#94d707' : ( 'dark' === $style ? '#0f1720' : '' ),
				'button_text_color'  => 'primary' === $style ? '#0f1720' : ( 'dark' === $style ? '#ffffff' : '' ),
				'border_radius'      => array( 'unit' => 'px', 'top' => '999', 'right' => '999', 'bottom' => '999', 'left' => '999' ),
				'text_padding'       => array( 'unit' => 'px', 'top' => '14', 'right' => '22', 'bottom' => '14', 'left' => '22', 'isLinked' => false ),
				'typography_typography' => 'custom',
				'typography_font_weight' => '600',
				'typography_font_size'   => array( 'unit' => 'px', 'size' => 15 ),
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

	public static function divider( $extra = array() ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'divider',
			'settings'   => array_merge( array(
				'color' => '#94d707', 'weight' => array( 'unit'=>'px','size'=>2 ), 'width' => array( 'unit'=>'px','size'=>60 ), 'align' => 'left',
			), $extra ),
		);
	}

	public static function image( $url_or_id, $extra = array() ) {
		$img = is_numeric( $url_or_id )
			? array( 'id' => (int) $url_or_id, 'url' => wp_get_attachment_url( (int) $url_or_id ) )
			: array( 'url' => $url_or_id, 'id' => '' );
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'image',
			'settings'   => array_merge( array( 'image' => $img, 'image_size' => 'large' ), $extra ),
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

	public static function icon_list( $items, $extra = array() ) {
		$list = array();
		foreach ( $items as $t ) {
			$list[] = array(
				'_id'       => self::id(),
				'text'      => $t,
				'selected_icon' => array( 'value' => 'fas fa-check', 'library' => 'fa-solid' ),
			);
		}
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'icon-list',
			'settings'   => array_merge( array(
				'icon_list' => $list,
				'icon_color' => '#94d707',
				'space_between' => array( 'unit' => 'px', 'size' => 10 ),
				'icon_align' => 'left',
				'text_color' => '#1a1f26',
			), $extra ),
		);
	}

	public static function icon_box( $title, $description, $url = '', $icon = 'fas fa-angle-right' ) {
		return array(
			'id'         => self::id(),
			'elType'     => 'widget',
			'widgetType' => 'icon-box',
			'settings'   => array(
				'title_text'       => $title,
				'description_text' => $description,
				'selected_icon'    => array( 'value' => $icon, 'library' => 'fa-solid' ),
				'link'             => array( 'url' => $url, 'is_external' => '', 'nofollow' => '' ),
				'view'             => 'stacked',
				'primary_color'    => '#94d707',
				'title_color'      => '#0f1720',
				'description_color'=> '#5a6270',
			),
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
				'border_width'                  => array( 'unit' => 'px', 'size' => 1 ),
				'border_color'                  => '#e5e7e0',
				'title_color'                   => '#0f1720',
				'tab_content_color'             => '#5a6270',
				'title_active_background_color' => '#f6f7f5',
			),
		);
	}

	/**
	 * Convenience: full-width dark hero with eyebrow, heading, lead, buttons.
	 */
	public static function hero( $args ) {
		$args = wp_parse_args( $args, array(
			'eyebrow' => '', 'heading' => '', 'lead' => '', 'buttons' => array(),
		) );
		$widgets = array();
		if ( $args['eyebrow'] ) {
			$widgets[] = self::heading( $args['eyebrow'], 'default', array(
				'header_size'     => 'p',
				'title_color'     => '#94d707',
				'typography_typography'    => 'custom',
				'typography_font_size'     => array( 'unit' => 'px', 'size' => 14 ),
				'typography_letter_spacing'=> array( 'unit' => 'px', 'size' => 2.6 ),
				'typography_text_transform'=> 'uppercase',
				'typography_font_weight'   => '600',
				'_margin'         => array( 'unit'=>'px','top'=>'0','bottom'=>'12','left'=>'0','right'=>'0','isLinked' => false ),
			) );
		}
		if ( $args['heading'] ) {
			$widgets[] = self::heading( $args['heading'], 'xxl', array(
				'header_size'   => 'h1',
				'title_color'   => '#ffffff',
				'typography_typography' => 'custom',
				'typography_font_family' => 'Fraunces',
				'typography_font_weight' => '450',
				'typography_font_size'   => array( 'unit' => 'em', 'size' => 3.8 ),
				'typography_line_height' => array( 'unit' => 'em', 'size' => 1.1 ),
				'_margin'        => array( 'unit' => 'px', 'top' => '0', 'bottom' => '16', 'left' => '0', 'right' => '0', 'isLinked' => false ),
			) );
		}
		if ( $args['lead'] ) {
			$widgets[] = self::text( $args['lead'], array(
				'text_color'      => 'rgba(255,255,255,0.78)',
				'typography_typography' => 'custom',
				'typography_font_size'  => array( 'unit' => 'px', 'size' => 18 ),
				'typography_line_height'=> array( 'unit' => 'em', 'size' => 1.6 ),
			) );
		}
		if ( $args['buttons'] ) {
			foreach ( $args['buttons'] as $b ) {
				$widgets[] = self::button( $b[0], $b[1], isset( $b[2] ) ? $b[2] : 'primary' );
			}
		}
		return self::section( array( array( 'widgets' => $widgets ) ), array(
			'layout'                    => 'boxed',
			'content_width'             => array( 'unit' => 'px', 'size' => 1240 ),
			'background_background'     => 'classic',
			'background_color'          => '#0f1720',
			'padding'                   => array( 'unit'=>'px','top'=>'120','right'=>'24','bottom'=>'110','left'=>'24','isLinked' => false ),
			'background_overlay_background' => 'gradient',
			'background_overlay_color'  => '#94d707',
			'background_overlay_color_b'=> '#0f1720',
			'background_overlay_color_stop' => array( 'unit'=>'%','size'=>0 ),
			'background_overlay_color_b_stop' => array( 'unit'=>'%','size'=>55 ),
			'background_overlay_gradient_type' => 'radial',
			'background_overlay_gradient_position' => 'top right',
			'background_overlay_opacity' => array( 'unit' => 'px', 'size' => 0.18 ),
		) );
	}
}

/**
 * Safe wpautop-like helper that we can call before WP is fully loaded during activation.
 */
if ( ! function_exists( 'wpautop_safe' ) ) {
	function wpautop_safe( $text ) {
		if ( function_exists( 'wpautop' ) ) {
			// Skip if already contains block tags.
			if ( preg_match( '/<(p|ul|ol|h[1-6]|div|blockquote)/i', $text ) ) { return $text; }
			return wpautop( $text );
		}
		return $text;
	}
}
