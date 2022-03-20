<?php
/**
 * Main plugin functionality.
 *
 * @package   Google\Gutenberg_Bento
 */

namespace Google\Gutenberg_Bento;

/**
 * Class Date_Countdown_Block_Type
 */
class Date_Countdown_Block_Type {

	const BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE = 'bento-date-countdown';
	const BENTO_DATE_COUNTDOWN_VERSION       = '1.0';

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
		add_action( 'init', array( $this, 'register_block_type' ) );
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
			self::BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE,
			sprintf( 'https://cdn.ampproject.org/v0/bento-date-countdown-%s.js', self::BENTO_DATE_COUNTDOWN_VERSION ),
			array( Blocks::BENTO_RUNTIME_SCRIPT_HANDLE ),
			null,
			true
		);

		wp_register_style(
			self::BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE,
			sprintf( 'https://cdn.ampproject.org/v0/bento-date-countdown-%s.css', self::BENTO_DATE_COUNTDOWN_VERSION ),
			array(),
			null,
			false
		);

		/**
		 * Filters whether to enqueue self-hosted Bento components instead of using the CDN.
		 *
		 * @param bool $self_host Whether to self-host. Default false.
		 */
		$self_host = (bool) apply_filters( 'gutenberg_bento_self_host', false );

		if ( $self_host ) {
			$web_component_asset_file = plugin_dir_path( __DIR__ ) . 'build/bento-date-countdown.asset.php';
			$web_component_asset      = is_readable( $web_component_asset_file ) ? require $web_component_asset_file : array();
			$web_component_version    = isset( $web_component_asset['version'] ) ? $web_component_asset['version'] : false;

			$script = wp_scripts()->query( self::BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE );
			if ( $script ) {
				$script->src  = plugin_dir_url( __DIR__ ) . 'build/bento-date-countdown.js';
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
	public function register_block_type() {
		register_block_type( GUTENBERG_BENTO_BLOCKS_ABSPATH . '/build/date-countdown/block.json' );
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
		$tags['bento-date-countdown'] = array_merge(
			isset( $tags['bento-date-countdown'] ) ? $tags['bento-date-countdown'] : array(),
			array_fill_keys(
				array(
					'end-date',
					'biggest-unit',
					'locale',
					'when-ended',
					'count-up',
				),
				true
			)
		);
		$tags['template']             = array();
		return $tags;
	}
}
