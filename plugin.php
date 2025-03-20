<?php
/**
 * Plugin Name: Correction Feedback
 * Description: Front end form for users to submit a correction to the site.
 * Version: 1.0
 * Author: Your Name
 *
 * @package johnk/plugin
 */

require_once __DIR__ . '/vendor/autoload.php';

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

function correction_init() {
	global $wp_rewrite;
	if ( ! $wp_rewrite->using_permalinks() ) {
		add_action( 'admin_notices', 'correction_permalink_notice' );
		exit();
	}

	add_action( 'init', 'JTK\Correction\CorrectionPostType::register_post_type' );

	$recaptcha                 = new JTK\ReCaptcha();
	$correction_link_shortcode = new JTK\Correction\CorrectionLinkShortcode();
	$correction_form_shortcode = new JTK\Correction\CorrectionFormShortcode( $recaptcha );

	add_shortcode( 'correction_link', array( $correction_link_shortcode, 'render' ) );
	add_shortcode( 'correction_form', array( $correction_form_shortcode, 'render' ) );

	if ( is_admin() ) {
		$recaptcha_admin  = new JTK\Admin\ReCaptchaAdmin( $recaptcha );
		$correction_admin = new JTK\Admin\Correction\CorrectionAdmin( $recaptcha_admin );
		// $correction_admin->register();
		add_action( 'init', array( $correction_admin, 'register' ) );
	}
}
register_activation_hook( __FILE__, 'JTK\Correction\CorrectionPlugin::activate' );
register_deactivation_hook( __FILE__, 'JTK\Correction\CorrectionPlugin::deactivate' );
add_action( 'init', 'correction_init', 5 );


