import { BentoFitText } from '@bentoproject/fit-text/react';
import '@bentoproject/fit-text/styles.css';
import './view.css';

import {
	RichText,
	useBlockProps,
	InspectorControls,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalUnitControl as UnitControl,
	ResizableBox,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

import './edit.css';

function FitTextEdit(props) {
	const { attributes, setAttributes } = props;
	const { fittedText, minFontSize, maxFontSize, height = 200 } = attributes;

	const [localHeight, setLocalHeight] = useState(height);

	useEffect(() => {
		setAttributes({ height: localHeight });
	}, [localHeight]);

	function setMinFontSize(fontSize) {
		props.setAttributes({ minFontSize: fontSize });
	}

	function setMaxFontSize(fontSize) {
		props.setAttributes({ maxFontSize: fontSize });
	}

	function setFittedText(text) {
		props.setAttributes({ fittedText: text });
	}

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Fit-Text Settings', 'gutenberg-bento')}>
					<UnitControl
						label={__('Minimum Font Size', 'gutenberg-bento')}
						onChange={setMinFontSize}
						units={[
							{
								a11yLabel: 'Pixels (px)',
								label: 'px',
								step: 1,
								value: 'px',
							},
						]}
						value={minFontSize}
					/>
					<UnitControl
						label={__('Maximum Font Size', 'gutenberg-bento')}
						onChange={setMaxFontSize}
						units={[
							{
								a11yLabel: 'Pixels (px)',
								label: 'px',
								step: 1,
								value: 'px',
							},
						]}
						value={maxFontSize}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<ResizableBox
					minHeight={200}
					enable={{ bottom: true, right: false }}
					onResizeStop={(event, direction, elt, delta) => {
						setLocalHeight(
							(prevHeight) => prevHeight + delta.height
						);
					}}
				>
					<BentoFitText
						className="bento-fit-text"
						style={{ height: localHeight }}
						minFontSize={parseInt(
							minFontSize.replace('px', ''),
							10
						)}
						maxFontSize={parseInt(
							maxFontSize.replace('px', ''),
							10
						)}
					>
						<RichText value={fittedText} onChange={setFittedText} />
					</BentoFitText>
				</ResizableBox>
			</div>
		</>
	);
}

export default FitTextEdit;
