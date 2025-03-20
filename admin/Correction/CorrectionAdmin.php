<?php

namespace JTK\Admin\Correction;

use JTK\Admin\AbstractAdmin;

class CorrectionAdmin extends AbstractAdmin {

	private $recaptcha_admin;

	function __construct( \JTK\Admin\ReCaptchaAdmin $recaptcha_admin ) {
		$this->recaptcha_admin = $recaptcha_admin;
	}

	function register() {
		// create the settings page parts.
		add_action( 'admin_init', array( $this->recaptcha_admin, 'register' ) );
		// form submission handler.
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		// add menus.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_menu', array( $this, 'settings_submenu' ) );
	}

	/**
	 * Add the Correction menu.
	 */
	public function admin_menu() {
		add_menu_page(
			'Corrections', // Page title.
			'Corrections', // Menu title.
			'edit_posts', // Capability.
			'correction', // Menu slug.
			array( $this, 'admin_page' ), // Callback function.
			'dashicons-edit',
			30
		);
	}

	/**
	 * Add settings submenu.
	 */
	function settings_submenu() {
		add_submenu_page(
			'correction', // Parent slug (the main Corrections menu slug).
			'Correction Settings', // Page title.
			'Settings', // Menu title.
			'manage_options', // Capability.
			'options-general.php?page=correction-settings', // Menu slug.
			array( $this->recaptcha_admin, 'settings_page' ) // Callback function.
		);
	}

	/**
	 * Admin page content.
	 */
	function admin_page() {
		?>
		<div class="wrap">
			<h2>Corrections</h2>
			<table class="wp-list-table widefat fixed striped posts">
				<thead>
					<tr>
						<th>Post ID</th>
						<th>Original Sentence</th>
						<th>Correction</th>
						<th>Email</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$corrections = get_posts(
						array(
							'post_type'   => 'correction',
							'post_status' => 'any',
							'numberposts' => 10,
						)
					);

					foreach ( $corrections as $correction ) {
						$original_post_id   = get_post_meta( $correction->ID, 'original_post_id', true );
						$original_sentence  = get_post_meta( $correction->ID, 'original_sentence', true );
						$corrected_sentence = get_post_meta( $correction->ID, 'correction', true );
						$email              = get_post_meta( $correction->ID, 'email', true );
						$status             = $correction->post_status;

						// Check if the original post exists.
						$original_post = get_post( $original_post_id );
						if ( $original_post ) {
							$original_post_link = '<a href="' . esc_url( get_edit_post_link( $original_post_id ) ) . '">' . esc_html( $original_post->post_title ) . '</a>';
						} else {
							$original_post_link = 'Post ID: ' . esc_html( $original_post_id ) . ' (Not Found)';
						}
						?>
						<tr>
							<td><?php echo $original_post_link; ?></td>					 
							<td><?php echo esc_html( $original_sentence ); ?></td>
							<td><?php echo esc_html( $corrected_sentence ); ?></td>
							<td><?php echo esc_html( $email ); ?></td>
							<td>
								<a href="
								<?php
								echo esc_url(
									add_query_arg(
										array(
											'action' => 'delete_correction',
											'correction_id' => $correction->ID,
										)
									)
								);
								?>
											">Delete</a>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Handle delete actions.
	 */
	function handle_actions() {
		if ( isset( $_GET['action'] ) && 'delete_correction ' === $_GET['action'] && isset( $_GET['correction_id'] ) ) {
			$correction_id = intval( $_GET['correction_id'] );

			if ( $correction_id ) {
				wp_delete_post( $correction_id, true ); // true = force delete.

				// Redirect back to the corrections admin page.
				wp_safe_redirect( admin_url( 'admin.php?page=correction' ) ); // Ensure to use admin.php?page=correction for menu slug.
				exit();
			}
		}
	}
}
