/* This JS is only used on the frontend. */

import './view.css';

(async () => {
	await window.customElements.whenDefined('bento-base-carousel');
	const carousels = document.querySelectorAll(
		'.wp-block-gutenberg-bento-carousel'
	);

	for (const carousel of carousels) {
		const bentoComponent = carousel.querySelector('bento-base-carousel');
		const api = await bentoComponent.getApi();

		carousel
			.querySelector('.gutenberg-bento-carousel-buttons__prev')
			.addEventListener('click', () => {
				api.prev();
			});
		carousel
			.querySelector('.gutenberg-bento-carousel-buttons__next')
			.addEventListener('click', () => {
				api.next();
			});
	}
})();
