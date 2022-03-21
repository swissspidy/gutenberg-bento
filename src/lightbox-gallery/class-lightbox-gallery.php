<?php
/**
 * Main plugin functionality.
 *
 * @package   Gutenberg_Bento
 */

namespace Gutenberg_Bento;

/**
 * Class Lightbox_Gallery
 */
class Lightbox_Gallery {

	const BENTO_SCRIPT_HANDLE = 'bento-lightbox-gallery';
	const BENTO_VERSION       = '1.0';

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
		add_action( 'init', array( $this, 'register_lightbox_gallery_assets' ) );
		add_filter( 'wp_kses_allowed_html', array( $this, 'filter_kses_allowed_html' ) );

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_filter( 'render_block', array( $this, 'filter_gallery_block' ), 10, 2 );
		add_filter( 'block_type_metadata_settings', array( $this, 'filter_block_type_metadata_settings' ), 10, 2 );
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
			self::BENTO_SCRIPT_HANDLE,
			sprintf( 'https://cdn.ampproject.org/v0/bento-lightbox-gallery-%s.js', self::BENTO_VERSION ),
			array( Blocks::BENTO_RUNTIME_SCRIPT_HANDLE ),
			null,
			true
		);

		wp_register_style(
			self::BENTO_SCRIPT_HANDLE,
			sprintf( 'https://cdn.ampproject.org/v0/bento-lightbox-gallery-%s.css', self::BENTO_VERSION ),
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
			$web_component_asset_file = plugin_dir_path( __DIR__ ) . 'build/lightbox-gallery/lightbox-gallery.asset.php';
			$web_component_asset      = is_readable( $web_component_asset_file ) ? require $web_component_asset_file : array();
			$web_component_version    = isset( $web_component_asset['version'] ) ? $web_component_asset['version'] : false;

			$style = wp_styles()->query( self::BENTO_SCRIPT_HANDLE );
			if ( $style ) {
				$style->src = plugin_dir_url( __DIR__ ) . 'build/lightbox-gallery/bento-lightbox-gallery.css';
				$style->ver = $web_component_version;
			}

			$script = wp_scripts()->query( self::BENTO_SCRIPT_HANDLE );
			if ( $script ) {
				$script->src  = plugin_dir_url( __DIR__ ) . 'build/lightbox-gallery/bento-lightbox-gallery.js';
				$script->ver  = $web_component_version;
				$script->deps = array(); // bento.js runtime is not needed when self-hosting.
			}
		}
	}

	/**
	 * Load block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_enqueue_script( 'gutenberg-bento-lightbox-gallery-edit' );
		wp_enqueue_style( 'gutenberg-bento-lightbox-gallery-edit' );
	}

	/**
	 * Filter KSES-allowed HTML.
	 *
	 * Prevent custom element from being removed if user cannot do unfiltered_html.
	 *
	 * @param array $tags Tags.
	 * @return array Filtered tags.
	 */
	public function filter_kses_allowed_html( $tags ) {
		$tags['bento-lightbox-gallery'] = array_merge(
			isset( $tags['bento-lightbox-gallery'] ) ? $tags['bento-lightbox-gallery'] : array(),
			array_fill_keys(
				array(),
				true
			)
		);

		$tags['img'] = array_merge(
			isset( $tags['img'] ) ? $tags['img'] : array(),
			array_fill_keys(
				array(
					'lightbox',
				),
				true
			)
		);
		return $tags;
	}

	/**
	 * Registers the scripts and styles for lightbox gallery.
	 *
	 * @return void
	 */
	public function register_lightbox_gallery_assets() {
		$edit_asset_file   = plugin_dir_path( __DIR__ ) . 'build/lightbox-gallery/lightbox-gallery.asset.php';
		$edit_asset        = is_readable( $edit_asset_file ) ? require $edit_asset_file : array();
		$edit_version      = isset( $edit_asset['version'] ) ? $edit_asset['version'] : false;
		$edit_dependencies = isset( $edit_asset['dependencies'] ) ? $edit_asset['dependencies'] : array();

		// Both used only in editor.
		wp_register_script(
			'gutenberg-bento-lightbox-gallery-edit',
			plugin_dir_url( dirname( __DIR__ ) ) . 'build/lightbox-gallery/lightbox-gallery.js',
			$edit_dependencies,
			$edit_version,
			true
		);
	}

	/**
	 * Filter the core/gallery block to enable bento-lightbox-gallery.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block The full block, including name and attributes.
	 * @return string Filtered block content.
	 */
	public function filter_gallery_block( $block_content, $block ) {
		if ( 'core/gallery' !== $block['blockName'] ) {
			return $block_content;
		}

		if ( 'none' !== $block['attrs']['linkTo'] ) {
			return $block_content;
		}

		if ( empty( $block['attrs']['bentoLightbox'] ) ) {
			return $block_content;
		}

		// TODO: Maybe only need to add <bento-lightbox-gallery> once on the page?
		$block_content .= '<bento-lightbox-gallery></bento-lightbox-gallery>';
		$block_content  = str_replace( '<img', '<img lightbox', $block_content );

		wp_enqueue_script( self::BENTO_SCRIPT_HANDLE );
		wp_enqueue_style( self::BENTO_SCRIPT_HANDLE );

		return $block_content;
	}

	/**
	 * Filter gallery block to add bentoLightbox attribute.
	 *
	 * @param array $settings Array of determined settings for registering a block type.
	 * @param array $metadata Metadata loaded from the block.json file.
	 * @return array Filtered settings.
	 */
	public function filter_block_type_metadata_settings( $settings, $metadata ) {
		if ( 'core/gallery' !== $metadata['name'] ) {
			return $settings;
		}

		$settings['attributes']['bentoLightbox'] = array(
			'type'    => 'boolean',
			'default' => false,
		);

		return $settings;
	}

}
