/* This JS is only used in the editor. */

import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import edit from './edit';
import save from './save';

const { name } = metadata;

export { metadata, name };

export const settings = {
	example: {
		attributes: {
			images: [
				{
					url: 'https://s.w.org/images/core/5.3/Glacial_lakes%2C_Bhutan.jpg',
				},
				{
					url: 'https://s.w.org/images/core/5.3/Sediment_off_the_Yucatan_Peninsula.jpg',
				},
			],
		},
	},
	edit,
	save,
};

registerBlockType('gutenberg-bento/carousel', settings);
