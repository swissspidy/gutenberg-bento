import { defineElement } from '@ampproject/amp-base-carousel/web-component';

// Just so webpack generates a separate stylesheet for us to enqueue in WP.
import '@ampproject/amp-base-carousel/styles.css';

if (
	document.readyState === 'complete' ||
	document.readyState === 'interactive'
) {
	defineElement();
}

document.addEventListener('DOMContentLoaded', () => defineElement());
