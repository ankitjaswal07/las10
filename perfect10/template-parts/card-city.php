<?php
/**
 * City card.
 *
 * Expected $args:
 *   term   WP_Term A p10_city term.
 *   index  int     1-based position.
 *   reveal bool    Apply the scroll-reveal class.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$args = isset( $args ) ? $args : array();
$term = isset( $args['term'] ) ? $args['term'] : null;

if ( ! $term instanceof WP_Term ) {
	return;
}

$link = get_term_link( $term );
if ( is_wp_error( $link ) ) {
	return;
}

$index        = isset( $args['index'] ) ? (int) $args['index'] : 0;
$reveal       = ! isset( $args['reveal'] ) || $args['reveal'];
$delay        = $index ? ( ( $index - 1 ) % 6 ) : 0;
$county_slug  = p10_city_county_slug( $term );
$county       = $county_slug ? get_term_by( 'slug', $county_slug, 'p10_county' ) : null;
$image        = p10_term_image( $term, 'p10-card-wide' );
$profile_count = (int) $term->count;
$classes      = $reveal ? 'p10-city p10-reveal' : 'p10-city';
?>
<a class="<?php echo esc_attr( $classes ); ?>"<?php echo $reveal ? ' data-delay="' . esc_attr( $delay ) . '"' : ''; ?> href="<?php echo esc_url( $link ); ?>">
	<img class="p10-city__img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" decoding="async">
	<?php if ( $index ) : ?>
		<span class="p10-city__num"><?php echo esc_html( p10_number( $index ) ); ?></span>
	<?php endif; ?>
	<span class="p10-city__inner">
		<?php if ( $county ) : ?>
			<span class="p10-city__county"><?php echo esc_html( $county->name ); ?></span>
		<?php endif; ?>
		<span class="p10-city__name"><?php echo esc_html( $term->name ); ?></span>
		<span class="p10-city__count">
			<?php
			echo esc_html( sprintf(
				/* translators: %s: number of profiles. */
				_n( '%s profile', '%s profiles', $profile_count, 'perfect10' ),
				number_format_i18n( $profile_count )
			) );
			?>
		</span>
	</span>
</a>
