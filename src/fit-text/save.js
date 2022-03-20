/**
 * WordPress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { fittedText, minFontSize, maxFontSize } = attributes;

	const className = 'bento-fit-text';

	return (
		<bento-fit-text
			{...useBlockProps.save() }
			className={ className }
			min-font-size={ parseInt( minFontSize.replace( 'px', '' ), 10 ) }
			max-font-size={ parseInt( maxFontSize.replace( 'px', '' ), 10 ) }
		>{ fittedText }</bento-fit-text>
	);
}