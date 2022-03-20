import {
	BentoAccordionSection,
	BentoAccordionHeader,
	BentoAccordionContent,
} from '@bentoproject/accordion/react';
import {
	InspectorControls,
	RichText,
	useBlockProps,
	useInnerBlocksProps,
} from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const TEMPLATE = [['core/paragraph', { content: 'Section content' }]];

export default function Edit({
	attributes: { id, title },
	setAttributes,
	isSelected,
}) {
	const blockProps = useBlockProps();
	const innerBlockProps = useInnerBlocksProps(
		{},
		{
			template: TEMPLATE,
		}
	);

	return (
		<>
			<div {...blockProps}>
				<BentoAccordionSection>
					<BentoAccordionHeader expanded={isSelected}>
						<RichText
							tagName="h2"
							aria-label={__('Section title', 'gutenberg-bento')}
							placeholder={__('Add title…', 'gutenberg-bento')}
							withoutInteractiveFormatting
							value={title}
							onChange={(nextValue) =>
								setAttributes({ title: nextValue })
							}
						/>
					</BentoAccordionHeader>
					<BentoAccordionContent>
						<div {...innerBlockProps} />
					</BentoAccordionContent>
				</BentoAccordionSection>
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
