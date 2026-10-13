=== Interior Specifics ===
Contributors: interiorspecifics
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Bespoke luxury theme for Interior Specifics: editorial typography, scroll-told storytelling and custom blocks for every section.

== Description ==

A classic PHP theme with custom blocks for the Interior Specifics website (Lagos, Nigeria). It pairs with the
Interior Specifics Core plugin, which owns the content model (services, showroom projects, testimonials, leads),
the site settings, lead capture and structured data. Without the plugin the theme still renders, but business
details, forms and the custom content types are unavailable.

Built assets live in `build/`. To rebuild them, run `bash bin/build-theme.sh` from the `wordpress/` folder of the
project repository (Tailwind CSS v4 + @wordpress/scripts).

== Installation ==

1. Upload the `interior-specifics` folder to `wp-content/themes/` (or upload the zip under Appearance → Themes → Add New).
2. Install and activate the Interior Specifics Core plugin.
3. Activate the theme under Appearance → Themes.
4. Set business details and page hero images under Settings → Interior Specifics.

== Changelog ==

= 1.0.0 =
* Initial release: port of the Interior Specifics Next.js site to WordPress.

== Copyright ==

Interior Specifics WordPress Theme, Copyright 2026 Interior Specifics Ltd.
Interior Specifics is distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

== Resources ==

This theme bundles the following third-party resources:

Cormorant (assets/fonts/cormorant-*.woff2)
Copyright 2015 The Cormorant Project Authors (https://github.com/CatharsisFonts/Cormorant)
License: SIL Open Font License, Version 1.1 (full text: assets/fonts/OFL.txt)
Source: https://fontsource.org/fonts/cormorant

Inter (assets/fonts/inter-latin-wght-normal.woff2)
Copyright 2016 The Inter Project Authors (https://github.com/rsms/inter)
License: SIL Open Font License, Version 1.1 (full text: assets/fonts/OFL.txt)
Source: https://fontsource.org/fonts/inter

Playfair Display (assets/fonts/playfair-display-latin-wght-normal.woff2)
Copyright 2017 The Playfair Display Project Authors (https://github.com/clauseggers/Playfair-Display), with Reserved Font Name "Playfair Display".
License: SIL Open Font License, Version 1.1 (full text: assets/fonts/OFL.txt)
Source: https://fontsource.org/fonts/playfair-display

Lenis 1.3 (bundled into build/js/main.js)
Copyright (c) 2024 darkroom.engineering
License: MIT, https://opensource.org/license/mit (full text: assets/licenses/lenis-LICENSE.txt)
Source: https://github.com/darkroomengineering/lenis

Images
The screenshot (screenshot.jpg) and the default images referenced by the blocks (src/blocks/*/block.json) are
photographs from Unsplash (https://unsplash.com), used under the Unsplash License
(https://unsplash.com/license). They are placeholders until the studio's own photography is added.

Icons (assets/icon.svg, assets/apple-touch-icon.png)
Copyright 2026 Interior Specifics Ltd. License: GPLv2 or later.
