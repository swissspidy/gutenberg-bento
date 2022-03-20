/**
 * WordPress dependencies
 */
import { useInnerBlocksProps, useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes: { id, title } }) {
	return (
		<section
			{...useBlockProps.save({
				id,
			})}
		>
			<h2>{title}</h2>
			<div {...useInnerBlocksProps.save()} />
		</section>
	);
}
