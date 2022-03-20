<?php
/**
 * Main class
 *
 * @package wp-bootstrap-blocks
 */

namespace Google\Gutenberg_Bento;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Google\Gutenberg_Bento\Block_Type', false ) ) :

	/**
	 * Class Block_Type
	 */
	abstract class Block_Type {
		/**
		 * Name of block type including namespace.
		 *
		 * @var string
		 */
		protected $name = '';

		/**
		 * Block attributes.
		 *
		 * @var array
		 */
		protected $block_data = '';

		/**
		 * Block_Type constructor.
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'register_block_type' ) );
		}

		/**
		 * Registers block type
		 */
		public function register_block_type() {
			register_block_type(
				$this->block_data,
				array(
					'render_callback' => array( $this, 'render_callback' ),
				)
			);
		}

	}

endif;
