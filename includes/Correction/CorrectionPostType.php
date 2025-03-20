<?php

namespace JTK\Correction;

/**
 * Correction post type.
 * We store the corrections as posts.
 */
class CorrectionPostType {

	public static function register_post_type() {
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
}
