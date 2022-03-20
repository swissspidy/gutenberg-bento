/**
 * External dependencies
 */
import { pick, get } from 'lodash';

export const pickRelevantMediaFiles = (image, sizeSlug = 'large') => {
	const imageProps = pick(image, ['alt', 'id', 'link', 'caption']);
	imageProps.url =
		get(image, ['sizes', sizeSlug, 'url']) ||
		get(image, ['media_details', 'sizes', sizeSlug, 'source_url']) ||
		image.url;
	const fullUrl =
		get(image, ['sizes', 'full', 'url']) ||
		get(image, ['media_details', 'sizes', 'full', 'source_url']);
	if (fullUrl) {
		imageProps.fullUrl = fullUrl;
	}
	return imageProps;
};

const ALLOWED_MEDIA_TYPES = ['image'];

export function isValidFileType(file) {
	return (
		ALLOWED_MEDIA_TYPES.some(
			(mediaType) => file.type?.indexOf(mediaType) === 0
		) || file.url?.indexOf('blob:') === 0
	);
}
