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

use WP_Block_Type_Registry;
use WP_Block_Type;
use AmpProject\Extension;
use WP_Dependencies;

const AMP_RUNTIME_SCRIPT_HANDLE = 'amp-runtime';
const BASE_CAROUSEL_VERSION     = '1.0';

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

	add_filter( 'amp_content_sanitizers', __NAMESPACE__ . '\add_amp_content_sanitizer' );
}

/**
 * Registers the scripts and styles for Bento components.
 *
 * Note that 'amp-runtime' and 'amp-base-carousel' are scripts registered by the AMP plugin. The Bento versions aren't
 * currently registered, so that is why this function needs to run currently on AMP pages.
 *
 * @return void
 */
function register_bento_assets() {
	if ( ! wp_script_is( AMP_RUNTIME_SCRIPT_HANDLE, 'registered' ) ) {
		wp_register_script( AMP_RUNTIME_SCRIPT_HANDLE, 'https://cdn.ampproject.org/v0.js', array(), null );
	}

	// Note that amp-runtime will not eventually be a dependency for Bento.
	$src    = sprintf( 'https://cdn.ampproject.org/v0/amp-base-carousel-%s.js', BASE_CAROUSEL_VERSION );
	$script = wp_scripts()->query( Extension::BASE_CAROUSEL );
	if ( $script ) {
		// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
		$script->src = $src;
	} else {
		wp_register_script( Extension::BASE_CAROUSEL, $src, array( AMP_RUNTIME_SCRIPT_HANDLE ), null, false );
	}

	// At the moment the AMP plugin does not register styles for Bento components, but this could change with <https://github.com/ampproject/amp-wp/pull/6353>.
	$src   = sprintf( 'https://cdn.ampproject.org/v0/amp-base-carousel-%s.css', BASE_CAROUSEL_VERSION );
	$style = wp_styles()->query( Extension::BASE_CAROUSEL );
	if ( $style ) {
		// Make sure that 1.0 (Bento) is used instead of 0.1 (latest).
		$style->src = $src;
	} else {
		wp_register_style( Extension::BASE_CAROUSEL, $src, array(), null, false );
	}
}

/**
 * Registers the scripts and styles for the carousel block.
 *
 * @return void
 */
function register_carousel_block_assets() {
	$edit_asset_file   = plugin_dir_path( __DIR__ ) . 'build/carousel.asset.php';
	$edit_asset        = is_readable( $edit_asset_file ) ? require $edit_asset_file : array();
	$edit_dependencies = isset( $edit_asset['dependencies'] ) ? $edit_asset['dependencies'] : array();

	$view_asset_file     = plugin_dir_path( __DIR__ ) . 'build/carousel.view.asset.php';
	$view_asset          = is_readable( $view_asset_file ) ? require $view_asset_file : array();
	$view_dependencies   = isset( $view_asset['dependencies'] ) ? $view_asset['dependencies'] : array();
	$view_dependencies[] = Extension::BASE_CAROUSEL;

	// Both used only in editor.
	wp_register_style( 'gutenberg-bento-carousel-edit', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.css', array(), $edit_asset['version'] );
	wp_register_script( 'gutenberg-bento-carousel-edit', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.js', $edit_dependencies, $edit_asset['version'] );

	// Used in editor + frontend.
	wp_register_style( 'gutenberg-bento-carousel', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.view.css', array( Extension::BASE_CAROUSEL ), $view_asset['version'] );

	// Used only on frontend.
	wp_register_script( 'gutenberg-bento-carousel-view', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.view.js', $view_dependencies, $view_asset['version'] );
}

/**
 * When on AMP pages, prevent the view script and other scripts/styles from being printed. These are handled by AMP.
 *
 * @return void
 */
function unregister_asset_dependencies_on_amp() {
	if ( ! is_amp() ) {
		return;
	}

	// Prevent the view script from being enqueued on AMP pages, since it will be added automatically by the AMP plugin.
	$block_type = WP_Block_Type_Registry::get_instance()->get_registered( 'gutenberg-bento/carousel' );
	if ( $block_type instanceof WP_Block_Type ) {
		$block_type->script = null;
	}

	// Prevent the script and style from being printed on AMP pages since AMP handles these automatically.
	foreach ( array( wp_scripts(), wp_styles() ) as $dependencies ) {
		/** @var WP_Dependencies $dependencies */
		$dependency = $dependencies->query( Extension::BASE_CAROUSEL );
		if ( $dependency ) {
			$dependency->src = false;
		}
	}
}

/**
 * Registers the carousel block type.
 *
 * @return void
 */
function register_carousel_block_type() {
	register_block_type(
		plugin_dir_path( __DIR__ ) . 'block.json',
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
	if ( is_rtl() ) {
		$content = str_replace( '<amp-base-carousel', '<amp-base-carousel dir="rtl"', $content );
	}

	return $content;
}

/**
 * Determines whether the current request is for an AMP document.
 *
 * @return bool Whether the current request is for an AMP document or not.
 */
function is_amp() {
	return ( function_exists( '\amp_is_request' ) && \amp_is_request() );
}

/**
 * Adds a new AMP sanitizer for the <amp-base-carousel> to ensure validity.
 *
 * @param array $sanitizers List of sanitizers.
 *
 * @return array Filtered list of sanitizers.
 */
function add_amp_content_sanitizer( $sanitizers ) {
	require_once __DIR__ . '/class-amp-carousel-sanitizer.php';

	$sanitizers[ AMP_Carousel_Sanitizer::class ] = array();

	return $sanitizers;
}
