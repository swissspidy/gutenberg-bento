/**
 * WordPress dependencies
 */
import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const {
		autoAdvance,
		loop,
		snap,
		direction,
		mixedLengths,
		controls,
		orientation,
		visibleCount,
	} = attributes;

	return (
		<figure {...useBlockProps.save()}>
			<bento-base-carousel
				className={'gutenberg-bento-carousel-wrapper'}
				auto-advance={autoAdvance ? 'true' : undefined}
				loop={loop ? 'true' : undefined}
				snap={snap ? 'true' : undefined}
				dir={direction ? 'true' : undefined}
				orientation={orientation}
				controls={controls}
				visible-count={visibleCount + ''}
				mixed-lengths={mixedLengths ? 'true' : undefined}
			>
				<InnerBlocks.Content />
			</bento-base-carousel>
		</figure>
	);
}
