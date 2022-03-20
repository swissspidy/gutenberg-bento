import { defineElement } from '@bentoproject/fit-text';

// Just so webpack generates a separate stylesheet for us to enqueue in WP.
import '@bentoproject/fit-text/styles.css';

if (
	document.readyState === 'complete' ||
	document.readyState === 'interactive'
) {
	defineElement();
}

document.addEventListener('DOMContentLoaded', () => defineElement());
