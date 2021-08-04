<?php
/**
 * AMP carousel sanitizer
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
 * Class AMP_Carousel_Sanitizer.
 */
class AMP_Carousel_Sanitizer extends \AMP_Base_Sanitizer {
	/**
	 * Sanitize the HTML contained in the DOMDocument received by the constructor.
	 *
	 * @return void
	 */
	public function sanitize() {
		$carousels = $this->dom->getElementsByTagName( 'amp-base-carousel' );

		/*
		 * @var \DOMElement $carousel
		 */
		foreach ( $carousels as $carousel ) {
			if ( ! $carousel->hasAttribute( 'class' ) || ! $carousel->getAttribute( 'class' ) ) {
				continue;
			}

			$carousel->setAttribute( 'layout', 'responsive' );

			// Same 4:1 aspect ratio as in view.css.
			$carousel->setAttribute( 'width', '4' );
			$carousel->setAttribute( 'height', '1' );

			// React saves `loop={ true }` always as boolean flag `loop`, but AMP does not allow this. It expects `loop="true"`.
			if ( $carousel->hasAttribute( 'loop' ) ) {
				$carousel->setAttribute( 'loop', 'true' );
			}

			$carousel_id = $carousel->getAttribute( 'id' );

			// Allow controlling the carousel using amp-bind similar to how carousel.view.js does.

			/*
			 * @var \DOMElement $prev_button
			 */
			$prev_button = $this->dom->xpath->query( '//button[ contains( @class, "gutenberg-bento-carousel-buttons__prev" ) ]', $carousel )->item( 0 );
			$prev_button->setAttribute( 'on', "tap:$carousel_id.prev()" );

			/*
			 * @var \DOMElement $next_button
			 */
			$next_button = $this->dom->xpath->query( '//button[ contains( @class, "gutenberg-bento-carousel-buttons__next" ) ]', $carousel )->item( 0 );
			$next_button->setAttribute( 'on', "tap:$carousel_id.next()" );
		}
	}
}
