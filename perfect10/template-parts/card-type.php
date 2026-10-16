<?php
/**
 * Profile-type tile.
 *
 * Expected $args:
 *   term   WP_Term A p10_category term.
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

$index  = isset( $args['index'] ) ? (int) $args['index'] : 0;
$reveal = ! isset( $args['reveal'] ) || $args['reveal'];
$delay  = $index ? ( ( $index - 1 ) % 6 ) : 0;

$link    = get_term_link( $term );
$number  = (string) p10_term_meta( $term, 'number', '' );
$short   = (string) p10_term_meta( $term, 'short', '' );
$label   = $short ? $short : $term->name;
$image   = p10_term_image( $term, 'p10-card' );
$classes = $reveal ? 'p10-type p10-reveal' : 'p10-type';

if ( is_wp_error( $link ) ) {
	return;
}
?>
<a class="<?php echo esc_attr( $classes ); ?>"<?php echo $reveal ? ' data-delay="' . esc_attr( $delay ) . '"' : ''; ?> href="<?php echo esc_url( $link ); ?>">
	<img class="p10-type__img" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" decoding="async">
	<span class="p10-type__inner">
		<?php if ( $number || $index ) : ?>
			<span class="p10-type__num"><?php echo esc_html( $number ? $number : p10_number( $index ) ); ?></span>
		<?php endif; ?>
		<span class="p10-type__label"><?php echo esc_html( $label ); ?></span>
		<?php if ( $term->count ) : ?>
			<span class="p10-type__count">
				<?php
				echo esc_html( sprintf(
					/* translators: %s: number of profiles. */
					_n( '%s profile', '%s profiles', (int) $term->count, 'perfect10' ),
					number_format_i18n( $term->count )
				) );
				?>
			</span>
		<?php endif; ?>
	</span>
</a>
