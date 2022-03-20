<?php
/**
 * Main plugin functionality.
 *
 * @package   Gutenberg_Bento
 */

namespace Gutenberg_Bento;

/**
 * Class Carousel_Block_type
 */
class Carousel_Block_Type {
	const BENTO_RUNTIME_SCRIPT_HANDLE       = 'bento-runtime';
	const BENTO_BASE_CAROUSEL_SCRIPT_HANDLE = 'bento-base-carousel';
	const BENTO_BASE_CAROUSEL_VERSION       = '1.0';

	// @todo Maybe these should be obtained from block.json directly?
	const BLOCK_VIEW_SCRIPT_HANDLE = 'gutenberg-bento-carousel-view-script';

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
		add_action( 'init', array( $this, 'register_bento_assets' ) );
		add_action( 'init', array( $this, 'register_carousel_block_type' ) );
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_kses_allowed_html' ) );
	}

	/**
	 * Registers the scripts and styles for Bento components.
	 *
	 * Note that 'amp-runtime' and 'bento-base-carousel' are scripts registered by the AMP plugin. The Bento versions aren't
	 * currently registered, so that is why this function needs to run currently on AMP pages.
	 *
	 * @return void
	 */
	public function register_bento_assets() {
		wp_register_script(
			self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE,
			sprintf(
				'https://cdn.ampproject.org/v0/bento-base-carousel-%s.js',
				self::BENTO_BASE_CAROUSEL_VERSION
			),
			array( self::BENTO_RUNTIME_SCRIPT_HANDLE ),
			null,
			true
		);

		// At the moment the AMP plugin does not register styles for Bento components, but this could change with <https://github.com/ampproject/amp-wp/pull/6353>.
		wp_register_style(
			self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE,
			sprintf( 'https://cdn.ampproject.org/v0/bento-base-carousel-%s.css', self::BENTO_BASE_CAROUSEL_VERSION ),
			array(),
			null,
			false
		);

		// TODO: We need to test this with self hosted files.
		/**
		 * Filters whether to enqueue self-hosted Bento components instead of using the CDN.
		 *
		 * @param bool $self_host Whether to self-host. Default false.
		 */
		$self_host = (bool) apply_filters( 'gutenberg_bento_self_host', false );

		if ( $self_host ) {
			$web_component_asset_file = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/bento-base-carousel.asset.php';
			$web_component_asset      = is_readable( $web_component_asset_file ) ? require $web_component_asset_file : array();
			$web_component_version    = isset( $web_component_asset['version'] ) ? $web_component_asset['version'] : false;

			$style = wp_styles()->query( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
			if ( $style ) {
				$style->src = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/bento-base-carousel.css';
				$style->ver = $web_component_version;
			}
			$script = wp_scripts()->query( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
			if ( $script ) {
				$script->src  = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/carousel/bento-base-carousel.js';
				$script->ver  = $web_component_version;
				$script->deps = array(); // bento.js runtime is not needed when self-hosting.
			}
		}
	}

	/**
	 * Registers the carousel block type.
	 *
	 * @return void
	 */
	public function register_carousel_block_type() {
		register_block_type(
			GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/carousel/block.json',
			array(
				'render_callback' => array( $this, 'render_carousel_block' ),
			)
		);

		$script = wp_scripts()->query( self::BLOCK_VIEW_SCRIPT_HANDLE );
		if ( $script ) {
			$script->deps = array_merge( $script->deps, array( self::BENTO_RUNTIME_SCRIPT_HANDLE, self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE ) );
			$script->args = 1;
		}

	}

	/**
	 * Render callback for the carousel block.
	 *
	 * Adds the `dir` HTML attribute if in RTL.
	 *
	 * @param array  $attributes Block attributes.
	 * @param string $content Block content.
	 *
	 * @return string Block content.
	 */
	public function render_carousel_block( $attributes, $content ) {
		// TODO: Check if we still need this.
		// Note this is a temporary measure until Gutenberg 11 is live. See <https://github.com/WordPress/gutenberg/pull/32977>.
		if ( ! is_admin() ) {
			wp_enqueue_script( self::BLOCK_VIEW_SCRIPT_HANDLE );
		}

		if ( is_rtl() ) {
			$content = str_replace( '<bento-base-carousel', '<bento-base-carousel dir="rtl"', $content );
		}

		return $content;
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
		$tags['bento-base-carousel'] = array_merge(
			isset( $tags['bento-base-carousel'] ) ? $tags['bento-base-carousel'] : array(),
			array_fill_keys(
				array(
					'class',
					'auto-advance',
					'loop',
					'snap',
				),
				true
			)
		);
		return $tags;
	}
}
