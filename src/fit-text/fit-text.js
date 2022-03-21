/* This JS is only used in the editor. */

import { registerBlockType } from '@wordpress/blocks';
import { alignJustify as icon } from '@wordpress/icons';

import metadata from './block.json';
import edit from './edit';
import save from './save';

const { name } = metadata;

export { metadata, name };

export const settings = {
	icon,
	example: {
		attributes: {
			fittedText: '',
			minFontSize: '10px',
			maxFontSize: '200px',
		},
	},
	edit,
	save,
};

registerBlockType('gutenberg-bento/fit-text', settings);
