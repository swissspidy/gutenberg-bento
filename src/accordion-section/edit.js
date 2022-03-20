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
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

const TEMPLATE = [['core/paragraph', { content: 'Section content' }]];

export default function Edit({
	attributes: { expanded, id, title },
	setAttributes,
}) {
	const blockProps = useBlockProps();
	const innerBlockProps = useInnerBlocksProps(
		{},
		{
			template: TEMPLATE,
		}
	);
	const updateTitle = useCallback((nextValue) => {
		setAttributes({ title: nextValue });
	}, []);
	const toggleExpanded = useCallback((nextValue) => {
		setAttributes({ expanded: nextValue });
	}, []);
	const updateId = useCallback((nextValue) => {
		setAttributes({
			id: nextValue !== '' ? nextValue : undefined,
		});
	}, []);

	return (
		<>
			<div {...blockProps}>
				<BentoAccordionSection expanded={expanded}>
					<BentoAccordionHeader>
						<RichText
							tagName="h2"
							aria-label={__('Section title', 'gutenberg-bento')}
							placeholder={__('Add title…', 'gutenberg-bento')}
							withoutInteractiveFormatting
							value={title}
							onChange={updateTitle}
						/>
					</BentoAccordionHeader>
					<BentoAccordionContent>
						<div {...innerBlockProps} />
					</BentoAccordionContent>
				</BentoAccordionSection>
			</div>
			<InspectorControls>
				<PanelBody title={__('Settings', 'gutenberg-bento')}>
					<ToggleControl
						label={__('Expanded', 'gutenberg-bento')}
						checked={expanded}
						onChange={toggleExpanded}
					/>
				</PanelBody>
			</InspectorControls>
			<InspectorControls __experimentalGroup="advanced">
				<TextControl
					autoComplete="off"
					label={__('HTML id attribute', 'gutenberg-bento')}
					onChange={updateId}
					value={id || ''}
				/>
			</InspectorControls>
		</>
	);
}
