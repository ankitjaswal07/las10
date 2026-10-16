<?php
/** Dynamic Elementor component. */
if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

abstract class P10_Elementor_Widget extends \Elementor\Widget_Base {
	public function get_categories() {
		return array( 'general' ); }
	public function get_icon() {
		return 'eicon-posts-grid'; }
	protected function columns() {
		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'perfect10' ),
				'type'           => \Elementor\Controls_Manager::SELECT,
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'selectors'      => array( '{{WRAPPER}} .p10-dynamic-grid' => 'grid-template-columns:repeat({{VALUE}}, minmax(0,1fr));' ),
			)
		);
		$this->add_responsive_control(
			'gap',
			array(
				'label'     => __( 'Gap', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors' => array( '{{WRAPPER}} .p10-dynamic-grid' => 'gap:{{SIZE}}{{UNIT}};' ),
			)
		);
	}
	protected function current_profile() {
		$id = get_the_ID();
		return 'p10_profile' === get_post_type( $id ) && 'publish' === get_post_status( $id ) && ! post_password_required( $id ) ? $id : 0;
	}
}
