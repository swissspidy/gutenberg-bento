# Gutenberg ❤️ Bento

An exploratory plugin for using [Bento components](https://amp.dev/documentation/guides-and-tutorials/start/bento_guide/) in [Gutenberg](https://github.com/WordPress/gutenberg).

## Background

Bento AMP offers well-tested, cross-browser compatible and accessible components that can be used on non-AMP pages without having to use AMP anywhere else.
Bento components are designed to be highly performant and contribute to an excellent page experience.

These components are not only available as custom elements, but also as React and Preact components with the same features and API.
That makes them an ideal candidate for use in the React-based Gutenberg editor.

Typically, with Gutenberg one has to write a block's Edit component in React and then replicate the same look and feel for the frontend without React, causing a lot of duplicate work.
With Bento this is no longer a problem.

## About this Plugin

This demo plugin shows how `<amp-base-carousel>` can be used to display an image carousel on the frontend, with the `BaseCarousel` React component doing the same in the editor,
removing the need for any duplication.

Thanks to the encapsulation advantages of custom elements like `<amp-base-carousel>`, other WordPress plugins and themes can't interfere with its look & feel.

While this plugin is only a proof-of-concept, it gives a glimpse at the possibilities of using Bento for Gutenberg block development, and the advantages that brings:

1. Great user experience and page experience
2. Reduced development and maintenance costs
3. Ensured feature parity between editor and frontend
4. No interference by other plugins and themes thanks to web components.

### AMP-first Sites

An added bonus of the `<amp-base-carousel>` component is that it can be used on an AMP-first site with very little work.

When using the official [AMP WordPress plugin](https://wordpress.org/plugins/amp/), all that's needed is to stop enqueuing the custom JavaScript & CSS for the component
and use `amp-bind` (`on=""`) where custom functionality like going to the next/previous slide is needed.

### File Structure

* `edit.js`: only used in the editor.
* `edit.css`: only used in the editor
* `carousel.view.js`: only used on the frontend.
* `view.css`: used both on the frontend and in the editor.

## Screenshots

The `<BaseCarousel>` carousel component in the editor:

![Carousel in the editor](https://user-images.githubusercontent.com/841956/127545477-478adba4-c8e1-4a69-b3da-a58dabf375a7.png)

The same carousel powered by `<amp-base-carousel>` on the frontend:

![Carousel on the frontend](https://user-images.githubusercontent.com/841956/127545504-9fa725b6-a52f-43c1-9da6-af4f4b0a9c69.png)

## Getting Started

1. Clone repository into `wp-content/plugins/gutenberg-bento` of your WordPress site.
2. Run `npm install`
3. Run `composer install`
4. Run `npm run build`
5. Go to your WordPress site and activate this plugin.

To set up WordPress locally, you can use something like [Local](https://localwp.com/).

## Known Issues

* React Fragments broken (ampproject/amphtml#35412) (**fixed**)
* React warnings about incorrect DOM attributes / unrecognized props (ampproject/amphtml#35553)
* Cannot use loop as boolean attribute for amp-base-carousel (ampproject/amphtml#35555)
* npm packages not shipping with CSS (ampproject/amphtml#35413)
* Lack of type definitions (ampproject/amphtml#34206)
* Lack of changelog / documentation
* Custom element versions not shipped via npm (ampproject/amphtml#35554)

## Notes

### Package Exports

A Bento React component can be imported as follows:

```js
import { BaseCarousel } from '@ampproject/amp-base-carousel/react';
```

The component's `package.json` file has `exports` entries for both `import` (ESM) and `require` (CommonJS).
This is supported by modern build tooling such as webpack v5.

At the same time, the package contains a `react.js` file pointing to the CommonJS version.
This is done to make the above import work in older build tooling software such as webpack v4.

So with webpack v4, the above import actually references the `react.js` file and ends up importing the CommonJS version.

Unfortunately, the [`@wordpress/scripts`](https://npmjs.com/package/@wordpress/scripts) utility still uses webpack v4,
which is why such workarounds like `react.js` are still necessary.

As soon as the ecosystem begins to upgrade, the benefits of `exports` and ESM imports can be fully leveraged even in WordPress land.
