/* This JS is only used in the editor. */

import { registerBlockType } from '@wordpress/blocks';
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import edit from './edit';
import save from './save';
import { Context } from './context';

export const settings = {
	edit,
	save,
};

registerBlockType('gutenberg-bento/date-countdown', settings);

registerBlockType('gutenberg-bento/countdown-days', {
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
	title: __('Seconds', 'gutenberg-bento'),
	edit: function BlockEdit() {
		const { ss } = useContext(Context);
		return ss || null;
	},
	save: () => {
		return '{{ss}}';
	},
});
