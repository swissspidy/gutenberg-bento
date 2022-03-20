/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes: { id } }) {
	return (
		<bento-accordion
			{...useInnerBlocksProps.save(useBlockProps.save({ id }))}
		/>
	);
}
