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

/**
 * Bootstraps the plugin.
 *
 * @return void
 */
function boostrap() {
	add_action( 'init', __NAMESPACE__ . '\register_bento_components' );
	add_action( 'init', __NAMESPACE__ . '\register_carousel_block' );
	add_action( 'init', __NAMESPACE__ . '\register_carousel_block_type' );
}

/**
 * Registers the scripts and styles for Bento components.
 *
 * @return void
 */
function register_bento_components() {
	wp_register_script( 'amp-runtime', 'https://cdn.ampproject.org/v0.js', array(), false );

	wp_register_style( 'amp-bento-carousel', 'https://cdn.ampproject.org/v0/amp-base-carousel-1.0.css', array(), false );
	wp_register_script( 'amp-bento-carousel', 'https://cdn.ampproject.org/v0/amp-base-carousel-1.0.js', array( 'amp-runtime' ), false );
}

/**
 * Registers the scripts and styles for the carousel block.
 *
 * @return void
 */
function register_carousel_block() {
	$edit_asset_file   = plugin_dir_path( __DIR__ ) . 'build/carousel.asset.php';
	$edit_asset        = is_readable( $edit_asset_file ) ? require $edit_asset_file : array();
	$edit_dependencies = isset( $edit_asset['dependencies'] ) ? $edit_asset['dependencies'] : array();

	$view_asset_file     = plugin_dir_path( __DIR__ ) . 'build/carousel.view.asset.php';
	$view_asset          = is_readable( $view_asset_file ) ? require $view_asset_file : array();
	$view_dependencies   = isset( $view_asset['dependencies'] ) ? $view_asset['dependencies'] : array();
	$view_dependencies[] = 'amp-bento-carousel';

	// Both used only in editor.
	wp_register_style( 'gutenberg-bento-carousel-edit', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.css', array(), $edit_asset['version'] );
	wp_register_script( 'gutenberg-bento-carousel-edit', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.js', $edit_dependencies, $edit_asset['version'] );

	// Used in editor + frontend.
	wp_register_style( 'gutenberg-bento-carousel', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.view.css', array( 'amp-bento-carousel' ), $view_asset['version'] );

	// Used only on frontend.
	wp_register_script( 'gutenberg-bento-carousel-view', plugins_url( basename( dirname( __DIR__ ) ) ) . '/build/carousel.view.js', $view_dependencies, $view_asset['version'] );
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
 * Enqueues scripts and styles needed on the frontend
 * and adds the `dir` HTML attribute if in RTL.
 *
 * @param array  $attributes Block attributes.
 * @param string $content Block content.
 *
 * @return string
 */
function render_carousel_block( $attributes, $content ) {
	if ( ! wp_script_is( 'gutenberg-bento-carousel-view' ) && ! is_admin() ) {
		wp_enqueue_script( 'gutenberg-bento-carousel-view' );
	}

	if ( ! wp_style_is( 'gutenberg-bento-carousel' ) && ! is_admin() ) {
		wp_enqueue_style( 'gutenberg-bento-carousel' );
	}

	if ( is_rtl() ) {
		$content = str_replace( '<amp-base-carousel', '<amp-base-carousel dir="rtl"', $content );
	}

	return $content;
}
