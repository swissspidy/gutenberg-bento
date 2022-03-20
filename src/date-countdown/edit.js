import { BentoDateCountdown } from '@bentoproject/date-countdown/react';
import '@bentoproject/base-carousel/styles.css';

import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	ToggleControl,
	DateTimePicker,
	SelectControl,
} from '@wordpress/components';
import { CountdownContextProvider } from './context';

function CountdownEdit(props) {
	const { attributes, setAttributes } = props;
	const { dateTime, whenEnded, biggestUnit, countUp } = attributes;

	function toggleCountUp() {
		setAttributes({ countUp: !countUp });
	}

	function toggleWhenEnded(newValue) {
		setAttributes({
			whenEnded: true === newValue ? 'continue' : 'stop',
		});
	}

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Countdown Settings', 'gutenberg-bento')}>
					<ToggleControl
						label={__(
							'Reverse direction (count up)',
							'gutenberg-bento'
						)}
						checked={countUp}
						onChange={toggleCountUp}
					/>
					<SelectControl
						label={__('Biggest Unit', 'gutenberg-bento')}
						value={biggestUnit} // e.g: value = [ 'a', 'c' ]
						onChange={(newValue) => {
							setAttributes({ biggestUnit: newValue });
						}}
						options={[
							{
								value: 'days',
								label: __('Days', 'gutenberg-bento'),
							},
							{
								value: 'hours',
								label: __('Hours', 'gutenberg-bento'),
							},
							{
								value: 'minutes',
								label: __('Minutes', 'gutenberg-bento'),
							},
							{
								value: 'seconds',
								label: __('Seconds', 'gutenberg-bento'),
							},
						]}
					/>
					<ToggleControl
						label={__(
							'Continue timer when reaching end',
							'gutenberg-bento'
						)}
						checked={'continue' === whenEnded}
						onChange={toggleWhenEnded}
					/>
					<DateTimePicker
						currentDate={new Date(dateTime)}
						onChange={(newDate) =>
							setAttributes({ dateTime: newDate })
						}
						is12Hour={true}
					/>
				</PanelBody>
			</InspectorControls>
			<div {...blockProps}>
				<BentoDateCountdown
					datetime={dateTime}
					whenEnded={whenEnded}
					biggestUnit={biggestUnit.toUpperCase()}
					countUp={countUp}
					render={(data) => (
						<CountdownContextProvider value={data}>
							<InnerBlocks />
						</CountdownContextProvider>
					)}
				/>
			</div>
		</>
	);
}

export default CountdownEdit;
