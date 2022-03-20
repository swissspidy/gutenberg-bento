/**
 * WordPress dependencies
 */
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { dateTime, whenEnded, biggestUnit, countUp } = attributes;

	return (
		<figure {...useBlockProps.save()}>
			<bento-date-countdown
				end-date={dateTime}
				when-ended={whenEnded}
				biggest-unit={biggestUnit.toUpperCase()}
				count-up={countUp}
			>
				<template>
					<InnerBlocks.Content />
				</template>
			</bento-date-countdown>
		</figure>
	);
}
