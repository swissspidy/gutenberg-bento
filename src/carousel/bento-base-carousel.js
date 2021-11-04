import { defineElement } from '@bentoproject/base-carousel/web-component';

// Just so webpack generates a separate stylesheet for us to enqueue in WP.
import '@bentoproject/base-carousel/styles.css';

if (
	document.readyState === 'complete' ||
	document.readyState === 'interactive'
) {
	defineElement();
}

document.addEventListener('DOMContentLoaded', () => defineElement());
