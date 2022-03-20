/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes: { expanded, id, title } }) {
	return (
		<section
			{...useBlockProps.save({
				expanded: expanded ? 'true' : undefined,
				id,
			})}
		>
			<h2>{title}</h2>
			<div {...useInnerBlocksProps.save()} />
		</section>
	);
}
