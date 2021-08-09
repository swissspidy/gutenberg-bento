<?php

namespace Google\Gutenberg_Bento\Tests;

use WP_UnitTestCase;
use function Google\Gutenberg_Bento\boostrap;

class Plugin_Test extends WP_UnitTestCase {
	public function test_boostrap() {
		boostrap();
		$this->assertSame( 10, has_action( 'init', 'Google\Gutenberg_Bento\register_bento_assets' ) );
		$this->assertSame( 10, has_action( 'init', 'Google\Gutenberg_Bento\register_carousel_block_assets' ) );
		$this->assertSame( 10, has_action( 'init', 'Google\Gutenberg_Bento\register_carousel_block_type' ) );
		$this->assertSame( 9, has_action( 'enqueue_block_assets', 'Google\Gutenberg_Bento\unregister_asset_dependencies_on_amp' ) );
		$this->assertSame( 10, has_action( 'amp_content_sanitizers', 'Google\Gutenberg_Bento\add_amp_content_sanitizer' ) );
	}
}
