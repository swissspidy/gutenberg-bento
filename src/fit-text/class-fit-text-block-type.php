<?php
/**
 * Bent Fit Text component integration.
 *
 * @package   Gutenberg_Bento
 */

namespace Gutenberg_Bento;

/**
 * Class Fit_Text_Block_type
 */
class Fit_Text_Block_Type {

	const BENTO_RUNTIME_SCRIPT_HANDLE  = 'bento-runtime';
	const BENTO_FIT_TEXT_SCRIPT_HANDLE = 'bento-fit-text';
	const BENTO_FIT_TEXT_VERSION       = '1.0';

	// @todo Maybe these should be obtained from block.json directly?
	const BLOCK_STYLE_HANDLE       = 'gutenberg-bento-fit-text-style';
	const BLOCK_VIEW_SCRIPT_HANDLE = 'gutenberg-bento-fit-text-view-script';

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
		add_action( 'init', array( $this, 'register_fit_text_block_type' ) );
		add_action( 'enqueue_block_assets', array( $this, 'unregister_asset_dependencies_on_amp' ), 9 ); // The 9 to before wp_enqueue_registered_block_scripts_and_styles().
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_kses_allowed_html' ) );
	}

	/**
	 * Registers the scripts and styles for Bento components.
	 *
	 * Note that 'amp-runtime' and 'bento-fit-text' are scripts registered by the AMP plugin. The Bento versions aren't
	 * currently registered, so that is why this function needs to run currently on AMP pages.
	 *
	 * @return void
	 */
	public function register_bento_assets() {

		$src    = sprintf( 'https://cdn.ampproject.org/v0/bento-fit-text-%s.js', self::BENTO_FIT_TEXT_VERSION );
		$script = wp_scripts()->query( self::BENTO_FIT_TEXT_SCRIPT_HANDLE );
		if ( $script ) {
			// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
			$script->src = $src;
		} else {
			wp_register_script( self::BENTO_FIT_TEXT_SCRIPT_HANDLE, $src, array( self::BENTO_RUNTIME_SCRIPT_HANDLE ), null, true );
		}

		// At the moment the AMP plugin does not register styles for Bento components, but this could change with <https://github.com/ampproject/amp-wp/pull/6353>.
		$src   = sprintf( 'https://cdn.ampproject.org/v0/bento-fit-text-%s.css', self::BENTO_FIT_TEXT_VERSION );
		$style = wp_styles()->query( self::BENTO_FIT_TEXT_SCRIPT_HANDLE );
		if ( $style ) {
			// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
			$style->src = $src;
		} else {
			wp_register_style( self::BENTO_FIT_TEXT_SCRIPT_HANDLE, $src, array(), null, false );
		}

		// TODO: We need to test this with self hosted files.
		/**
		 * Filters whether to enqueue self-hosted Bento components instead of using the CDN.
		 *
		 * @param bool $self_host Whether to self-host. Default false.
		 */
		$self_host = (bool) apply_filters( 'gutenberg_bento_self_host', false );

		if ( $self_host ) {
			$web_component_asset_file = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/bento-fit-text.asset.php';
			$web_component_asset      = is_readable( $web_component_asset_file ) ? require $web_component_asset_file : array();
			$web_component_version    = isset( $web_component_asset['version'] ) ? $web_component_asset['version'] : false;

			$style = wp_styles()->query( self::BENTO_FIT_TEXT_SCRIPT_HANDLE );
			if ( $style ) {
				$style->src = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/bento-fit-text.css';
				$style->ver = $web_component_version;
			}
			$script = wp_scripts()->query( self::BENTO_FIT_TEXT_SCRIPT_HANDLE );
			if ( $script ) {
				$script->src  = GUTENBERG_BENTO_BLOCKS_ABSPATH . 'build/fit-text/bento-fit-text.js';
				$script->ver  = $web_component_version;
				$script->deps = array(); // bento.js runtime is not needed when self-hosting.
			}
		}
	}

	/**
	 * Registers the fit-text block type.
	 *
	 * @return void
	 */
	public function register_fit_text_block_type() {
		register_block_type(
			GUTENBERG_BENTO_BLOCKS_ABSPATH . '/build/fit-text/block.json'
		);

		$script = wp_scripts()->query( self::BLOCK_VIEW_SCRIPT_HANDLE );
		if ( $script ) {
			$script->deps = array_merge( $script->deps, array( self::BENTO_RUNTIME_SCRIPT_HANDLE, self::BENTO_FIT_TEXT_SCRIPT_HANDLE ) );
			$script->args = 1;
		}

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
			$style->deps = array_diff( $style->deps, array( self::BENTO_FIT_TEXT_SCRIPT_HANDLE ) );
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
		$tags['bento-fit-text'] = array_merge(
			isset( $tags['bento-fit-text'] ) ? $tags['bento-fit-text'] : array(),
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
}
