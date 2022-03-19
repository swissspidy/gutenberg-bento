<?php
/**
 * Main plugin functionality.
 *
 * @package   Google\Gutenberg_Bento
 * @copyright 2021 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://github.com/swissspidy/gutenberg-bento
 */

/**
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Gutenberg_Bento;

use function amp_is_request;

const BENTO_RUNTIME_SCRIPT_HANDLE       = 'bento-runtime';
const BENTO_BASE_CAROUSEL_SCRIPT_HANDLE = 'bento-base-carousel';
const BENTO_BASE_CAROUSEL_VERSION       = '1.0';

// @todo Maybe these should be obtained from block.json directly?
const BLOCK_STYLE_HANDLE       = 'gutenberg-bento-carousel';
const BLOCK_VIEW_SCRIPT_HANDLE = 'gutenberg-bento-carousel-view';

/**
 * Bootstraps the plugin.
 *
 * @return void
 */
function boostrap() {
	add_action( 'init', __NAMESPACE__ . '\register_bento_assets' );
	add_action( 'init', __NAMESPACE__ . '\register_carousel_block_assets' );
	add_action( 'init', __NAMESPACE__ . '\register_carousel_block_type' );
	add_action( 'enqueue_block_assets', __NAMESPACE__ . '\unregister_asset_dependencies_on_amp', 9 ); // The 9 to before wp_enqueue_registered_block_scripts_and_styles().

	add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\filter_kses_allowed_html' );
	add_filter( 'amp_content_sanitizers', __NAMESPACE__ . '\add_amp_content_sanitizer' );
}

/**
 * Registers the scripts and styles for Bento components.
 *
 * Note that 'amp-runtime' and 'bento-base-carousel' are scripts registered by the AMP plugin. The Bento versions aren't
 * currently registered, so that is why this function needs to run currently on AMP pages.
 *
 * @return void
 */
