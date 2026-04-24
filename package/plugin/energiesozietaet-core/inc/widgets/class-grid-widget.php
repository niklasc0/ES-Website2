<?php
/**
 * Generic Elementor widget wrapping our shortcodes so clients can drag them in.
 *
 * @package Energiesozietaet_Core
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

function es_register_grid_widget( $manager, $shortcode, $class_name, $title, $icon, $default_cols ) {
	if ( ! class_exists( 'Elementor\\Widget_Base' ) ) { return; }
	if ( class_exists( $class_name ) ) { $manager->register( new $class_name() ); return; }

	$code = 'class ' . $class_name . ' extends \\Elementor\\Widget_Base {';
	$code .= 'public function get_name() { return "' . esc_js( $class_name ) . '"; }';
	$code .= 'public function get_title() { return "' . esc_js( $title ) . '"; }';
	$code .= 'public function get_icon() { return "' . esc_js( $icon ) . '"; }';
	$code .= 'public function get_categories() { return array("energiesozietaet"); }';
	$code .= 'protected function register_controls() {
		$this->start_controls_section("sec", array("label" => "Einstellungen"));
		$this->add_control("columns", array(
			"label" => "Spalten",
			"type" => \\Elementor\\Controls_Manager::SELECT,
			"default" => "' . (int) $default_cols . '",
			"options" => array("2" => "2", "3" => "3", "4" => "4"),
		));
		$this->add_control("limit", array(
			"label" => "Anzahl (−1 = alle)",
			"type" => \\Elementor\\Controls_Manager::NUMBER,
			"default" => -1,
		));';
	if ( 'es_einzelleistungen' === $shortcode ) {
		$code .= '$this->add_control("beratungsfeld", array(
			"label" => "Beratungsfeld",
			"type" => \\Elementor\\Controls_Manager::SELECT,
			"default" => "",
			"options" => array("" => "Alle", "rechtsberatung" => "Rechtsberatung", "steuerberatung" => "Steuerberatung", "unternehmensberatung" => "Unternehmensberatung"),
		));';
	}
	$code .= '$this->end_controls_section();
	}
	protected function render() {
		$s = $this->get_settings_for_display();
		$atts = "";
		foreach ($s as $k=>$v) { if (in_array($k, array("columns","limit","beratungsfeld"), true) && $v !== "" && $v !== null) { $atts .= " " . $k . "=\"" . esc_attr($v) . "\""; } }
		echo do_shortcode("[' . esc_js( $shortcode ) . '" . $atts . "]");
	}
	}';
	eval( $code ); // phpcs:ignore
	$manager->register( new $class_name() );
}
