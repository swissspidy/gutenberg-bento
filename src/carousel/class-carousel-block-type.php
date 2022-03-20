<?php
/**
 * Main plugin functionality.
 *
 * @package   Google\Gutenberg_Bento
 */

namespace Google\Gutenberg_Bento;

/**
 * Class Carousel_Block_type
 */
class Carousel_Block_Type {

	const BENTO_RUNTIME_SCRIPT_HANDLE       = 'bento-runtime';
	const BENTO_BASE_CAROUSEL_SCRIPT_HANDLE = 'bento-base-carousel';
	const BENTO_BASE_CAROUSEL_VERSION       = '1.0';

	// @todo Maybe these should be obtained from block.json directly?
	const BLOCK_STYLE_HANDLE       = 'gutenberg-bento-carousel';
	const BLOCK_VIEW_SCRIPT_HANDLE = 'gutenberg-bento-carousel-view';

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
		add_action( 'init', array( $this, 'register_carousel_block_assets' ) );
		add_action( 'init', array( $this, 'register_carousel_block_type' ) );
		add_action( 'enqueue_block_assets', array( $this, 'unregister_asset_dependencies_on_amp' ), 9 ); // The 9 to before wp_enqueue_registered_block_scripts_and_styles().
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_kses_allowed_html' ) );
		add_filter( 'amp_content_sanitizers', array( $this, 'add_amp_content_sanitizer' ) );
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

		$src    = sprintf( 'https://cdn.ampproject.org/v0/bento-base-carousel-%s.js', self::BENTO_BASE_CAROUSEL_VERSION );
		$script = wp_scripts()->query( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
		if ( $script ) {
			// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
			$script->src = $src;
		} else {
			wp_register_script( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE, $src, array( self::BENTO_RUNTIME_SCRIPT_HANDLE ), null, true );
		}

		// At the moment the AMP plugin does not register styles for Bento components, but this could change with <https://github.com/ampproject/amp-wp/pull/6353>.
		$src   = sprintf( 'https://cdn.ampproject.org/v0/bento-base-carousel-%s.css', self::BENTO_BASE_CAROUSEL_VERSION );
		$style = wp_styles()->query( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
		if ( $style ) {
			// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
			$style->src = $src;
		} else {
			wp_register_style( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE, $src, array(), null, false );
		}

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
				$script->src  = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/bento-base-carousel.js';
				$script->ver  = $web_component_version;
				$script->deps = array(); // bento.js runtime is not needed when self-hosting.
			}
		}
	}

	/**
	 * Registers the scripts and styles for the carousel block.
	 *
	 * @return void
	 */
	function register_carousel_block_assets() {
		$edit_asset_file   = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/carousel.asset.php';
		$edit_asset        = is_readable( $edit_asset_file ) ? require $edit_asset_file : array();
		$edit_version      = isset( $edit_asset['version'] ) ? $edit_asset['version'] : false;
		$edit_dependencies = isset( $edit_asset['dependencies'] ) ? $edit_asset['dependencies'] : array();

		$view_asset_file     = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/carousel.view.asset.php';
		$view_asset          = is_readable( $view_asset_file ) ? require $view_asset_file : array();
		$view_version        = isset( $view_asset['version'] ) ? $view_asset['version'] : false;
		$view_dependencies   = isset( $view_asset['dependencies'] ) ? $view_asset['dependencies'] : array();
		$view_dependencies[] = self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE;

		// Both used only in editor.
		wp_register_style( 'gutenberg-bento-carousel-edit', GUTENBERG_BENTO_BLOCKS_ASSETSURL . '/carousel.css', array(), $edit_version );
		wp_register_script( 'gutenberg-bento-carousel-edit', GUTENBERG_BENTO_BLOCKS_ASSETSURL . '/carousel.js', $edit_dependencies, $edit_version );

		// Used in editor + frontend.
		wp_register_style( self::BLOCK_STYLE_HANDLE, GUTENBERG_BENTO_BLOCKS_ASSETSURL . '/carousel.view.css', array( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE ), $view_version );

		// Used only on frontend.
		wp_register_script( self::BLOCK_VIEW_SCRIPT_HANDLE, GUTENBERG_BENTO_BLOCKS_ASSETSURL . '/carousel.view.js', $view_dependencies, $view_version, true );
	}

	/**
	 * Registers the carousel block type.
	 *
	 * @return void
	 */
	public function register_carousel_block_type() {
		register_block_type(
			__DIR__ . '/block.json',
			array(
				'render_callback' => array( $this, 'render_carousel_block' ),
			)
		);
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
		// Note this is a temporary measure until Gutenberg 11 is live. See <https://github.com/WordPress/gutenberg/pull/32977>.
		if ( ! is_admin() && ! $this->is_amp() ) {
			wp_enqueue_script( self::BLOCK_VIEW_SCRIPT_HANDLE );
		}

		if ( is_rtl() ) {
			$content = str_replace( '<bento-base-carousel', '<bento-base-carousel dir="rtl"', $content );
		}

		return $content;
	}

	/**
	 * When on AMP pages, prevent the view script and other scripts/styles from being printed. These are handled by AMP.
	 *
	 * @return void
	 */
	public function unregister_asset_dependencies_on_amp() {
		// Note the is_amp() function can only be called at or after the `wp` action.
		if ( ! $this->is_amp() ) {
			return;
		}

		$style = wp_styles()->query( self::BLOCK_STYLE_HANDLE );
		if ( $style ) {
			$style->deps = array_diff( $style->deps, array( self::BENTO_BASE_CAROUSEL_SCRIPT_HANDLE ) );
		}
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

	/**
	 * Determines whether the current request is for an AMP document.
	 *
	 * @return bool Whether the current request is for an AMP document or not.
	 */
	public function is_amp() {
		return ( function_exists( 'amp_is_request' ) && amp_is_request() );
	}

	/**
	 * Adds a new AMP sanitizer for the <bento-base-carousel> to ensure validity.
	 *
	 * @param array $sanitizers List of sanitizers.
	 *
	 * @return array Filtered list of sanitizers.
	 */
	public function add_amp_content_sanitizer( $sanitizers ) {
		if ( class_exists( \AMP_Base_Sanitizer::class ) ) {
			require_once __DIR__ . '/class-amp-carousel-sanitizer.php';

			$sanitizers[ AMP_Carousel_Sanitizer::class ] = array();
		}

		return $sanitizers;
	}
}
