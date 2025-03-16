<?php
/**
 * Plugin Name: Correction Feedback Plugin
 * Description: A very basic plugin example.
 * Version: 1.0
 * Author: Your Name
 *
 * @package johnk/plugin
 */

require_once 'ReCaptcha.php';
require_once 'ReCaptchaAdmin.php';

/**
 * PLUGIN ACTIVATION/DEACTIVATION
 */

/**
 * Plugin Activation Function.
 */
function correction_plugin_activate() {

	$page_title   = 'Submit a Correction';
	$page_content = '[correction_form]';

	$correction_pages = get_posts(
		array(
			'post_type' => 'page',
			'title'     => $page_title,
		)
	);

	if ( count( $correction_pages ) < 1 ) {
		$page_id = wp_insert_post(
			array(
				'post_title'   => $page_title,
				'post_content' => $page_content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);
		if ( $page_id ) {
			update_option( 'correction_form_page_id', $page_id );
		}
	} else {
        $page = $correction_pages[0];
		update_option( 'correction_form_page_id', $page->ID );
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'correction_plugin_activate' );

/**
 * Plugin Deactivation Function
 */
function correction_plugin_deactivate() {
	unregister_post_type( 'correction' );
	delete_option( 'correction_form_page_id' );
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'correction_plugin_deactivate' );

/**
 * CUSTOM POST TYPES
 */

/**
 * Correction post type.
 */
function register_correction_post_type() {
	register_post_type(
		'correction', // $post_type
		array(
			'labels'          => array(
				'name'               => __( 'Corrections', 'textdomain' ),
				'singular_name'      => __( 'Correction', 'textdomain' ),
				'menu_name'          => __( 'Corrections', 'textdomain' ),
				'all_items'          => __( 'All Corrections', 'textdomain' ),
				'add_new'            => __( 'Add New', 'textdomain' ),
				'add_new_item'       => __( 'Add New Correction', 'textdomain' ),
				'edit_item'          => __( 'Edit Correction', 'textdomain' ),
				'new_item'           => __( 'New Correction', 'textdomain' ),
				'view_item'          => __( 'View Correction', 'textdomain' ),
				'search_items'       => __( 'Search Corrections', 'textdomain' ),
				'not_found'          => __( 'No corrections found', 'textdomain' ),
				'not_found_in_trash' => __( 'No corrections found in Trash', 'textdomain' ),
			),
			'public'          => true, // Show in admin menu.
			'show_ui'         => true, // Show admin UI.
			'show_in_menu'    => true, // show in the admin menu.
			'capability_type' => 'correction',
			'hierarchical'    => false,
			'rewrite'         => array( 'slug' => 'correction' ),
			'query_var'       => true,
			'supports'        => array( 'title', 'editor' ),
			'menu_position'   => 30,
			'has_archive'     => false, // No archive page.
		)
	);
}

/**
 * SHORTCODES
 */

/**
 * Shortcode to display the link.
 */
function correction_link_shortcode() {
	global $post;
	if ( ! $post ) {
		return '';
	}
	$form_url  = add_query_arg(
		array(
			'correction_form' => 'true',
			'post_id'         => $post->ID,
		),
		get_permalink( get_option( 'correction_form_page_id' ) )
	);
	$icon      = '[]';
	$link_text = 'Submit a Correction ';
	$link      = '<a href="' . esc_url( $form_url ) . '" target="_blank">' . esc_html( $link_text ) . $icon . '</a>';
	return $link;
}

/**
 * Shortcode to display and handle the correction form.
 */
function correction_form_shortcode() {
	ob_start();

	$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;

	if ( $post_id <= 0 ) {
		echo '<p>Invalid post ID.</p>';
		return ob_get_clean();
	}

	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
        $recaptcha = new ReCaptcha();
		$nonce              = wp_create_nonce( 'correction_form_nonce' );

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

		if ( $recaptcha->is_enabled() ) {
            $recaptcha->render_script();
		}

		return ob_get_clean();
	} else {
		return correction_form_handler();
	}
}

/**
 * Handle the correction form submission.
 */
function correction_form_handler() {
	if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
		ob_start();

		if ( ! isset( $_POST['correction_form_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['correction_form_nonce'] ) ), 'correction_form_nonce' ) ) {
			echo '<p>Security check failed.</p>';
			return ob_get_clean();
		}

		$original_sentence = isset( $_POST['original_sentence'] ) ? sanitize_textarea_field( wp_unslash( $_POST['original_sentence'] ) ) : '';
		$correction        = isset( $_POST['correction-text'] ) ? sanitize_textarea_field( wp_unslash( $_POST['correction-text'] ) ) : '';
		$email             = isset( $_POST['correction-email'] ) ? sanitize_email( wp_unslash( $_POST['correction-email'] ) ) : '';
		$post_id           = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
		$recaptcha_token   = isset( $_POST['recaptcha_token'] ) ? sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) ) : '';

		if ( empty( $original_sentence ) || empty( $correction ) || empty( $email ) ) {
			echo '<p>Please fill in all required fields.</p>';
			return ob_get_clean();
		}

        $recaptcha = new ReCaptcha();
        if ($recaptcha->is_enabled() && !$recaptcha->verify($recaptcha_token)) {
            echo '<p>reCAPTCHA verification failed. You might be a bot.</p>';
            return ob_get_clean();
        }

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

/**
 * ADMIN
 */

/**
 * Add the Correction menu.
 */
function correction_admin_menu() {
	add_menu_page(
		'Corrections', // Page title.
		'Corrections', // Menu title.
		'edit_posts', // Capability.
		'correction', // Menu slug.
		'correction_admin_page', // Callback function.
		'dashicons-edit',
		30
	);
}

/**
 * Add settings submenu.
 */
function correction_add_settings_submenu() {
	add_submenu_page(
		'correction', // Parent slug (the main Corrections menu slug).
		'Correction Settings', // Page title.
		'Settings', // Menu title.
		'manage_options', // Capability.
		'options-general.php?page=correction-settings', // Menu slug.
		'correction_settings_page' // Callback function.
	);
}

/**
 * Admin page content.
 */
function correction_admin_page() {
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
										'action'        => 'delete_correction',
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
function handle_correction_actions() {
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

/**
 * Settings page content.
 */
function correction_settings_page() {
  ?>
  <div class="wrap">
    <h2>Correction Settings</h2>
    <form method="post" action="options.php">
      <?php
      settings_fields('correction_settings_group');
      do_settings_sections('correction-settings');
      submit_button();
      ?>
    </form>
  </div>
  <?php
}

/**
 * INIT SECTION
 */

/**
 * Admin notice error shown when permalinks are off.
 */
function correction_permalink_notice() {
	?>
	<div class="notice notice-error">
		<p><?php esc_html_e( 'The Correction Plugin requires using permalinks. Please enable permalinks in your WordPress settings.', 'textdomain' ); ?></p>
	</div>
	<?php
}

/**
 * I like to keep the callbacks together.
 */
function correction_init() {
	global $wp_rewrite;
	if ( ! $wp_rewrite->using_permalinks() ) {
		add_action( 'admin_notices', 'correction_permalink_notice' );
	} else {
        $recaptcha = new ReCaptcha();
        $recaptcha_admin = new ReCaptchaAdmin($recaptcha);
        add_action('admin_init', array($recaptcha_admin, 'register_settings'));

		add_shortcode( 'correction_link', 'correction_link_shortcode' );
		add_shortcode( 'correction_form', 'correction_form_shortcode' );
		add_action( 'init', 'register_correction_post_type' );
		add_action( 'admin_init', 'handle_correction_actions' );
		add_action( 'admin_menu', 'correction_admin_menu' );
		add_action( 'admin_menu', 'correction_add_settings_submenu' );
	}
}
add_action( 'init', 'correction_init', 5 );