function register_bento_assets() {
	wp_register_script( BENTO_RUNTIME_SCRIPT_HANDLE, 'https://cdn.ampproject.org/bento.js', array(), false, true );

	$src    = sprintf( 'https://cdn.ampproject.org/v0/bento-base-carousel-%s.js', BENTO_BASE_CAROUSEL_VERSION );
	$script = wp_scripts()->query( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
	if ( $script ) {
		// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
		$script->src = $src;
	} else {
		wp_register_script( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE, $src, array( BENTO_RUNTIME_SCRIPT_HANDLE ), null, true );
	}

	// At the moment the AMP plugin does not register styles for Bento components, but this could change with <https://github.com/ampproject/amp-wp/pull/6353>.
	$src   = sprintf( 'https://cdn.ampproject.org/v0/bento-base-carousel-%s.css', BENTO_BASE_CAROUSEL_VERSION );
	$style = wp_styles()->query( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
	if ( $style ) {
		// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
		$style->src = $src;
	} else {
		wp_register_style( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE, $src, array(), null, false );
	}

	/**
	 * Filters whether to enqueue self-hosted Bento components instead of using the CDN.
	 *
	 * @param bool $self_host Whether to self-host. Default false.
	 */
	$self_host = (bool) apply_filters( 'gutenberg_bento_self_host', false );

	if ( $self_host ) {
		$web_component_asset_file = plugin_dir_path( __DIR__ ) . 'build/bento-base-carousel.asset.php';
		$web_component_asset      = is_readable( $web_component_asset_file ) ? require $web_component_asset_file : array();
		$web_component_version    = isset( $web_component_asset['version'] ) ? $web_component_asset['version'] : false;

		$style = wp_styles()->query( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
		if ( $style ) {
			$style->src = plugin_dir_url( __DIR__ ) . 'build/bento-base-carousel.css';
			$style->ver = $web_component_version;
		}
		$script = wp_scripts()->query( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE );
		if ( $script ) {
			$script->src  = plugin_dir_url( __DIR__ ) . 'build/bento-base-carousel.js';
			$script->ver  = $web_component_version;
			$script->deps = array(); // bento.js runtime is not needed when self-hosting.
		}
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
function filter_kses_allowed_html( $tags ) {
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
 * Registers the scripts and styles for the carousel block.
 *
 * @return void
 */
function register_carousel_block_assets() {
	$edit_asset_file   = plugin_dir_path( __DIR__ ) . 'build/carousel.asset.php';
	$edit_asset        = is_readable( $edit_asset_file ) ? require $edit_asset_file : array();
	$edit_version      = isset( $edit_asset['version'] ) ? $edit_asset['version'] : false;
	$edit_dependencies = isset( $edit_asset['dependencies'] ) ? $edit_asset['dependencies'] : array();

	$view_asset_file     = plugin_dir_path( __DIR__ ) . 'build/carousel.view.asset.php';
	$view_asset          = is_readable( $view_asset_file ) ? require $view_asset_file : array();
	$view_version        = isset( $view_asset['version'] ) ? $view_asset['version'] : false;
	$view_dependencies   = isset( $view_asset['dependencies'] ) ? $view_asset['dependencies'] : array();
	$view_dependencies[] = BENTO_BASE_CAROUSEL_SCRIPT_HANDLE;

	// Both used only in editor.
	wp_register_style( 'gutenberg-bento-carousel-edit', plugin_dir_url( __DIR__ ) . 'build/carousel.css', array(), $edit_version );
	wp_register_script( 'gutenberg-bento-carousel-edit', plugin_dir_url( __DIR__ ) . 'build/carousel.js', $edit_dependencies, $edit_version );

	// Used in editor + frontend.
	wp_register_style( BLOCK_STYLE_HANDLE, plugin_dir_url( __DIR__ ) . 'build/carousel.view.css', array( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE ), $view_version );

	// Used only on frontend.
	wp_register_script( BLOCK_VIEW_SCRIPT_HANDLE, plugin_dir_url( __DIR__ ) . 'build/carousel.view.js', $view_dependencies, $view_version );
}

/**
 * When on AMP pages, prevent the view script and other scripts/styles from being printed. These are handled by AMP.
 *
 * @return void
 */
function unregister_asset_dependencies_on_amp() {
	// Note the is_amp() function can only be called at or after the `wp` action.
	if ( ! is_amp() ) {
		return;
	}

	$style = wp_styles()->query( BLOCK_STYLE_HANDLE );
	if ( $style ) {
		$style->deps = array_diff( $style->deps, array( BENTO_BASE_CAROUSEL_SCRIPT_HANDLE ) );
	}
}

/**
 * Registers the carousel block type.
 *
 * @return void
 */
function register_carousel_block_type() {
	register_block_type(
		plugin_dir_path( __DIR__ ) . './src/carousel/block.json',
		array(
			'render_callback' => __NAMESPACE__ . '\render_carousel_block',
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
function render_carousel_block( $attributes, $content ) {
	// Note this is a temporary measure until Gutenberg 11 is live. See <https://github.com/WordPress/gutenberg/pull/32977>.
	if ( ! is_admin() && ! is_amp() ) {
		wp_enqueue_script( BLOCK_VIEW_SCRIPT_HANDLE );
	}

	if ( is_rtl() ) {
		$content = str_replace( '<bento-base-carousel', '<bento-base-carousel dir="rtl"', $content );
	}

	return $content;
}

/**
 * Determines whether the current request is for an AMP document.
 *
 * @return bool Whether the current request is for an AMP document or not.
 */
function is_amp() {
	return ( function_exists( 'amp_is_request' ) && amp_is_request() );
}

/**
 * Adds a new AMP sanitizer for the <bento-base-carousel> to ensure validity.
 *
 * @param array $sanitizers List of sanitizers.
 *
 * @return array Filtered list of sanitizers.
 */
function add_amp_content_sanitizer( $sanitizers ) {
	if ( class_exists( \AMP_Base_Sanitizer::class ) ) {
		require_once __DIR__ . '/class-amp-carousel-sanitizer.php';

		$sanitizers[ AMP_Carousel_Sanitizer::class ] = array();
	}

	return $sanitizers;
}
