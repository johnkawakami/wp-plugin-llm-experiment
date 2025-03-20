<?php

namespace JTK\Correction;

class CorrectionPlugin {

	/**
	 * Plugin Activation Function.
	 */
	public static function activate() {

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

	/**
	 * Plugin Deactivation Function
	 */
	public static function deactivate() {
		unregister_post_type( 'correction' );
		delete_option( 'correction_form_page_id' );
		flush_rewrite_rules();
	}
}
