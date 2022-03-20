# Gutenberg ❤️ Bento

An exploratory plugin for using [Bento components](https://amp.dev/documentation/guides-and-tutorials/start/bento_guide/) in [Gutenberg](https://github.com/WordPress/gutenberg).

## Background

Bento offers well-tested, cross-browser compatible and accessible web components (aka custom elements) that can be used on any website.
Bento components are highly performant and contribute to an excellent page experience. They power [AMP](https://amp.dev/) under the hood as well, but they can be used independent of AMP.

These components are not only available as custom elements, but also as React and Preact components with the same features and API.
That makes them an ideal candidate for use in the React-based Gutenberg editor.

Typically, with Gutenberg one has to write a block's Edit component in React and then replicate the same look and feel for the frontend without React. This causes a lot of duplicate work and additional maintenance burden.
With Bento this is no longer a problem.

## About this Plugin

This plugin enhances existing core blocks and creates new blocks for using Bento components.

Thanks to the encapsulation advantages of custom elements like `<bento-base-carousel>`, other WordPress plugins and themes can't interfere with their look & feel.

While this plugin is only a proof-of-concept, it gives a glimpse at the possibilities of using Bento for Gutenberg block development, and the advantages that brings:

1. Great user experience and page experience
2. Reduced development and maintenance costs
3. Ensured feature parity between editor and frontend
4. No interference by other plugins and themes, thanks to web components.

Specifically:

### Accordion Block

Uses [Bento Accordion](https://bentojs.dev/components/bento-accordion/) with `InnerBlocks`.

### Carousel Block

Uses [Bento Carousel](https://bentojs.dev/components/bento-carousel/) with `InnerBlocks`.

The `<BentoBaseCarousel>` carousel component in the editor:

![Carousel in the editor](https://user-images.githubusercontent.com/841956/127545477-478adba4-c8e1-4a69-b3da-a58dabf375a7.png)

The same carousel powered by `<bento-base-carousel>` on the frontend:

![Carousel on the frontend](https://user-images.githubusercontent.com/841956/127545504-9fa725b6-a52f-43c1-9da6-af4f4b0a9c69.png)

### Date Countdown Block

Uses [Bento Date Countdown](https://bentojs.dev/components/bento-date-countdown/) to displays a countdown sequence to a specified date.

![Countdown in the Editor](https://user-images.githubusercontent.com/841956/159172372-7b4db27f-5237-438f-b0db-1475bb5e6eb5.png)
![Countdown on the Frontend](https://user-images.githubusercontent.com/841956/159170942-831605af-12d0-4b85-9e18-700be4817009.png)

### Fit Text Block

### Lightbox Gallery

![Toggle in the Editor](https://user-images.githubusercontent.com/841956/159158387-f2da1f97-4859-448b-b112-896eb16ddb00.png)

[Demo video](https://user-images.githubusercontent.com/841956/159156776-345f13a5-ea05-4297-af3c-6b8acc50742a.mp4)

### AMP

Bento components build the foundation of [AMP](https://amp.dev/), but can be used standalone as well.

Because of that interoperability, an added bonus of components like `<bento-base-carousel>` is that they can be used on an AMP-first site with very little work.

When using the official [AMP WordPress plugin](https://wordpress.org/plugins/amp/), all that's needed is to stop enqueuing the custom JavaScript & CSS for the component
and use `amp-bind` (`on=""`) where custom functionality like going to the next/previous slide is needed.

This plugin makes use of exactly that to give you a better understanding of how this works.

## Getting Started

1. Clone repository into `wp-content/plugins/gutenberg-bento` of your WordPress site.
2. Run `npm install`
3. Run `composer install`
4. Run `npm run build`
5. Go to your WordPress site and activate this plugin.

To set up WordPress locally, you can use something like [Local](https://localwp.com/).

## Known Issues

* Recent versions of Bento React components being broken (ampproject/amphtml#37919)
* npm packages not shipping with CSS (ampproject/amphtml#35413)
* Lack of type definitions (ampproject/amphtml#34206)
* Lack of changelog / documentation

## Notes

### Bento CDN vs. Self-Hosting

One of the features of Bento is the use of the CDN for loading all JavaScript files and stylesheets.

However, in some cases one might not want to use a CDN.
If you prefer self-hosting the assets, you can use the `gutenberg_bento_self_host` filter to enqueue the scripts and styles bundled with the plugin.

**Note:** this is not fully implemented yet; the scripts for self-hosting are not currently being built correctly.

### Authors

This project was built during the [CloudFest 2022 Hackathon](https://www.cloudfest.com/hackathon) by the following contributors:

* [Alain Schlesser](https://github.com/schlessera)
* [Pascal Birchler](https://github.com/swissspidy)
* [Adam Zielinski](https://github.com/adamziel)
* [Marcel Schmitz](https://github.com/schmitzoide)
* [Greg Ziółkowski](https://github.com/gziolo)
* [Héctor Prieto](https://github.com/priethor)
* [Jessica Lyschik](https://github.com/luminuu)

It is based on an original early prototype built by [Pascal Birchler](https://github.com/swissspidy).
