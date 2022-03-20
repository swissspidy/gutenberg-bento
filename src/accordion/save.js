/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes: { animate, id } }) {
	return (
		<bento-accordion
			{...useInnerBlocksProps.save(
				useBlockProps.save({
					animate: animate ? 'true' : undefined,
					id,
				})
			)}
		/>
	);
}
