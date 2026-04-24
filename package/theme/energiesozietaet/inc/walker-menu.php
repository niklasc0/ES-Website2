<?php
/**
 * Minimal nav walker.
 *
 * @package Energiesozietaet
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class ES_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '<ul class="sub-menu">';
	}
	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$output .= '</ul>';
	}
}
