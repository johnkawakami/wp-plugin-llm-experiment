<?php

namespace JTK\Correction;

/**
 * This is not done right. This should either be a static class or a Singleton.
 * The way this is writte, it can end up definining the correction_link shortcode
 * multiple times.
 */
class CorrectionLinkShortcode {
	public function __construct() {
		add_shortcode( 'correction_link', array( $this, 'render' ) );
	}

	public function render( $atts ) {
		global $post;
		if ( ! $post ) {
			return '';
		}

		$form_url = add_query_arg(
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
}
