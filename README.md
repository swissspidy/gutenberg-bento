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

## Known Issues / Notes / Questions

### Using Module Version for React

A Bento component's `package.json` file has `exports` entries for both `import` (ESM) and `require` (CommonJS).
At the same time, the package contains a `react.js` file pointing to the CommonJS version.

With this constellation, I haven't found a way to actually import the ESM version.

So when importing the component like so, it will actually reference the `react.js` file and end up importing the CommonJS version:

```js
import { BaseCarousel } from '@ampproject/amp-base-carousel/react';
```

Aside: how can the `.max` version (i.e. the unminified version) be imported? Or what is it used for?

### Lack of Type Definitions

[GitHub issue](https://github.com/ampproject/amphtml/issues/34206)

Having some type definitions or even just keeping inline documentation in the npm package would help improve developer experience. 

### Missing CSS

[GitHub issue](https://github.com/ampproject/amphtml/issues/35413)

Bento uses [JSS](https://cssinjs.org/) for stylesheets, but the compiled React components only contain the class names, not the actual CSS.

As a workaround, the repository contains some manually copied CSS for use with the React component.

### React Fragments broken

Originally reported in this [GitHub issue](https://github.com/ampproject/amphtml/issues/35412), this bug has since been fixed. 🎉
