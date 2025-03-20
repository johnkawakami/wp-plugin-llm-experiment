<?php

namespace JTK\Correction;

class CorrectionBlock {

    /**
     * Register callbacks. Must be run as an 'init' action.
     */
    public function register() {
        // Register the block's script
        wp_register_script(
            'correction-block-editor',
            plugins_url('build/index.js', CORRECTION_PLUGIN_FILE),
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components')
        );

        // Register the block's editor styles (optional)
        wp_register_style(
            'correction-block-editor-style',
            plugins_url('build/editor.css', CORRECTION_PLUGIN_FILE),
            array('wp-edit-blocks')
        );

        // Register the block
        register_block_type('correction-block/main', array(
            'editor_script' => 'correction-block-editor',
            'editor_style' => 'correction-block-editor-style',
            'render_callback' => [$this, 'render_callback']
        ));
    }

    public function render_callback( $attributes, $content ) {
        if (isset($attributes['content'])) {
            return do_shortcode('[correction_link]' . $attributes['content'] . '[/correction_link]');
        }
        return '';
    }
}
