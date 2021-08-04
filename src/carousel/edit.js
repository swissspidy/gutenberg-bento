import { BaseCarousel } from '@ampproject/amp-base-carousel/react';

import { useEffect, useCallback, useRef } from '@wordpress/element';
import {
	MediaPlaceholder,
	useBlockProps,
	store as blockEditorStore,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { __, isRTL } from '@wordpress/i18n';
import { Button, PanelBody, ToggleControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { View } from '@wordpress/primitives';
import { getBlobByURL, isBlobURL, revokeBlobURL } from '@wordpress/blob';

import './edit.css';

const ALLOWED_MEDIA_TYPES = [ 'image' ];

const PLACEHOLDER_TEXT = __(
	'Drag images, upload new ones or select files from your library.',
	'gutenberg-bento'
);

function CarouselEdit( props ) {
	const { attributes, clientId, isSelected, noticeUI, onFocus } = props;
	const { images, autoAdvance, loop, snap } = attributes;

	const { mediaUpload, wasBlockJustInserted } = useSelect( ( select ) => {
		const settings = select( blockEditorStore ).getSettings();

		return {
			mediaUpload: settings.mediaUpload,
			wasBlockJustInserted: select(
				blockEditorStore
			).wasBlockJustInserted( clientId, 'inserter_menu' ),
		};
	} );

	function setAttributes( newAttrs ) {
		if ( newAttrs.ids ) {
			throw new Error(
				'The "ids" attribute should not be changed directly. It is managed automatically when "images" attribute changes'
			);
		}

		if ( newAttrs.images ) {
			newAttrs = {
				...newAttrs,
				// Unlike images[ n ].id which is a string, always ensure the
				// ids array contains numbers as per its attribute type.
				ids: newAttrs.images.map( ( { id } ) => parseInt( id ) ),
			};
		}

		props.setAttributes( newAttrs );
	}

	function toggleAutoAdvance() {
		setAttributes( { autoAdvance: ! autoAdvance } );
	}

	function toggleLoop() {
		setAttributes( { loop: ! loop } );
	}

	function toggleSnap() {
		setAttributes( { snap: ! snap } );
	}

	const onSelectImages = useCallback( ( newImages ) => {
		setAttributes( {
			images: newImages.map( ( newImage ) => ( {
				alt: newImage.alt,
				link: newImage.link,
				caption: newImage.caption,
				url:
					newImage?.sizes?.large?.url ||
					newImage?.media_details?.sizes?.large?.source_url ||
					newImage.url,
				fullUrl:
					newImage?.sizes?.full?.url ||
					newImage?.media_details?.sizes?.full?.source_url ||
					undefined,
				id: newImage.id.toString(),
			} ) ),
		} );
	}, [] );

	const hasImages = images.length > 0;
	const hasImageIds = hasImages && images.some( ( image ) => !! image.id );

	useEffect( () => {
		if ( hasImages && images.every( ( { url } ) => isBlobURL( url ) ) ) {
			const filesList = images.map( ( { url } ) => getBlobByURL( url ) );
			images.forEach( ( { url } ) => revokeBlobURL( url ) );
			mediaUpload( {
				filesList,
				onFileChange: onSelectImages,
				allowedTypes: [ 'image' ],
			} );
		}
	}, [] );

	const mediaPlaceholder = (
		<MediaPlaceholder
			addToGallery={ hasImageIds }
			isAppender={ hasImages }
			disableMediaButtons={ hasImages && ! isSelected }
			labels={ {
				title: ! hasImages && __( 'Carousel', 'gutenberg-bento' ),
				instructions: ! hasImages && PLACEHOLDER_TEXT,
			} }
			onSelect={ onSelectImages }
			accept="image/*"
			allowedTypes={ ALLOWED_MEDIA_TYPES }
			multiple
			value={ hasImageIds ? images : {} }
			notices={ hasImages ? undefined : noticeUI }
			onFocus={ onFocus }
			autoOpenMediaUpload={
				! hasImages && isSelected && wasBlockJustInserted
			}
		/>
	);

	const carouselRef = useRef();

	const goToNextSlide = () => {
		if ( carouselRef.current ) {
			carouselRef.current.next();
		}
	};

	const goToPreviousSlide = () => {
		if ( carouselRef.current ) {
			carouselRef.current.prev();
		}
	};

	const blockProps = useBlockProps();

	if ( ! hasImages ) {
		return <View { ...blockProps }>{ mediaPlaceholder }</View>;
	}

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Carousel Settings', 'gutenberg-bento' ) }
				>
					<ToggleControl
						label={ __( 'Auto Advance', 'gutenberg-bento' ) }
						checked={ !! autoAdvance }
						onChange={ toggleAutoAdvance }
					/>
					<ToggleControl
						label={ __( 'Loop', 'gutenberg-bento' ) }
						checked={ !! loop }
						onChange={ toggleLoop }
					/>
					<ToggleControl
						label={ __( 'Snap', 'gutenberg-bento' ) }
						checked={ !! snap }
						onChange={ toggleSnap }
					/>
				</PanelBody>
			</InspectorControls>
			<figure { ...blockProps }>
				<BaseCarousel
					ref={ carouselRef }
					className="gutenberg-bento-carousel-wrapper"
					dir={ isRTL() ? 'rtl' : 'ltr' }
					autoAdvance={ autoAdvance }
					loop={ loop }
					snap={ snap }
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
									{ href ? (
										<a href={ href }>{ img }</a>
									) : (
										img
									) }
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
				</BaseCarousel>
				<div className="gutenberg-bento-carousel-buttons">
					<Button
						isSecondary
						className="gutenberg-bento-carousel-buttons__prev"
						onClick={ goToPreviousSlide }
					>
						Previous
					</Button>
					<Button
						isSecondary
						className="gutenberg-bento-carousel-buttons__next"
						onClick={ goToNextSlide }
					>
						Next
					</Button>
				</div>
			</figure>
		</>
	);
}

export default CarouselEdit;
