<?php
/** Dynamic Elementor component. */
if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

class P10_Elementor_Facts extends P10_Elementor_Widget {
	public function get_name() {
		return 'p10-facts'; }
	public function get_title() {
		return __( 'P10 Current Profile Facts', 'perfect10' ); }
	protected function register_controls() {
		$this->start_controls_section( 'facts', array( 'label' => __( 'Profile facts', 'perfect10' ) ) );
		$options = array();
		foreach ( array( 'age', 'height_cm', 'hair', 'eyes', 'dress', 'languages' ) as $key ) {
			$schema          = p10_profile_field_schema();
			$options[ $key ] = $schema[ $key ]['label']; }
		$this->add_control(
			'fields',
			array(
				'label'    => __( 'Visible fields', 'perfect10' ),
				'type'     => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => $options,
				'default'  => array_keys( $options ),
			)
		);
		$this->add_control(
			'color',
			array(
				'label'     => __( 'Text color', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} dl' => 'color:{{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}
	protected function render() {
		$id = $this->current_profile();
		if ( ! $id ) {
			echo '<p>' . esc_html__( 'Select a published profile preview to display facts.', 'perfect10' ) . '</p>';
			return; }
		$schema = p10_profile_field_schema();
		echo '<dl class="p10-profile-specs">';
		foreach ( (array) $this->get_settings_for_display( 'fields' ) as $key ) {
			if ( ! in_array( $key, array( 'age', 'height_cm', 'hair', 'eyes', 'dress', 'languages' ), true ) ) {
				continue; }
			$value = get_post_meta( $id, 'p10_' . $key, true );
			if ( '' !== $value && is_scalar( $value ) ) {
				echo '<dt>' . esc_html( $schema[ $key ]['label'] ) . '</dt><dd>' . esc_html( $value ) . '</dd>'; }
		}
		echo '</dl>';
	}
}
