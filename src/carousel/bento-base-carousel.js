import { defineElement } from '@bentoproject/base-carousel';

// Just so webpack generates a separate stylesheet for us to enqueue in WP.
import '@bentoproject/base-carousel/styles.css';
// import '@bentoproject/base-carousel/web-component.css'; // eslint-disable-line import/no-unresolved -- False positive.

if (
	document.readyState === 'complete' ||
	document.readyState === 'interactive'
) {
	defineElement();
}

document.addEventListener('DOMContentLoaded', () => defineElement());
