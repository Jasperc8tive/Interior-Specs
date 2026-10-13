# Interior Specifics — WordPress

Production WordPress build of the Interior Specifics website, converted from the Next.js app in the
repository root. Same design, motion and content, now editable in WordPress.

| Part | Path | Role |
|---|---|---|
| Theme | `themes/interior-specifics/` | Presentation: PHP templates, custom blocks for every section, Tailwind CSS, vanilla-JS motion |
| Plugin | `plugins/interior-specifics-core/` | Content and behaviour: Services, Showroom projects, Testimonials, Leads, site settings, lead forms + email, structured data, WP-CLI seeder |
| Dev stack | `docker-compose.yml` | WordPress (PHP 8.3) + MariaDB + Mailpit + WP-CLI |
| Seed data | `seed/*.json` | Exported from `../lib/*.ts` by `scripts/export-seed.mjs` |

Content lives in the plugin, so switching or updating the theme never loses services, projects, leads or settings.

## Local development

Requirements: Docker, Node 20+ (Node 24 recommended — the seed exporter imports the TypeScript data files directly), Git Bash on Windows.

```bash
cd wordpress
bash bin/setup.sh          # build, start Docker, install WordPress, activate, seed everything
```

- Site: http://localhost:8090 · Admin: http://localhost:8090/wp-admin (`admin` / `admin`)
- Mailpit (catches every email locally): http://localhost:8025
- Change the port in `.env` (`WP_PORT`).

Day-to-day:

```bash
cd themes/interior-specifics && npm start     # rebuild blocks/JS/CSS on save
bash bin/build-theme.sh                       # one-off production build of the theme
docker compose --profile cli run --rm wpcli <command>   # WP-CLI, e.g. `post list --post_type=is_lead`
bash bin/setup.sh --pages                     # rebuild page layouts from block defaults (overwrites page content)
npm run lint:js                               # in the theme or plugin folder (run npm install after pulling)
```

PHP notices go to `/tmp/wp-debug.log` inside the WordPress container (outside the web root):
`docker compose exec wordpress tail -f /tmp/wp-debug.log`.

### How the theme is organised

- `src/blocks/<name>/` — one dynamic block per page section (`block.json`, `index.js` editor UI, `render.php` markup). Every block's default attributes reproduce the original copy, so inserting a block with no changes shows the original section.
- `src/editor/index.js` — shared editor: each block declares its fields; the sidebar and live preview are generated.
- `src/css/main.css` — design tokens and utilities ported from the Next.js `globals.css`, plus the motion conventions (`data-reveal`, `data-settle`, `data-scrub`, `data-parallax`). Area styles live in `src/css/parts/`.
- `src/js/modules/*.js` — vanilla-JS behaviours, auto-loaded; each does nothing unless its `data-*` hook is on the page. All motion respects `prefers-reduced-motion`, and content is visible without JavaScript.
- `inc/helpers.php` — PHP ports of the shared React primitives (`ist_button`, `ist_animated_heading`, `ist_reveal_heading`, `ist_image`, `ist_logo` …).
- Reveals fire at Next's viewport amounts (0.15 for `[data-reveal-stagger]` grids, 0.2 for `data-reveal="section"`, 0.3 otherwise); override per element with `data-reveal-amount="0.3"`.
- In-page anchors clear the sticky header through `html { scroll-padding-top }` (main.css), for native jumps and Lenis alike, so targets need no `scroll-mt-*` of their own. Anchor clicks and the skip link move focus to the target.

## Editing the site (for the content team)

