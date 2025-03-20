<?php

namespace JTK\Correction;

/**
 * This should be a Singleton or a static class.
 * The constructor should not register the shortcode.
 */
class CorrectionFormShortcode {
	private $recaptcha;

	public function __construct( \JTK\ReCaptcha $recaptcha ) {
		$this->recaptcha = $recaptcha;
		// add_shortcode('correction_form', array($this, 'render'));
	}

	public function render( $atts ) {
		ob_start();

		$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;

		if ( $post_id <= 0 ) {
			echo '<p>Invalid post ID.</p>';
			return ob_get_clean();
		}

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
			$this->render_form( $post_id );
		} else {
			$this->handle_form_submission( $post_id );
		}

		return ob_get_clean();
	}

	private function render_form( $post_id ) {
		$nonce              = wp_create_nonce( 'correction_form_nonce' );
		$recaptcha_site_key = $this->recaptcha->get_site_key();

		echo '<form method="post">';
		echo '<input type="hidden" name="post_id" value="' . esc_attr( $post_id ) . '">';
		echo '<input type="hidden" name="correction_form_nonce" value="' . esc_attr( $nonce ) . '">';
		echo '<label for="original_sentence">Original Sentence (Paste Here):</label><br>';
		echo '<textarea name="original_sentence" id="original_sentence" rows="4" cols="50" required></textarea><br><br>';
		echo '<label for="correction">Your Correction:</label><br>';
		echo '<textarea name="correction-text" id="correction" rows="4" cols="50" required></textarea><br><br>';
		echo '<label for="email">Your Email:</label><br>';
		echo '<input type="email" name="correction-email" id="email"><br><br>';
		echo '<input type="hidden" name="recaptcha_token" id="recaptcha_token">';
		echo '<input type="submit" value="Submit">';
		echo '</form>';

		if ( ! empty( $recaptcha_site_key ) ) {
			$this->recaptcha->render_script();
		}
	}

	private function handle_form_submission( $post_id ) {
		if ( ! isset( $_POST['correction_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['correction_form_nonce'] ) ), 'correction_form_nonce' ) ) {
			echo '<p>Security check failed.</p>';
			return;
		}

		$original_sentence = isset( $_POST['original_sentence'] ) ? sanitize_textarea_field( wp_unslash( $_POST['original_sentence'] ) ) : '';
		$correction        = isset( $_POST['correction-text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['correction-text'] ) ) : '';
		$email             = isset( $_POST['correction-email'] ) ? sanitize_email( wp_unslash( $_POST['correction-email'] ) ) : '';
		$recaptcha_token   = isset( $_POST['recaptcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) ) : '';

		if ( empty( $original_sentence ) || empty( $correction ) || empty( $email ) ) {
			echo '<p>Please fill in all required fields.</p>';
			return;
		}

		if ( ! $this->recaptcha->verify( $recaptcha_token ) ) {
			echo '<p>reCAPTCHA verification failed. You might be a bot.</p>';
			return;
		}

		// create a new correction post
		$correction_post = array(
			'post_type'    => 'correction',
			'post_status'  => 'pending',
			'post_title'   => 'Correction for Post ID: ' . $post_id,
			'post_content' => "Original Sentence:\n" . $original_sentence . "\n\nCorrection:\n" . $correction . "\n\nEmail: " . $email,
			'meta_input'   => array(
				'original_post_id'  => $post_id,
				'original_sentence' => $original_sentence,
				'correction'        => $correction,
				'email'             => $email,
			),
		);

		$result = wp_insert_post( $correction_post, true );

		// send email notification to admin
		$admin_email = get_option( 'admin_email' );
		$subject     = 'New Correction Submitted';
		$message     = "A new correction has been submitted for post ID: {$post_id}\n\n";
		$message    .= "Original Sentence:\n{$original_sentence}\n\n";
		$message    .= "Correction:\n{$correction}\n\n";
		$message    .= "Email: {$email}\n";

		wp_mail( $admin_email, $subject, $message );

		echo '<p>Thank you for your correction! It has been submitted.</p>';
		return ob_get_clean();
	}
}
