/**
 * External dependencies
 */
import { concat } from 'lodash';
import { BentoBaseCarousel } from '@bentoproject/base-carousel/react';
import '@bentoproject/base-carousel/styles.css';

/**
 * WordPress dependencies
 */
import { useRef, render, useEffect, useState } from '@wordpress/element';
import {
	BlockControls,
	MediaReplaceFlow,
	useBlockProps,
	useInnerBlocksProps,
	store as blockEditorStore,
	InspectorControls,
} from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';
import { arrowLeft, arrowRight } from '@wordpress/icons';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	withNotices,
	ToggleControl,
	SelectControl,
	TextControl,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlobURL } from '@wordpress/blob';

/**
 * Internal dependencies
 */
import { isValidFileType, pickRelevantMediaFiles } from './helpers';
import './editor.css';

const ALLOWED_MEDIA_TYPES = ['image'];

function CarouselEdit(props) {
	const { attributes, setAttributes, clientId, noticeOperations } = props;
	const {
		autoAdvance,
		loop,
		snap,
		direction,
		orientation,
		controls,
		visibleCount,
		mixedLengths,
	} = attributes;

	const carouselRef = useRef();
	const innerBlocksProps = useInnerBlocksProps(
		{},
		{
			orientation,
			__experimentalBlocksListItemsWrapper: (
				<BentoBaseCarousel
					ref={carouselRef}
					dir={direction}
					orientation={orientation}
					controls={controls}
					autoAdvance={autoAdvance}
					visibleCount={visibleCount}
					mixedLengths={mixedLengths}
					loop={loop}
					snap={snap}
				/>
			),
		}
	);
	const innerBlocks = useSelect(
		(select) => {
			return select(blockEditorStore).getBlock(clientId)?.innerBlocks;
		},
		[clientId]
	);
	const [hasGutenbergPatch, setHasGutenbergPatch] = useState(null);
	useEffect(() => {
		const target = document.createElement('div');
		render(<div {...innerBlocksProps} />, target);
		setTimeout(() => {
			setHasGutenbergPatch(target.innerHTML.includes('class="carousel-'));
		});
	}, []);

	const hasImages = innerBlocks.length > 0;
	const imagesIds = hasImages
		? innerBlocks.map((image) => image?.attributes?.id)
		: [];
	const hasImageIds = imagesIds.some((id) => id);

	const { replaceInnerBlocks, selectBlock } = useDispatch(blockEditorStore);

	function updateImages(selectedImages) {
		const newFileUploads =
			Object.prototype.toString.call(selectedImages) ===
			'[object FileList]';

		const imageArray = newFileUploads
			? Array.from(selectedImages).map((file) => {
					if (!file.url) {
						return pickRelevantMediaFiles({
							url: createBlobURL(file),
						});
					}

					return file;
			  })
			: selectedImages;

		if (!imageArray.every(isValidFileType)) {
			noticeOperations.removeAllNotices();
			noticeOperations.createErrorNotice(
				__(
					'If uploading to a gallery all files need to be image formats',
					'gutenberg-bento'
				),
				{ id: 'gallery-upload-invalid-file' }
			);
		}

		const processedImages = imageArray
			.filter((file) => file.url || isValidFileType(file))
			.map((file) => {
				if (!file.url) {
					return pickRelevantMediaFiles({
						url: createBlobURL(file),
					});
				}

				return file;
			});

		// Because we are reusing existing innerImage blocks any reordering
		// done in the media library will be lost so we need to reapply that ordering
		// once the new image blocks are merged in with existing.
		const newOrderMap = processedImages.reduce(
			(result, image, index) => ((result[image.id] = index), result),
			{}
		);

		const existingImageBlocks = !newFileUploads
			? innerBlocks.filter((block) =>
					processedImages.find(
						(img) => img.id === block.attributes.id
					)
			  )
			: innerBlocks;

		const newImageList = processedImages.filter(
			(img) =>
				!existingImageBlocks.find(
					(existingImg) => img.id === existingImg.attributes.id
				)
		);

		const newBlocks = newImageList.map((image) => {
			return createBlock('core/image', {
				id: image.id,
				url: image.url,
				caption: image.caption,
				alt: image.alt,
			});
		});

		if (newBlocks?.length > 0) {
			selectBlock(newBlocks[0].clientId);
		}

		replaceInnerBlocks(
			clientId,
			concat(existingImageBlocks, newBlocks).sort(
				(a, b) =>
					newOrderMap[a.attributes.id] - newOrderMap[b.attributes.id]
			)
		);
	}

	function initTheBlock() {
		replaceInnerBlocks(clientId, [createBlock('core/cover', {})]);
	}

	const goToNextSlide = () => {
		if (carouselRef.current) {
			carouselRef.current.next();
		}
	};

	const goToPreviousSlide = () => {
		if (carouselRef.current) {
			carouselRef.current.prev();
		}
	};

	const blockProps = useBlockProps();

	if (hasGutenbergPatch === false) {
		return (
			<div className="components-placeholder block-editor-media-placeholder is-large">
				<div className="components-placeholder__label">
					{__('Bento Carousel', 'gutenberg-bento')}
				</div>
				<fieldset className="components-placeholder__fieldset">
					<legend className="components-placeholder__instructions">
						{__(
							'Using this component requires a patched Gutenberg version. Please ' +
								'apply the following PR and retry: ',
							'gutenberg-bento'
						)}
						<br />
						<a
							target="_blank"
							rel="noopener noreferrer"
							href="https://github.com/WordPress/gutenberg/pull/39597"
						>
							https://github.com/WordPress/gutenberg/pull/39597
						</a>
					</legend>
				</fieldset>
			</div>
		);
	}

	if (!innerBlocks.length) {
		return (
			<div className="components-placeholder block-editor-media-placeholder is-large">
				<div className="components-placeholder__label">
					{__('Bento Carousel', 'gutenberg-bento')}
				</div>
				<fieldset className="components-placeholder__fieldset">
					<legend className="components-placeholder__instructions">
						{__(
							'Build your super cool carousel.',
							'gutenberg-bento'
						)}
					</legend>
					<div className="components-form-file-upload">
						<button
							type="button"
							onClick={initTheBlock}
							className="components-button block-editor-media-placeholder__button block-editor-media-placeholder__upload-button is-primary"
						>
							Start with a cover block
						</button>
					</div>
				</fieldset>
			</div>
		);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Carousel Settings', 'gutenberg-bento')}>
					<ToggleControl
						label={__('Auto Advance', 'gutenberg-bento')}
						checked={!!autoAdvance}
						onChange={() =>
							setAttributes({ autoAdvance: !autoAdvance })
						}
					/>
					<ToggleControl
						label={__('Loop', 'gutenberg-bento')}
						checked={!!loop}
						onChange={() => setAttributes({ loop: !loop })}
					/>
					<ToggleControl
						label={__('Snap', 'gutenberg-bento')}
						checked={!!snap}
						onChange={() => setAttributes({ snap: !snap })}
					/>
					<ToggleControl
						label={__('Mixed lengths', 'gutenberg-bento')}
						checked={!!mixedLengths}
						onChange={() =>
							setAttributes({ mixedLengths: !mixedLengths })
						}
					/>
					<SelectControl
						label={__('Direction', 'gutenberg-bento')}
						value={direction}
						// `undefined` is required for the preload attribute to be unset.
						onChange={(value) => {
							setAttributes({
								direction: value,
							});
						}}
						options={[
							{
								value: 'ltr',
								label: __('Left to right', 'gutenberg-bento'),
							},
							{
								value: 'rtl',
								label: __('Right to left', 'gutenberg-bento'),
							},
						]}
					/>
					<SelectControl
						label={__('Orientation', 'gutenberg-bento')}
						value={orientation}
						// `undefined` is required for the preload attribute to be unset.
						onChange={(value) =>
							setAttributes({
								orientation: value,
							})
						}
						options={[
							{
								value: 'horizontal',
								label: __('Horizontal', 'gutenberg-bento'),
							},
							{
								value: 'vertical',
								label: __('Vertical', 'gutenberg-bento'),
							},
						]}
					/>
					<SelectControl
						label={__('Controls', 'gutenberg-bento')}
						value={controls}
						// `undefined` is required for the preload attribute to be unset.
						onChange={(value) =>
							setAttributes({
								controls: value,
							})
						}
						options={[
							{
								value: 'always',
								label: __('Always', 'gutenberg-bento'),
							},
							{
								value: 'auto',
								label: __('Auto', 'gutenberg-bento'),
							},
							{
								value: 'never',
								label: __('Never', 'gutenberg-bento'),
							},
						]}
					/>
					<TextControl
						label={__('Visible slides', 'gutenberg-bento')}
						type="number"
						onChange={(value) => {
							const int = parseInt(value, 10);

							setAttributes({
								// It should be possible to unset the value,
								// e.g. with an empty string.
								visibleCount: isNaN(int) ? 1 : int,
							});
						}}
						value={visibleCount}
						step="1"
					/>
				</PanelBody>
			</InspectorControls>
			<BlockControls group="other">
				<MediaReplaceFlow
					allowedTypes={ALLOWED_MEDIA_TYPES}
					accept="image/*"
					handleUpload={false}
					onSelect={updateImages}
					name={__('Add images', 'gutenberg-bento')}
					multiple={true}
					mediaIds={imagesIds}
					addToGallery={hasImageIds}
				/>
				<ToolbarGroup>
					<ToolbarButton
						title={__('Previous slide', 'gutenberg-bento')}
						icon={arrowLeft}
						onClick={goToPreviousSlide}
					/>
					<ToolbarButton
						title={__('Next slide', 'gutenberg-bento')}
						icon={arrowRight}
						onClick={goToNextSlide}
					/>
				</ToolbarGroup>
			</BlockControls>
			<figure {...blockProps}>
				<div className="gutenberg-bento-carousel-wrapper">
					<div {...innerBlocksProps} />
				</div>
			</figure>
		</>
	);
}

export default compose([withNotices])(CarouselEdit);
