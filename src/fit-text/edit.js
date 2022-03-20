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
import { useState } from '@wordpress/element';

function FitTextEdit(props) {
	const { attributes } = props;
	const { fittedText, minFontSize, maxFontSize } = attributes;

	const [height, setHeight] = useState(200);
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
						setHeight(height + delta.height);
					}}
				>
					<BentoFitText
						className="bento-fit-text"
						style={{ height }}
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
