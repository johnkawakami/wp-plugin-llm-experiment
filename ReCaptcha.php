<?php

class ReCaptcha {
  private $site_key;
  private $secret_key;

  public function __construct() {
    $this->site_key = get_option('correction_recaptcha_site_key');
    $this->secret_key = get_option('correction_recaptcha_secret_key');
  }

  public function is_enabled() {
    return get_option('correction_recaptcha_enabled') === '1';
  }

  public function get_site_key() {
    return $this->site_key;
  }

  public function get_secret_key() {
    return $this->secret_key;
  }

  public function verify($token) {
    if (empty($this->secret_key)) {
      return false;
    }

    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_response = wp_remote_post(
      $recaptcha_url,
      array(
        'body' => array(
          'secret' => $this->secret_key,
          'response' => $token,
        ),
      )
    );

    if (is_wp_error($recaptcha_response)) {
      return false;
    }

    $recaptcha_body = json_decode(wp_remote_retrieve_body($recaptcha_response));

    return $recaptcha_body->success && $recaptcha_body->score >= 0.5;
  }

  public function render_script() {
    if (!empty($this->site_key)) {
      echo '<script src="https://www.google.com/recaptcha/api.js?render=' . esc_attr($this->site_key) . '"></script>';
      echo '<script>';
      echo 'grecaptcha.ready(function() {';
      echo   'grecaptcha.execute("' . esc_attr($this->site_key) . '", {action: "correction_form"}).then(function(token) {';
      echo     'document.getElementById("recaptcha_token").value = token;';
      echo   '});';
      echo '});';
      echo '</script>';
    }
  }
}
