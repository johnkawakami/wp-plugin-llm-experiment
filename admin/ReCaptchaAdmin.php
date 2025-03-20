<?php

namespace JTK\Admin;

use JTK\ReCaptcha;
use JTK\Admin\AbstractAdmin;

class ReCaptchaAdmin extends AbstractAdmin {
	private $recaptcha;

	public function __construct( ReCaptcha $recaptcha ) {
		$this->recaptcha = $recaptcha;
	}

	public function register() {
		register_setting( 'correction_settings_group', 'correction_recaptcha_enabled' );
		register_setting( 'correction_settings_group', 'correction_recaptcha_site_key' );
		register_setting( 'correction_settings_group', 'correction_recaptcha_secret_key' );

		add_settings_section(
			'correction_recaptcha_settings',
			'reCAPTCHA Settings',
			array( $this, 'recaptcha_settings_callback' ),
			'correction-settings'
		);

		add_settings_field(
			'correction_recaptcha_enabled',
			'Enable reCAPTCHA',
			array( $this, 'recaptcha_enabled_callback' ),
			'correction-settings',
			'correction_recaptcha_settings'
		);

		add_settings_field(
			'correction_recaptcha_site_key',
			'reCAPTCHA Site Key',
			array( $this, 'recaptcha_site_key_callback' ),
			'correction-settings',
			'correction_recaptcha_settings'
		);

		add_settings_field(
			'correction_recaptcha_secret_key',
			'reCAPTCHA Secret Key',
			array( $this, 'recaptcha_secret_key_callback' ),
			'correction-settings',
			'correction_recaptcha_settings'
		);
	}

	public function recaptcha_settings_callback() {
		echo 'Enter your reCAPTCHA v3 site key and secret key.';
	}

	public function recaptcha_enabled_callback() {
		$enabled = get_option( 'correction_recaptcha_enabled' );
		echo "<input type='checkbox' name='correction_recaptcha_enabled' value='1' " . checked( $enabled, 1, false ) . ' />';
	}

	public function recaptcha_site_key_callback() {
		$site_key = $this->recaptcha->get_site_key();
		echo "<input type='text' name='correction_recaptcha_site_key' value='" . esc_attr( $site_key ) . "' size='50' />";
	}

	public function recaptcha_secret_key_callback() {
		$secret_key = $this->recaptcha->get_secret_key();
		echo "<input type='text' name='correction_recaptcha_secret_key' value='" . esc_attr( $secret_key ) . "' size='50' />";
	}
	/**
	 * Settings page content.
	 */
	function settings_page() {
		?>
		<div class="wrap">
		<h2>Correction Settings</h2>
		<form method="post" action="options.php">
			<?php
				settings_fields( 'correction_settings_group' );
				do_settings_sections( 'correction-settings' );
				submit_button();
			?>
		</form>
		</div>
		<?php
	}
}

