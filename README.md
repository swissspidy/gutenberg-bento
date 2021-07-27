# Gutenberg ❤️ Bento

An exploratory plugin for using [Bento components](https://amp.dev/documentation/guides-and-tutorials/start/bento_guide/) in [Gutenberg](https://github.com/WordPress/gutenberg).

## Getting Started

1. Clone repository into `wp-content/plugins/gutenberg-bento` of your WordPress site.
2. Run `npm install`
3. Run `composer install`
4. Run `npm run build`
5. Go to your WordPress site and activate this plugin.

To set up WordPress locally, you can use something like [Local](https://localwp.com/).

## How it Works

Bento AMP offers well-tested, cross-browser compatible and accessible components that can be used on non-AMP pages without having to use AMP anywhere else.
Bento components are designed to be highly performant and contribute to an excellent page experience.

These components are not only available as custom elements, but also as React and Preact components with the same features and API.
That makes them an ideal candidate for use in the React-based Gutenberg editor.

Typically, with Gutenberg one has to write a block's Edit component in React and then replicate the same look and feel for the frontend without React, causing a lot of duplicate work.
With Bento this is no longer a problem.

This demo plugin shows how `<amp-base-carousel>` can be used to display an image carousel on the frontend, with the `BaseCarousel` React component doing the same in the editor,
removing the need for any duplication.

Thanks to the encapsulation advantages of custom elements like `<amp-base-carousel>`, other WordPress plugins and themes can't interfere with its look & feel.

## Known Issues / Notes

### Using Module Version for React

While a Bento component's `package.json` file has `exports` entries for both `import` (ESM) and `require` (CommonJS), it looks like most tools don't support that yet.

So when importing the component like so, it will actually reference the `react.js` file within the package, which points to the CommonJS version:

```js
import { BaseCarousel } from '@ampproject/amp-base-carousel/react';
```

Aside: how can the `.max` version be imported?

### Lack of Type Definitions

[GitHub issue](https://github.com/ampproject/amphtml/issues/34206)

Having some type definitions or even just keeping inline documentation in the npm package would help improve developer experience. 

### Missing CSS

[GitHub issue](https://github.com/ampproject/amphtml/issues/35413)

Bento uses [JSS](https://cssinjs.org/) for stylesheets, but the compiled React components only contain the class names, not the actual CSS.

As a workaround, the repository contains some manually copied CSS for use with the React component.

### React Fragments broken

[GitHub issue](https://github.com/ampproject/amphtml/issues/35412)

AMP defines the type interfaces for the Preact APIs [like so](https://github.com/ampproject/amphtml/blob/d4c1cebb15b09bb607a36b0724c16bb5e2ab11eb/src/preact/index.js#L25-L68):

```js
function createElement(unusedType, unusedProps, var_args) {
	return preact.createElement.apply(undefined, arguments);
}

// ...

function Fragment(props) {
  return preact.Fragment(props);
}
```

While `Fragment` is a function in Preact, it isn't in _React_. This causes JavaScript errors when running the application, saying that `preact.Fragment` is not a function.

The compiled Bento React components contain code like this (with `preact` actually pointing to the `react` package):

```js

function createElement2(unusedType, unusedProps, var_args) {
	return preact.createElement.apply(void 0, arguments);
}

// ...

function Fragment2(props) {
  // TypeError: preact.Fragment is not a function
  return preact.Fragment(props);
}
```

To fix this, the correct way to do this would be:

```js
function Fragment2(props) {
  return createElement2(preact.Fragment, props);
}
```
