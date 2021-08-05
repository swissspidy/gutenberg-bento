/**
 * WordPress dependencies
 */
import { RichText, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

export default function save( { attributes } ) {
	const { images, autoAdvance, loop, snap } = attributes;

	const className = 'gutenberg-bento-carousel-wrapper';

	return (
		<figure { ...useBlockProps.save() }>
			<amp-base-carousel
				className={ className }
				auto-advance={ autoAdvance ? 'true' : undefined }
				loop={ loop ? 'true' : undefined }
				snap={ snap ? 'true' : undefined }
			>
				{ images.map( ( image ) => {
					let href;

					const img = (
						<img
							src={ image.url }
							alt={ image.alt }
							data-id={ image.id }
							data-full-url={ image.fullUrl }
							data-link={ image.link }
							className={
								image.id ? `wp-image-${ image.id }` : null
							}
						/>
					);

					return (
						<div
							key={ image.id || image.url }
							className="gutenberg-bento-carousel-item"
						>
							<figure>
								{ href ? <a href={ href }>{ img }</a> : img }
								{ ! RichText.isEmpty( image.caption ) && (
									<RichText.Content
										tagName="figcaption"
										className="gutenberg-bento-carousel-item__caption"
										value={ image.caption }
									/>
								) }
							</figure>
						</div>
					);
				} ) }
			</amp-base-carousel>
			<div className="gutenberg-bento-carousel-buttons">
				<button className="gutenberg-bento-carousel-buttons__prev">
					{ __( 'Previous', 'gutenberg-bento' ) }
				</button>
				<button className="gutenberg-bento-carousel-buttons__next">
					{ __( 'Next', 'gutenberg-bento' ) }
				</button>
			</div>
		</figure>
	);
}
