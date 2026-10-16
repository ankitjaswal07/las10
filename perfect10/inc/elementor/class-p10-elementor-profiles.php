<?php
/** Dynamic Elementor component. */
if ( ! defined( 'ABSPATH' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
	exit;
}

class P10_Elementor_Profiles extends P10_Elementor_Widget {
	public function get_name() {
		return 'p10-profiles'; }
	public function get_title() {
		return __( 'P10 Profile Listing', 'perfect10' ); }
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Published profiles', 'perfect10' ) ) );
		$this->add_control(
			'limit',
			array(
				'label'   => __( 'Maximum profiles', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min'     => 1,
				'max'     => 48,
			)
		);
		foreach ( array(
			'p10_county'   => 'County',
			'p10_city'     => 'City',
			'p10_category' => 'Category',
		) as $tax => $label ) {
			$options = array( '' => __( 'All', 'perfect10' ) );
			$terms   = get_terms(
				array(
					'taxonomy'   => $tax,
					'hide_empty' => false,
				)
			);
			if ( ! is_wp_error( $terms ) ) {
				foreach ( $terms as $term ) {
					$options[ $term->slug ] = $term->name; }
			}
			$this->add_control(
				$tax,
				array(
					'label'   => $label,
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => $options,
					'default' => '',
				)
			);
		}
		$this->add_control(
			'context',
			array(
				'label'   => __( 'Use current location/category archive', 'perfect10' ),
				'type'    => \Elementor\Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->columns();
		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Profile title color', 'perfect10' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .p10-card__title' => 'color:{{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}
	protected function render() {
		$s       = $this->get_settings_for_display();
		$args    = array(
			'post_type'      => 'p10_profile',
			'post_status'    => 'publish',
			'has_password'   => false,
			'posts_per_page' => max( 1, min( 48, (int) $s['limit'] ) ),
			'no_found_rows'  => true,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
		);
		$filters = array();
		foreach ( array( 'p10_county', 'p10_city', 'p10_category' ) as $tax ) {
			if ( ! empty( $s[ $tax ] ) ) {
				$filters[ $tax ] = $s[ $tax ]; }
		}
		$term = get_queried_object();
		if ( 'yes' === $s['context'] && $term instanceof WP_Term && in_array( $term->taxonomy, array( 'p10_county', 'p10_city', 'p10_category', 'p10_nationality' ), true ) ) {
			$filters[ $term->taxonomy ] = $term->slug; }
		foreach ( $filters as $tax => $slug ) {
			$args['tax_query'][] = array(
				'taxonomy' => $tax,
				'field'    => 'slug',
				'terms'    => sanitize_title( $slug ),
			); }
		$query = new WP_Query( $args );
		echo '<div class="p10-grid p10-dynamic-grid">';
		foreach ( $query->posts as $profile ) {
			get_template_part(
				'template-parts/card-profile',
				null,
				array(
					'profile' => $profile,
					'reveal'  => false,
				)
			); }
		if ( ! $query->posts ) {
			echo '<p>' . esc_html__( 'No published profiles match these filters.', 'perfect10' ) . '</p>'; }
		echo '</div>';
	}
}
