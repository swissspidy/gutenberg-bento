/* This JS is only used on the frontend. */

import './view.css';

// Bento components are available experimentally.
( window.AMP = window.AMP || [] ).push( ( AMP ) => {
	AMP.toggleExperiment( 'bento', true );
} );

const carousels = document.querySelectorAll(
	'.gutenberg-bento-carousel-wrapper'
);

carousels.forEach( async ( carousel ) => {
	const bentoComponent = carousel.querySelector( 'amp-base-carousel' );
	await window.customElements.whenDefined( 'amp-base-carousel' );
	const api = await bentoComponent.getApi();

	carousel
		.querySelector( '.gutenberg-bento-carousel-buttons__prev' )
		.addEventListener( 'click', () => {
			api.prev();
		} );
	carousel
		.querySelector( '.gutenberg-bento-carousel-buttons__next' )
		.addEventListener( 'click', () => {
			api.next();
		} );
} );