- **Pages** (Home, About, Book a Consultation, Contact, Privacy, Terms) are built from *Interior Specifics* blocks. Select a block to edit its text, images and lists in the right-hand sidebar; the preview updates live. Blocks can be reordered, duplicated or removed like any WordPress block.
- **Services**, **Showroom** (projects) and **Testimonials** have their own menus. Extra fields (investment, process steps, FAQs, gallery, budget, testimonial…) are in the sidebar panel of each item. Order is controlled by the *Order* field (Page Attributes).
- **Leads** — every form submission is stored here (and emailed). Filter by form or status, mark as contacted, export CSV.
- **Settings → Interior Specifics** — phone, WhatsApp, email, address, hours, headline stats, Calendly link, Google Maps embed, Google Analytics ID, social links, lead email routing, the free-guide PDF and the **Page heroes** (hero images for the Services archive, Showroom archive, Journal and other pages; a page's own featured image overrides the default).
- **Privacy** — Tools → Export / Erase Personal Data include the visitor's enquiries (matched on email).
- **Appearance → Menus** — header (Primary), footer (Explore) and legal menus. The *Services* dropdown fills itself from published services.

## Deploying to production

Works on any host that runs WordPress 6.6+ and PHP 8.1+ (8.3 recommended).

1. **Package**: `bash bin/package.sh` → `dist/interior-specifics.zip` and `dist/interior-specifics-core.zip`.
2. **Install** WordPress on the host, then upload and activate the plugin first, then the theme (Plugins / Appearance → Add New → Upload). Install and activate **Yoast SEO**.
3. **Configure `wp-config.php`**:
   ```php
   define( 'RESEND_API_KEY', 're_…' );        // email delivery via Resend (https://resend.com)
   define( 'WP_ENVIRONMENT_TYPE', 'production' );
   define( 'DISALLOW_FILE_EDIT', true );
   ```
   Verify your domain in Resend and set **Settings → Interior Specifics → From address** to an address on it (e.g. `hello@interiorspecifics.com`). Without `RESEND_API_KEY`, mail goes through the host's mailer (or any SMTP plugin) — leads are always saved under **Leads** either way.
4. **Content** — choose one:
   - *Fresh site with the original content*: copy `seed/` to the server and run `wp is seed --dir=/path/to/seed --user=<admin login>` then `wp is seo-defaults` (requires WP-CLI on the host; images download from Unsplash). Re-running is safe (upserts by slug). `wp is seed --only=heroes` imports just the page-hero images, filling only empty settings.
   - *From the staging site*: Tools → Export → All content on staging, Tools → Import on production (WordPress Importer, "Download and import file attachments"), then **Settings → Reading** (front page = Home, posts page = Journal) and **Settings → Permalinks** → custom structure `/blog/%postname%/`.
5. **Settings → Interior Specifics**: enter the real phone/WhatsApp number, Calendly URL, Google Maps embed URL, GA4 ID, social links and the guide PDF.
6. **Settings → Permalinks** → Save once (flushes rewrite rules).
7. **Caching**: page caching is safe — forms carry no nonces and are cache-friendly; exclude `/wp-json/is/v1/*` from caching (most cache plugins skip REST by default). Keep the full-page cache lifetime under 24 hours: form tokens expire after a day (filter `isp_form_token_max_age`). Theme assets are versioned by file time, so serve `wp-content/themes/interior-specifics/build/`, `assets/fonts/` and `wp-content/uploads/` with `Cache-Control: public, max-age=31536000, immutable` (Apache: a `<FilesMatch "\.(css|js|woff2|jpe?g|png|webp|avif|svg)$">` block with `Header set Cache-Control …`; nginx/CDN equivalent). Never long-cache HTML.
8. **Images**: the plugin saves JPEG/PNG sub-sizes as WebP when the server's GD or Imagick supports it (filter `isp_webp_sub_sizes`). If images were uploaded before the plugin was active (e.g. an import), run `wp media regenerate --yes` once.
9. **Reverse proxy**: if the site sits behind a proxy or CDN, trust `X-Forwarded-For` only from that proxy (the per-visitor rate limit uses the client IP).

### Built-in protections and data handling

- **Spam**: honeypot, a 3-second minimum fill time, signed form tokens that expire after a day, 5 submissions per form per IP (IPv6 per /64) every 10 minutes, and a site-wide cap of 30 emailed leads per form per hour (filter `isp_lead_global_limit`; leads past the cap are still stored, just not emailed). Guide emails: 1 per recipient per day, 50 per day site-wide (filter `isp_guide_daily_limit`).
- **Retention**: a daily job deletes *closed* leads older than 24 months and strips the IP hash and user agent from older leads. Change it with `define( 'ISP_LEAD_RETENTION_MONTHS', 12 );` (0 turns it off) or the `isp_lead_retention_months` filter.
- **Hardening**: comments, pingbacks and XML-RPC are off; `/wp/v2/users` is hidden from visitors and author archives redirect home; testimonials are not readable through the public REST API; author meta and schema credit the studio, never the login name.
- **Uninstalling the plugin** removes only its settings, transients and scheduled jobs. Leads, services, projects, testimonials and their terms are kept.

### Before launch checklist

- [ ] Replace placeholder phone/WhatsApp/socials in Settings → Interior Specifics
- [ ] Replace placeholder Privacy Policy and Terms copy with reviewed legal text
- [ ] Replace placeholder director names/photos and award list on About
- [ ] Replace Unsplash imagery with real project photography (Media Library; blocks and projects take Media Library images)
- [ ] Resend domain verified; send a test enquiry from every form and confirm delivery
- [ ] Calendly URL and Google Maps embed set; GA4 ID set
- [ ] Yoast: connect Google Search Console, submit `/sitemap_index.xml`
- [ ] Users → Profile: set the admin's public display name (e.g. "Interior Specifics"), never the login name
- [ ] Page heroes: the default Services hero is a portrait photo cropped to landscape; pick a landscape image
