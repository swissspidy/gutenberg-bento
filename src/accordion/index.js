import { registerBlockType } from '@wordpress/blocks';
import { stretchFullWidth as icon } from '@wordpress/icons';

import metadata from './block.json';
import Edit from './edit';
import save from './save';

registerBlockType(metadata.name, {
	icon,
	edit: Edit,
	save,
});
