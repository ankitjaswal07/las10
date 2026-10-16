<?php
/**
 * Floating "◆ Message" concierge widget with an inline enquiry form.
 *
 * @package Perfect10
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! p10_option( 'concierge_enabled', 1 ) ) {
	return;
}

$label = (string) p10_option( 'concierge_label', __( 'Message', 'perfect10' ) );
$note  = (string) p10_option( 'concierge_note', '' );
?>
<div class="p10-concierge" data-p10-concierge>

	<div class="p10-concierge__panel" role="dialog" aria-label="<?php esc_attr_e( 'Concierge', 'perfect10' ); ?>" aria-hidden="true">
		<div class="p10-concierge__head">
			<h2 class="p10-concierge__title"><?php esc_html_e( 'Concierge', 'perfect10' ); ?></h2>
			<button type="button" class="p10-concierge__close" data-p10-concierge-close aria-label="<?php esc_attr_e( 'Close', 'perfect10' ); ?>">&times;</button>
		</div>

		<?php p10_the_form( 'concierge', array( 'submit' => __( 'Send message', 'perfect10' ), 'class' => 'p10-form--compact' ) ); ?>

		<?php if ( $note ) : ?>
			<p class="p10-concierge__note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>
	</div>

	<button type="button" class="p10-concierge__btn" data-p10-concierge-open aria-expanded="false">
		<?php echo esc_html( $label ); ?>
	</button>

</div>
