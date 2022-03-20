import { defineElement } from '@bentoproject/lightbox-gallery';

// Just so webpack generates a separate stylesheet for us to enqueue in WP.
import '@bentoproject/lightbox-gallery/styles.css';

if (
	document.readyState === 'complete' ||
	document.readyState === 'interactive'
) {
	defineElement();
}

document.addEventListener('DOMContentLoaded', () => defineElement());
