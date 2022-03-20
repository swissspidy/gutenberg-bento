<?php
/**
 * Bento Accordion component integration.
 *
 * @package Gutenberg_Bento
 */

namespace Gutenberg_Bento;

/**
 * Class Accordion_Block_Type
 */
class Accordion_Block_Type {

	const BENTO_RUNTIME_SCRIPT_HANDLE = 'bento-runtime';
	const BLOCK_VIEW_STYLE_HANDLE     = 'gutenberg-bento-accordion-view-style';

	/**
	 * Calls the initialization function.
	 *
	 * @return void
	 */
	public function register() {
		$this->init_hooks();
	}

	/**
	 * Init hooks
	 */
	protected function init_hooks() {
		add_action( 'init', array( $this, 'register_block_type' ) );
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_kses_allowed_html' ) );
	}

	/**
	 * Registers the block type.
	 *
	 * @return void
	 */
	public function register_block_type() {
		$settings = array();
		if ( ! is_admin() && ! $this->is_amp() ) {
			wp_register_style( self::BLOCK_VIEW_STYLE_HANDLE, 'https://cdn.ampproject.org/v0/bento-accordion-1.0.css', array(), null, false );
			$settings['style'] = self::BLOCK_VIEW_STYLE_HANDLE;
		}

		$block = register_block_type(
			GUTENBERG_BENTO_BLOCKS_ABSPATH . '/build/accordion/block.json',
			$settings
		);

		$script = wp_scripts()->query( $block->view_script );
		if ( $script ) {
			$script->deps = array_merge( $script->deps, array( self::BENTO_RUNTIME_SCRIPT_HANDLE ) );
			$script->args = 1;
		}

		register_block_type(
			GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/accordion-section'
		);
	}

	/**
	 * Filter Kses-allowed HTML.
	 *
	 * Prevent custom element from being removed if user cannot do unfiltered_html.
	 *
	 * @param array $tags Tags.
	 * @return array Filtered tags.
	 */
	public function filter_kses_allowed_html( $tags ) {
		$tags['bento-accordion'] = array_merge(
			isset( $tags['bento-accordion'] ) ? $tags['bento-accordion'] : array(),
			array_fill_keys(
				array(
					'animate',
					'class',
					'id',
				),
				true
			)
		);
		return $tags;
	}

	/**
	 * Determines whether the current request is for an AMP document.
	 *
	 * @return bool Whether the current request is for an AMP document or not.
	 */
	public function is_amp() {
		return ( function_exists( 'amp_is_request' ) && amp_is_request() );
	}
}
