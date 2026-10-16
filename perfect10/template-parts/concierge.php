<?php
/** Accessible assistant replaces only the floating contact launcher. Other theme forms remain. */
if ( ! defined( 'ABSPATH' ) || ! p10_option( 'concierge_enabled', 1 ) ) {
	return; }
?>
<div class="p10-concierge p10-chat" data-p10-chat>
	<button type="button" class="p10-concierge__btn" data-chat-open aria-haspopup="dialog" aria-controls="p10-chat-dialog" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open website assistant', 'perfect10' ); ?>">
		<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20 11a8 8 0 0 1-8 8H5l-4 3 2-7a8 8 0 1 1 17-4Z"/><path d="M7 10h10M7 14h6"/></svg>
	</button>
	<dialog id="p10-chat-dialog" class="p10-chat-dialog" aria-labelledby="p10-chat-title">
		<div class="p10-concierge__head"><h2 id="p10-chat-title" class="p10-concierge__title"><?php esc_html_e( 'Website Assistant', 'perfect10' ); ?></h2><button type="button" data-chat-close aria-label="Close assistant">×</button></div>
		<p class="p10-chat-notice">Published website information only. Chats are not saved by this theme. Do not enter sensitive personal details. Voice input may use your browser’s external speech service; no audio is sent by this theme to an AI provider.</p>
		<p class="p10-chat-provider" data-chat-provider></p>
		<div class="p10-chat-log" role="log" aria-live="polite" aria-relevant="additions" tabindex="0" aria-label="Conversation" data-chat-log></div>
		<p role="status" data-chat-status></p>
		<form data-chat-form><label for="p10-chat-input">Your message</label><div class="p10-chat-input"><input id="p10-chat-input" name="message" maxlength="600" autocomplete="off" required><button type="submit">Send</button></div></form>
		<div class="p10-chat-tools" aria-label="Assistant controls">
			<button type="button" data-chat-mic hidden>Use microphone</button>
			<button type="button" data-chat-mute aria-pressed="true">Voice muted</button>
			<button type="button" data-chat-stop>Stop voice</button>
			<button type="button" data-chat-repeat>Repeat response</button>
			<button type="button" data-chat-reset>Reset</button>
			<button type="button" data-chat-follow hidden>Request follow-up</button>
		</div>
		<p class="p10-chat-notice" data-chat-voice-note>Text chat works without voice. Speech recognition requires a supported browser, microphone permission and usually HTTPS.</p>
	</dialog>
</div>
