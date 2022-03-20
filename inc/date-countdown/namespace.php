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

namespace Google\Gutenberg_Bento\Date_Countdown;

const BENTO_RUNTIME_SCRIPT_HANDLE        = 'bento-runtime';
const BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE = 'bento-date-countdown';
const BENTO_DATE_COUNTDOWN_VERSION       = '1.0';

/**
 * Bootstraps the plugin.
 *
 * @return void
 */
function boostrap() {
	add_action( 'init', __NAMESPACE__ . '\register_bento_assets' );
	add_action( 'init', __NAMESPACE__ . '\register_block_assets' );
	add_action( 'init', __NAMESPACE__ . '\register_block_type' );

	add_filter( 'wp_kses_allowed_html', __NAMESPACE__ . '\filter_kses_allowed_html' );
}

/**
 * Registers the scripts and styles for Bento components.
 *
 * Note that 'amp-runtime' and 'bento-date-countdown' are scripts registered by the AMP plugin. The Bento versions aren't
 * currently registered, so that is why this function needs to run currently on AMP pages.
 *
 * @return void
 */
function register_bento_assets() {
	wp_register_script( BENTO_RUNTIME_SCRIPT_HANDLE, 'https://cdn.ampproject.org/bento.js', array(), false, true );

	wp_register_script( BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE, sprintf( 'https://cdn.ampproject.org/v0/bento-date-countdown-%s.js', BENTO_DATE_COUNTDOWN_VERSION ), array( BENTO_RUNTIME_SCRIPT_HANDLE ), null, true );

	// At the moment the AMP plugin does not register styles for Bento components, but this could change with <https://github.com/ampproject/amp-wp/pull/6353>.
	wp_register_style( BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE, sprintf( 'https://cdn.ampproject.org/v0/bento-date-countdown-%s.css', BENTO_DATE_COUNTDOWN_VERSION ), array(), null, false );

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

		$script = wp_scripts()->query( BENTO_DATE_COUNTDOWN_SCRIPT_HANDLE );
		if ( $script ) {
			$script->src  = plugin_dir_url( __DIR__ ) . 'build/bento-date-countdown.js';
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

/**
 * Registers the scripts and styles for the date-countdown block.
 *
 * @return void
 */
function register_block_assets() {
	$edit_asset_file   = plugin_dir_path( dirname( __DIR__ ) ) . 'build/date-countdown.asset.php';
	$edit_asset        = is_readable( $edit_asset_file ) ? require $edit_asset_file : array();
	$edit_version      = isset( $edit_asset['version'] ) ? $edit_asset['version'] : false;
	$edit_dependencies = isset( $edit_asset['dependencies'] ) ? $edit_asset['dependencies'] : array();

	// Both used only in editor.
	wp_register_style(
		'gutenberg-bento-date-countdown-edit',
		plugin_dir_url( dirname( __DIR__ ) ) . 'build/date-countdown.css',
		array(),
		$edit_version
	);
	wp_register_script(
		'gutenberg-bento-date-countdown-edit',
		plugin_dir_url( dirname( __DIR__ ) ) . 'build/date-countdown.js',
		$edit_dependencies,
		$edit_version
	);
}

/**
 * Registers the date-countdown block type.
 *
 * @return void
 */
function register_block_type() {
	\register_block_type( plugin_dir_path( __FILE__ ) );
}
