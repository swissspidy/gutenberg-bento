import { BentoAccordion } from '@bentoproject/accordion/react';
import {
	InnerBlocks,
	InspectorControls,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import '@bentoproject/accordion/styles.css';

const ACCORDION_SECTION_BLOCK = 'gutenberg-bento/accordion-section';
const ALLOWED_BLOCKS = [ACCORDION_SECTION_BLOCK];
const TEMPLATE = [[ACCORDION_SECTION_BLOCK]];

export default function Edit({ attributes: { id }, setAttributes }) {
	const blockProps = useBlockProps();
	const innerBlockProps = useInnerBlocksProps(
		{},
		{
			allowedBlocks: ALLOWED_BLOCKS,
			renderAppender: InnerBlocks.ButtonBlockAppender,
			template: TEMPLATE,
		}
	);
	return (
		<>
			<div {...blockProps}>
				<BentoAccordion>
					<div {...innerBlockProps} />
				</BentoAccordion>
			</div>
			<InspectorControls __experimentalGroup="advanced">
				<TextControl
					autoComplete="off"
					label={__('HTML id attribute', 'gutenberg-bento')}
					onChange={(nextValue) => {
						setAttributes({
							id: nextValue !== '' ? nextValue : undefined,
						});
					}}
					value={id || ''}
				/>
			</InspectorControls>
		</>
	);
}
