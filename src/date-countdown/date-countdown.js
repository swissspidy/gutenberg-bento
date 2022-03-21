/* This JS is only used in the editor. */

import { registerBlockType } from '@wordpress/blocks';
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { calendar as icon } from '@wordpress/icons';

import edit from './edit';
import save from './save';
import { Context } from './context';

registerBlockType('gutenberg-bento/date-countdown', {
	icon,
	category: 'gutenberg-bento-blocks',
	edit,
	save,
});

registerBlockType('gutenberg-bento/countdown-days', {
	icon,
	category: 'gutenberg-bento-blocks',
	title: 'Days',
	edit: function BlockEdit() {
		const { dd } = useContext(Context);
		return dd || null;
	},
	save: () => {
		return '{{dd}}';
	},
});

registerBlockType('gutenberg-bento/countdown-hours', {
	icon,
	category: 'gutenberg-bento-blocks',
	title: __('Hours', 'gutenberg-bento'),
	edit: function BlockEdit() {
		const { hh } = useContext(Context);
		return hh || null;
	},
	save: () => {
		return '{{hh}}';
	},
});

registerBlockType('gutenberg-bento/countdown-minutes', {
	icon,
	category: 'gutenberg-bento-blocks',
	title: __('Minutes', 'gutenberg-bento'),
	edit: function BlockEdit() {
		const { mm } = useContext(Context);
		return mm || null;
	},
	save: () => {
		return '{{mm}}';
	},
});

registerBlockType('gutenberg-bento/countdown-seconds', {
	icon,
	category: 'gutenberg-bento-blocks',
	title: __('Seconds', 'gutenberg-bento'),
	edit: function BlockEdit() {
		const { ss } = useContext(Context);
		return ss || null;
	},
	save: () => {
		return '{{ss}}';
	},
});
