=== Interior Specifics Core ===
Contributors: interiorspecifics
Tags: interior design, portfolio, leads
Requires at least: 6.6
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Content model, site settings, lead capture, structured data and seeding for the Interior Specifics website.

== Description ==

Keeps the site's content independent of the theme:

* Content types: Services, Showroom projects (with project types and scope), Testimonials and Leads.
* Settings → Interior Specifics: business details, headline stats, booking and integrations, social profiles,
  page hero images and lead delivery.
* Lead capture for the contact, booking, newsletter and guide forms, stored under Leads and emailed through
  Resend (when the `RESEND_API_KEY` constant is defined) or the server mailer.
* SEO: titles, descriptions, social tags, sitemaps and JSON-LD, or integration with Yoast SEO when it is active.
* Block-editor sidebar for the custom fields.
* Site-wide hardening: comments, pingbacks and XML-RPC are switched off; the users REST routes and author
  archives are hidden from visitors.
* Media: JPEG and PNG sub-sizes are generated as WebP when the server supports it.
* WP-CLI: `wp is seed` imports the original site's content, and `wp is seo-defaults` applies the SEO defaults.

== Installation ==

1. Upload the `interior-specifics-core` folder to `wp-content/plugins/` and activate it.
2. Fill in Settings → Interior Specifics.
3. Optional: run `wp is seed --dir=/path/to/seed` to import the starter content, and `wp media regenerate --yes`
   once if images were uploaded before the plugin was active (to create the WebP sub-sizes).

== Uninstall ==

Deleting the plugin from the Plugins screen removes only its own settings (the `isp_settings` option and any
other `isp_*` option), its rate-limit transients and any scheduled `isp_*` events.

All content is deliberately kept: leads (enquiries), services, showroom projects, testimonials, their custom
fields and the project taxonomies stay in the database. This means an accidental delete or a reinstall never
loses enquiries or portfolio work; reactivating the plugin brings everything back. To remove the content too,
delete it from the admin screens (or with WP-CLI, e.g. `wp post delete $(wp post list --post_type=is_lead --format=ids) --force`)
before deleting the plugin.

Deactivating the plugin only removes its permalinks (/services/, /showroom/); nothing is deleted.

== Changelog ==

= 1.0.0 =
* Initial release.

== Copyright ==

Interior Specifics Core, Copyright 2026 Interior Specifics Ltd.

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

== Third-party resources ==

The editor sidebar (build/editor.js) is built with @wordpress/scripts and uses only WordPress core packages
loaded at runtime; no third-party code is bundled.

Images imported by `wp is seed` are photographs from Unsplash (https://unsplash.com), used under the
Unsplash License (https://unsplash.com/license). Each one is credited in its Media Library description.
