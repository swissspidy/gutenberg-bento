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
	Button,
	Dropdown, PanelRow,
} from '@wordpress/components';
import { CountdownContextProvider } from './context';
import { useRef } from '@wordpress/element';

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

	const anchorRef = useRef();

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
						value={biggestUnit}
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
					<PanelRow ref={anchorRef}>
						<span>{__('Publish', 'gutenberg-bento')}</span>
						<Dropdown
							popoverProps={{ anchorRef: anchorRef.current }}
							position="bottom left"
							contentClassName="date-countdown__dialog"
							renderToggle={({ onToggle, isOpen }) => (
								<>
									<Button
										className="edit-post-post-schedule__toggle"
										onClick={onToggle}
										aria-expanded={isOpen}
										variant="tertiary"
									>
										{dateTime
											? new Date(dateTime).toISOString()
											: __(
													'Choose date',
													'gutenberg-bento'
											  )}
									</Button>
								</>
							)}
							renderContent={() => (
								<DateTimePicker
									currentDate={dateTime}
									onChange={(newDate) =>
										setAttributes({ dateTime: newDate })
									}
									is12Hour={true}
								/>
							)}
						/>
					</PanelRow>
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
