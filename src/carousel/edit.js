import { BentoBaseCarousel } from '@bentoproject/base-carousel/react';
import '@bentoproject/base-carousel/styles.css';

window.BentoBaseCarousel = BentoBaseCarousel;

import { concat, zip } from 'lodash';
import {
	useEffect,
	useCallback,
	useRef,
	cloneElement,
	Children,
	Fragment,
} from '@wordpress/element';
import {
	BlockControls,
	MediaReplaceFlow,
	MediaPlaceholder,
	useBlockProps,
	useInnerBlocksProps,
	store as blockEditorStore,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';
import { compose } from '@wordpress/compose';
import { __, isRTL } from '@wordpress/i18n';
import {
	Button, PanelBody,
	withNotices, ToggleControl,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { View } from '@wordpress/primitives';
import { createBlobURL, getBlobByURL, isBlobURL, revokeBlobURL } from '@wordpress/blob';
import { isValidFileType, pickRelevantMediaFiles } from "./helpers";

const ALLOWED_MEDIA_TYPES = ['image'];

const PLACEHOLDER_TEXT = __(
	'Drag images, upload new ones or select files from your library.',
	'gutenberg-bento',
);

function CarouselEdit( props ) {
	const { attributes, clientId, isSelected, noticeUI, onFocus, noticeOperations } = props;
	const { images, autoAdvance, loop, snap } = attributes;

	const { mediaUpload, wasBlockJustInserted } = useSelect( ( select ) => {
		const settings = select( blockEditorStore ).getSettings();

		return {
			mediaUpload: settings.mediaUpload,
			wasBlockJustInserted: select( blockEditorStore ).wasBlockJustInserted(
				clientId,
				'inserter_menu',
			),
		};
	} );

	function setAttributes( newAttrs ) {
		if ( newAttrs.ids ) {
			throw new Error(
				'The "ids" attribute should not be changed directly. It is managed automatically when "images" attribute changes',
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

	const innerBlocksProps = useInnerBlocksProps(
		{
			myTestProp2: 123,
		},
		{
			allowedBlocks: ['core/image'],
			orientation: 'horizontal',
			renderAppender: false,
			myTestProp: 123,
			__experimentalAppenderTagName: 'my-bento-hack',
			__experimentalLayout: { type: 'flex', alignments: [] },
		},
	);

	function toggleAutoAdvance() {
		setAttributes( { autoAdvance: !autoAdvance } );
	}

	function toggleLoop() {
		setAttributes( { loop: !loop } );
	}

	function toggleSnap() {
		setAttributes( { snap: !snap } );
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
	const hasImageIds = hasImages && images.some( ( image ) => !!image.id );

	useEffect( () => {
		if ( hasImages && images.every( ( { url } ) => isBlobURL( url ) ) ) {
			const filesList = images.map( ( { url } ) => getBlobByURL( url ) );
			images.forEach( ( { url } ) => revokeBlobURL( url ) );
			mediaUpload( {
				filesList,
				onFileChange: onSelectImages,
				allowedTypes: ['image'],
			} );
		}
	}, [] );

	const mediaPlaceholder = (
		<MediaPlaceholder
			addToGallery={ hasImageIds }
			isAppender={ hasImages }
			disableMediaButtons={ hasImages && !isSelected }
			labels={ {
				title: !hasImages && __( 'Carousel', 'gutenberg-bento' ),
				instructions: !hasImages && PLACEHOLDER_TEXT,
			} }
			onSelect={ onSelectImages }
			accept="image/*"
			allowedTypes={ ALLOWED_MEDIA_TYPES }
			multiple
			value={ hasImageIds ? images : {} }
			notices={ hasImages ? undefined : noticeUI }
			onFocus={ onFocus }
			autoOpenMediaUpload={
				!hasImages && isSelected && wasBlockJustInserted
			}
		/>
	);

	const innerBlockImages = useSelect(
		( select ) => {
			return select( blockEditorStore ).getBlock( clientId )?.innerBlocks;
		},
		[clientId],
	);
	console.log( innerBlockImages );

	const {
		replaceInnerBlocks,
		selectBlock,
	} = useDispatch( blockEditorStore );

	function updateImages( selectedImages ) {
		const newFileUploads =
			Object.prototype.toString.call( selectedImages ) ===
			'[object FileList]';

		const imageArray = newFileUploads
			? Array.from( selectedImages ).map( ( file ) => {
				if ( !file.url ) {
					return pickRelevantMediaFiles( {
						url: createBlobURL( file ),
					} );
				}

				return file;
			} )
			: selectedImages;

		if ( !imageArray.every( isValidFileType ) ) {
			noticeOperations.removeAllNotices();
			noticeOperations.createErrorNotice(
				__(
					'If uploading to a gallery all files need to be image formats',
				),
				{ id: 'gallery-upload-invalid-file' },
			);
		}

		const processedImages = imageArray
			.filter( ( file ) => file.url || isValidFileType( file ) )
			.map( ( file ) => {
				if ( !file.url ) {
					return pickRelevantMediaFiles( {
						url: createBlobURL( file ),
					} );
				}

				return file;
			} );

		// Because we are reusing existing innerImage blocks any reordering
		// done in the media library will be lost so we need to reapply that ordering
		// once the new image blocks are merged in with existing.
		const newOrderMap = processedImages.reduce(
			( result, image, index ) => (
				( result[ image.id ] = index ), result
			),
			{},
		);

		const existingImageBlocks = !newFileUploads
			? innerBlockImages.filter( ( block ) =>
				processedImages.find(
					( img ) => img.id === block.attributes.id,
				),
			)
			: innerBlockImages;

		const newImageList = processedImages.filter(
			( img ) =>
				!existingImageBlocks.find(
					( existingImg ) => img.id === existingImg.attributes.id,
				),
		);

		const newBlocks = newImageList.map( ( image ) => {
			return createBlock( 'core/image', {
				id: image.id,
				url: image.url,
				caption: image.caption,
				alt: image.alt,
			} );
		} );

		if ( newBlocks?.length > 0 ) {
			selectBlock( newBlocks[ 0 ].clientId );
		}

		replaceInnerBlocks(
			clientId,
			concat( existingImageBlocks, newBlocks ).sort(
				( a, b ) =>
					newOrderMap[ a.attributes.id ] -
					newOrderMap[ b.attributes.id ],
			),
		);
	}

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

	if ( !hasImages ) {
		return <View { ...blockProps }>{ mediaPlaceholder }</View>;
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Carousel Settings', 'gutenberg-bento' ) }>
					<ToggleControl
						label={ __( 'Auto Advance', 'gutenberg-bento' ) }
						checked={ !!autoAdvance }
						onChange={ toggleAutoAdvance }
					/>
					<ToggleControl
						label={ __( 'Loop', 'gutenberg-bento' ) }
						checked={ !!loop }
						onChange={ toggleLoop }
					/>
					<ToggleControl
						label={ __( 'Snap', 'gutenberg-bento' ) }
						checked={ !!snap }
						onChange={ toggleSnap }
					/>
				</PanelBody>
			</InspectorControls>
			<BlockControls group="other">
				<MediaReplaceFlow
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					accept="image/*"
					handleUpload={ false }
					onSelect={ updateImages }
					name={ __( 'Add' ) }
					multiple={ true }
					mediaIds={ images.map( ( image ) => image.id ) }
					addToGallery={ hasImageIds }
				/>
			</BlockControls>
			<figure { ...blockProps }>
				<div className="gutenberg-bento-carousel-wrapper">
					<div {...innerBlocksProps} />
						{/*{innerBlocksProps.children}*/}
						{/*<Fragment { ...innerBlocksProps } />*/}
						{ /*images.map( ( image ) => {
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
										{ !RichText.isEmpty( image.caption ) && (
											<RichText.Content
												tagName="figcaption"
												className="gutenberg-bento-carousel-item__caption"
												value={ image.caption }
											/>
										) }
									</figure>
								</div>
							);
						} ) */ }
					{/*</BentoBaseCarousel>*/}
				</div>
				<div className="gutenberg-bento-carousel-buttons">
					<Button
						isSecondary
						className="gutenberg-bento-carousel-buttons__prev"
						onClick={ goToPreviousSlide }
					>
						{ __( 'Previous', 'gutenberg-bento' ) }
					</Button>
					<Button
						isSecondary
						className="gutenberg-bento-carousel-buttons__next"
						onClick={ goToNextSlide }
					>
						{ __( 'Next', 'gutenberg-bento' ) }
					</Button>
				</div>
			</figure>
		</>
	);
}

export default compose( [
	withNotices,
] )( CarouselEdit );

