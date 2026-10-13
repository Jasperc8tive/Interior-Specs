<?php
/**
 * WP-CLI: `wp is seed` — idempotent import of the Next.js content (seed/*.json) plus pages,
 * menus and reading settings. Safe to re-run: records are upserted by slug and images are
 * imported once (tracked by their Unsplash photo ID).
 *
 *   wp is seed --dir=/seed                 # everything
 *   wp is seed --dir=/seed --only=pages    # just pages (existing page content is kept)
 *   wp is seed --dir=/seed --only=pages --force-pages   # rebuild page content from defaults
 *   wp is seed --skip-images               # no downloads (featured images left empty)
 *   wp is seed --only=heroes               # just the archive/page hero images in Settings
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Seeder command.
 */
class ISP_CLI_Seed {

	/**
	 * Seed JSON directory.
	 *
	 * @var string
	 */
	private string $dir = '';

	/**
	 * Whether to download images.
	 *
	 * @var bool
	 */
	private bool $images = true;

	/**
	 * Author for created content: the --user, else the first administrator.
	 *
	 * @var int
	 */
	private int $author = 0;

	/**
	 * Import content, pages, menus and settings.
	 *
	 * ## OPTIONS
	 *
	 * [--dir=<dir>]
	 * : Directory containing services.json, projects.json, posts.json, testimonials.json, method.json, site.json.
	 * ---
	 * default: /seed
	 * ---
	 *
	 * [--only=<parts>]
	 * : Comma-separated subset: settings,services,projects,testimonials,posts,pages,menus,heroes.
	 *
	 * [--skip-images]
	 * : Do not download images.
	 *
	 * [--force-pages]
	 * : Overwrite the content of existing pages with the default block layout.
	 *
	 * @param string[]             $args       Positional args.
	 * @param array<string, mixed> $assoc_args Flags.
	 */
	public function __invoke( array $args, array $assoc_args ): void {
		$this->dir    = untrailingslashit( (string) ( $assoc_args['dir'] ?? '/seed' ) );
		$this->images = empty( $assoc_args['skip-images'] );
		$only         = isset( $assoc_args['only'] ) ? array_map( 'trim', explode( ',', (string) $assoc_args['only'] ) ) : array( 'settings', 'services', 'projects', 'testimonials', 'posts', 'pages', 'menus', 'heroes' );
		$force_pages  = ! empty( $assoc_args['force-pages'] );
		$this->author = $this->default_author();

		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		if ( in_array( 'settings', $only, true ) ) {
			$this->seed_settings();
		}
		if ( in_array( 'services', $only, true ) ) {
			$this->seed_services();
		}
		if ( in_array( 'projects', $only, true ) ) {
			$this->seed_projects();
		}
		if ( in_array( 'testimonials', $only, true ) ) {
			$this->seed_testimonials();
		}
		if ( in_array( 'posts', $only, true ) ) {
			$this->seed_posts();
		}
		if ( in_array( 'pages', $only, true ) ) {
			$this->seed_pages( $force_pages );
		}
		if ( in_array( 'menus', $only, true ) ) {
			$this->seed_menus();
		}
		if ( in_array( 'heroes', $only, true ) ) {
			$this->seed_heroes();
		}

		flush_rewrite_rules();
		WP_CLI::success( 'Seeding complete.' );
	}

	/**
	 * Decode one seed file.
	 *
	 * @param string $file File name.
	 * @return array<mixed>
	 */
	private function read( string $file ): array {
		$path = $this->dir . '/' . $file;
		if ( ! is_readable( $path ) ) {
			WP_CLI::error( "Missing seed file: {$path}. Run `node scripts/export-seed.mjs` first." );
		}
		$data = json_decode( (string) file_get_contents( $path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return is_array( $data ) ? $data : array();
	}

	/**
	 * Settings + site identity + reading/permalink options.
	 */
	private function seed_settings(): void {
		$site = $this->read( 'site.json' );
		update_option( 'blogname', $site['name'] ?? 'Interior Specifics' );
		update_option( 'blogdescription', $site['tagline'] ?? '' );
		update_option( 'timezone_string', 'Africa/Lagos' );
		update_option( 'date_format', 'F j, Y' );
		update_option( 'permalink_structure', '/blog/%postname%/' );
		isp_close_existing_comments();

		if ( ! get_option( 'isp_settings' ) ) {
			update_option( 'isp_settings', isp_default_settings() );
		}
		WP_CLI::log( 'Settings: site identity, permalinks (/blog/%postname%/), timezone.' );
	}

	/**
	 * The user seeded content is credited to: `--user`, else the first administrator. Content
	 * without an author shows "(No author)" in the editor and a dangling schema author.
	 */
	private function default_author(): int {
		$user = get_current_user_id();
		if ( $user ) {
			return $user;
		}
		$admins = get_users(
			array(
				'role'    => 'administrator',
				'number'  => 1,
				'orderby' => 'ID',
				'order'   => 'ASC',
				'fields'  => 'ID',
			)
		);
		return $admins ? (int) $admins[0] : 0;
	}

	/**
	 * Find a post by slug and type.
	 *
	 * @param string $post_type Post type.
	 * @param string $slug      Slug.
	 */
	private function find( string $post_type, string $slug ): int {
		$found = get_posts(
			array(
				'post_type'      => $post_type,
				'name'           => $slug,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		return $found ? (int) $found[0] : 0;
	}

	/**
	 * Insert or update a post by slug.
	 *
	 * @param array<string, mixed> $postarr wp_insert_post args (must include post_type and post_name).
	 */
	private function upsert( array $postarr ): int {
		$existing = $this->find( $postarr['post_type'], $postarr['post_name'] );
		if ( $existing ) {
			$postarr['ID'] = $existing;
		}
		// wp_insert_post() would otherwise credit the CLI user (0 without --user), and on an
		// update it would overwrite the real author; keep an existing author when there is one.
		$author   = $existing ? (int) get_post_field( 'post_author', $existing ) : 0;
		$postarr += array(
			'post_status' => 'publish',
			'post_author' => $author ? $author : $this->author,
		);
		$id       = wp_insert_post( wp_slash( $postarr ), true );
		if ( is_wp_error( $id ) ) {
			WP_CLI::warning( "Could not save {$postarr['post_name']}: " . $id->get_error_message() );
			return 0;
		}
		return (int) $id;
	}

	/**
	 * Paragraphs → core paragraph blocks.
	 *
	 * @param string[] $paragraphs Plain text.
	 */
	private function paragraphs( array $paragraphs ): string {
		return implode(
			"\n\n",
			array_map( static fn( $p ) => "<!-- wp:paragraph -->\n<p>" . esc_html( $p ) . "</p>\n<!-- /wp:paragraph -->", $paragraphs )
		);
	}

	/**
	 * Import an Unsplash image once; returns the attachment ID (0 on failure or --skip-images).
	 * Unsplash images are fetched at least 2000px wide (more when the URL asks for a larger `w`).
	 *
	 * @param string $url   Source URL.
	 * @param string $title Title / alt text.
	 */
	public function image( string $url, string $title ): int {
		if ( ! $url ) {
			return 0;
		}
		$source_id = preg_match( '#unsplash\.com/(photo-[\w-]+)#', $url, $m ) ? $m[1] : md5( $url );

		$existing = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'meta_key'       => '_isp_source_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => $source_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $existing ) {
			return (int) $existing[0];
		}
		if ( ! $this->images ) {
			return 0;
		}

		$width    = preg_match( '#[?&]w=(\d+)#', $url, $w ) ? max( 2000, (int) $w[1] ) : 2000;
		$download = str_contains( $url, 'images.unsplash.com' )
			? 'https://images.unsplash.com/' . $source_id . '?auto=format&fit=crop&w=' . $width . '&q=80&fm=jpg'
			: $url;
		$tmp      = download_url( $download, 60 );
		if ( is_wp_error( $tmp ) ) {
			WP_CLI::warning( "Image download failed ({$source_id}): " . $tmp->get_error_message() );
			return 0;
		}
		$id = media_handle_sideload(
			array(
				'name'     => sanitize_title( $title ) . '.jpg',
				'tmp_name' => $tmp,
			),
			0,
			$title
		);
		if ( is_wp_error( $id ) ) {
			wp_delete_file( $tmp );
			WP_CLI::warning( "Image import failed ({$source_id}): " . $id->get_error_message() );
			return 0;
		}
		update_post_meta( $id, '_isp_source_id', $source_id );
		update_post_meta( $id, '_wp_attachment_image_alt', $title );
		// Credit in the description (Media Library only): the caption would show in the lightbox.
		wp_update_post(
			array(
				'ID'           => $id,
				'post_author'  => $this->author,
				'post_content' => str_starts_with( $source_id, 'photo-' ) ? 'Photo: Unsplash (https://unsplash.com/photos/' . substr( $source_id, 6 ) . ')' : '',
			)
		);
		WP_CLI::log( "  image ← {$source_id}" );
		return (int) $id;
	}

	/**
	 * Services CPT.
	 */
	private function seed_services(): void {
		foreach ( $this->read( 'services.json' ) as $i => $s ) {
			$id = $this->upsert(
				array(
					'post_type'    => 'is_service',
					'post_name'    => $s['slug'],
					'post_title'   => $s['title'],
					'post_excerpt' => $s['short'],
					'post_content' => $this->paragraphs( $s['overview'] ),
					'menu_order'   => $i,
				)
			);
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'isp_investment_from', $s['investmentFrom'] );
			update_post_meta( $id, 'isp_process', $s['process'] );
			update_post_meta( $id, 'isp_faqs', $s['faqs'] );
			update_post_meta( $id, 'isp_seo_keywords', $s['keywords'] );
			update_post_meta( $id, 'isp_featured', ! empty( $s['featured'] ) );
			$thumb = $this->image( $s['image'], $s['title'] );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
			WP_CLI::log( "Service: {$s['title']}" );
		}
	}

	/**
	 * Project CPT, types, scope and galleries.
	 */
	private function seed_projects(): void {
		foreach ( array( 'Residential', 'Commercial', 'Hospitality' ) as $type ) {
			if ( ! term_exists( sanitize_title( $type ), 'is_project_type' ) ) {
				wp_insert_term( $type, 'is_project_type', array( 'slug' => sanitize_title( $type ) ) );
			}
		}

		foreach ( $this->read( 'projects.json' ) as $i => $p ) {
			$id = $this->upsert(
				array(
					'post_type'    => 'is_project',
					'post_name'    => $p['slug'],
					'post_title'   => $p['title'],
					'post_excerpt' => $p['blurb'],
					'menu_order'   => $i,
				)
			);
			if ( ! $id ) {
				continue;
			}
			foreach ( array( 'location', 'budget', 'year', 'timeline', 'challenge', 'solution', 'results' ) as $key ) {
				update_post_meta( $id, 'isp_' . $key, $p[ $key ] ?? '' );
			}
			update_post_meta( $id, 'isp_testimonial_quote', $p['testimonial']['quote'] ?? '' );
			update_post_meta( $id, 'isp_testimonial_name', $p['testimonial']['name'] ?? '' );
			update_post_meta( $id, 'isp_featured', true );
			wp_set_object_terms( $id, sanitize_title( $p['type'] ), 'is_project_type' );
			wp_set_object_terms( $id, $p['scope'], 'is_project_scope' );

			$thumb = $this->image( $p['image'], $p['title'] );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
			$gallery = array();
			foreach ( $p['gallery'] as $n => $url ) {
				$gallery[] = $this->image( $url, $p['title'] . ' — detail ' . ( $n + 1 ) );
			}
			update_post_meta( $id, 'isp_gallery', array_values( array_filter( $gallery ) ) );
			WP_CLI::log( "Project: {$p['title']}" );
		}
	}

	/**
	 * Testimonials CPT (slug derived from the client name).
	 */
	private function seed_testimonials(): void {
		foreach ( $this->read( 'testimonials.json' ) as $i => $t ) {
			$id = $this->upsert(
				array(
					'post_type'    => 'is_testimonial',
					'post_name'    => sanitize_title( $t['name'] ),
					'post_title'   => $t['name'],
					'post_content' => $t['quote'],
					'menu_order'   => $i,
				)
			);
			if ( $id ) {
				update_post_meta( $id, 'isp_location', $t['location'] );
				update_post_meta( $id, 'isp_project_label', $t['project'] );
				WP_CLI::log( "Testimonial: {$t['name']}" );
			}
		}
	}

	/**
	 * Journal posts with categories, dates and featured images.
	 */
	private function seed_posts(): void {
		foreach ( $this->read( 'posts.json' ) as $p ) {
			$cat = term_exists( $p['category'], 'category' );
			if ( ! $cat ) {
				$cat = wp_insert_term( $p['category'], 'category' );
			}
			$blocks = array();
			foreach ( $p['body'] as $block ) {
				$blocks[] = 'h2' === $block['type']
					? "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">" . esc_html( $block['text'] ) . "</h2>\n<!-- /wp:heading -->"
					: "<!-- wp:paragraph -->\n<p>" . esc_html( $block['text'] ) . "</p>\n<!-- /wp:paragraph -->";
			}
			$date = $p['date'] . ' 09:00:00';
			$id   = $this->upsert(
				array(
					'post_type'     => 'post',
					'post_name'     => $p['slug'],
					'post_title'    => $p['title'],
					'post_excerpt'  => $p['excerpt'],
					'post_content'  => implode( "\n\n", $blocks ),
					'post_date'     => $date,
					'post_date_gmt' => get_gmt_from_date( $date ),
					'post_category' => array( (int) ( is_array( $cat ) ? $cat['term_id'] : $cat ) ),
				)
			);
			if ( ! $id ) {
				continue;
			}
			update_post_meta( $id, 'isp_read_time', $p['readTime'] );
			$thumb = $this->image( $p['image'], $p['title'] );
			if ( $thumb ) {
				set_post_thumbnail( $id, $thumb );
			}
			WP_CLI::log( "Post: {$p['title']}" );
		}
		$this->delete_default( 1, 'hello-world' );
		$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );
		if ( $uncategorized && 0 === (int) $uncategorized->count ) {
			wp_update_term(
				$uncategorized->term_id,
				'category',
				array(
					'name' => 'Journal',
					'slug' => 'journal',
				)
			);
		}
	}

	/**
	 * Hero images for archives and fallback pages (Settings → Interior Specifics → Page heroes).
	 * Only empty settings are filled, so images chosen in the admin are kept.
	 */
	private function seed_heroes(): void {
		$heroes = array(
			'hero_services' => array( 'photo-1618220179428-22790b461013', 'Refined living space designed by Interior Specifics' ),
			'hero_showroom' => array( 'photo-1505691938895-1758d7feb511', 'Luxury bedroom interior from the Interior Specifics showroom' ),
			'hero_journal'  => array( 'photo-1524758631624-e2822e304c36', 'Bright, considered interior with natural light' ),
			'hero_default'  => array( 'photo-1600210492493-0946911123ea', 'Interior detail' ),
		);

		$saved    = get_option( 'isp_settings', array() );
		$settings = is_array( $saved ) && $saved ? $saved : isp_default_settings();
		foreach ( $heroes as $key => [ $photo, $alt ] ) {
			if ( ! empty( $settings[ $key ] ) && wp_attachment_is_image( (int) $settings[ $key ] ) ) {
				WP_CLI::log( "Hero kept: {$key}" );
				continue;
			}
			$id               = $this->image( "https://images.unsplash.com/{$photo}?auto=format&fit=crop&w=2400&q=80", $alt );
			$settings[ $key ] = $id ? (string) $id : '';
			WP_CLI::log( "Hero: {$key} → " . ( $id ? "attachment {$id}" : 'none' ) );
		}
		update_option( 'isp_settings', $settings );
	}

	/**
	 * Serialise a block, importing any Unsplash images found in its attributes (including
	 * block.json defaults) so pages ship with local, responsive media.
	 *
	 * @param string               $name  Block name without namespace.
	 * @param array<string, mixed> $attrs Attributes overriding block.json defaults.
	 */
	private function block( string $name, array $attrs = array() ): string {
		$full = str_contains( $name, '/' ) ? $name : 'interior-specifics/' . $name;
		$type = WP_Block_Type_Registry::get_instance()->get_registered( $full );
		if ( ! $type ) {
			WP_CLI::warning( "Block {$full} is not registered (is the theme active and built?). Writing it anyway." );
		} else {
			foreach ( (array) $type->attributes as $key => $def ) {
				if ( ! array_key_exists( $key, $attrs ) && array_key_exists( 'default', $def ) ) {
					$attrs[ $key ] = $def['default'];
				}
			}
		}
		$attrs = $this->localise_images( $attrs );
		return serialize_block(
			array(
				'blockName'    => $full,
				'attrs'        => $attrs,
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
	}

	/**
	 * Walk attributes; wherever an `imageUrl` sits next to an empty `imageId`, import it.
	 *
	 * @param array<mixed> $attrs Attributes or a repeater item.
	 * @return array<mixed>
	 */
	private function localise_images( array $attrs ): array {
		if ( ! empty( $attrs['imageUrl'] ) && array_key_exists( 'imageId', $attrs ) && empty( $attrs['imageId'] ) ) {
			$alt              = (string) ( $attrs['imageAlt'] ?? $attrs['title'] ?? $attrs['name'] ?? 'Interior Specifics' );
			$attrs['imageId'] = $this->image( (string) $attrs['imageUrl'], wp_strip_all_tags( $alt ) );
		}
		foreach ( $attrs as $key => $value ) {
			if ( is_array( $value ) ) {
				$attrs[ $key ] = $this->localise_images( $value );
			}
		}
		return $attrs;
	}

	/**
	 * Prose body for simple pages (legal), styled by the theme's .is-prose.
	 *
	 * @param string[] $paragraphs Paragraphs.
	 */
	private function prose( array $paragraphs ): string {
		return "<!-- wp:group {\"className\":\"is-prose\",\"layout\":{\"type\":\"constrained\"}} -->\n<div class=\"wp-block-group is-prose\">" . $this->paragraphs( $paragraphs ) . "</div>\n<!-- /wp:group -->";
	}

	/**
	 * Page definitions: slug => [title, block markup].
	 *
	 * @return array<string, array{0: string, 1: callable}>
	 */
	private function pages(): array {
		$u = static fn( string $id, int $w = 2000 ) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";

		return array(
			'home'    => array(
				'Home',
				fn() => implode(
					"\n\n",
					array_map(
						fn( $b ) => $this->block( $b ),
						array( 'story-hero', 'marquee', 'ethos', 'method-journey', 'why-ledger', 'featured-work', 'stats-band', 'founder-note', 'testimonials', 'services-preview', 'journal-preview', 'closing-cta' )
					)
				),
			),
			'about'   => array(
				'About',
				fn() => implode(
					"\n\n",
					array(
						$this->block(
							'page-hero',
							array(
								'eyebrow'    => 'Since ' . isp_setting( 'founded_year' ),
								'title'      => 'Crafting legacies through design',
								'accentFrom' => 1,
								'accentTo'   => 1,
								'intro'      => 'We are a Lagos-based luxury interior design studio dedicated to spaces that honour the people who live and work in them.',
								'imageUrl'   => $u( 'photo-1567538096630-e0c55bd6374c' ),
								'imageAlt'   => 'Elegant designed interior by Interior Specifics',
							)
						),
						$this->block( 'about-profile' ),
						$this->block(
							'value-grid',
							array(
								'eyebrow'    => 'What We Stand For',
								'title'      => 'Our core values',
								'columns'    => 4,
								'background' => 'ivory',
								'items'      => array(
									array(
										'title' => 'Excellence',
										'body'  => 'We pursue the exceptional in every detail, never the merely acceptable.',
									),
									array(
										'title' => 'Integrity',
										'body'  => 'Transparent budgets, honest timelines and advice you can trust.',
									),
									array(
										'title' => 'Innovation',
										'body'  => 'Fresh thinking grounded in timeless design principles.',
									),
									array(
										'title' => 'Client-Centricity',
										'body'  => 'Your vision and your comfort lead every decision we make.',
									),
								),
							)
						),
						$this->block( 'process-timeline' ),
						$this->block( 'directors' ),
						$this->block( 'awards' ),
						$this->block(
							'cta-section',
							array(
								'eyebrow' => 'Work With Us',
								'title'   => 'Partner with our team',
								'intro'   => "Tell us about your space and your ambitions. We'll show you what's possible.",
							)
						),
					)
				),
			),
			'book'    => array(
				'Book a Consultation',
				fn() => implode(
					"\n\n",
					array(
						$this->block(
							'page-hero',
							array(
								'eyebrow'    => 'Private Consultation',
								'title'      => 'Book your private consultation',
								'accentFrom' => 2,
								'accentTo'   => 3,
								'intro'      => 'A complimentary 30-minute conversation about your space — in person at our Lagos showroom, by video, or over the phone.',
								'imageUrl'   => $u( 'photo-1567016526105-22da7c13161a' ),
								'imageAlt'   => 'Elegant consultation lounge interior',
							)
						),
						$this->block(
							'value-grid',
							array(
								'eyebrow'    => 'What to Expect',
								'title'      => 'Your first 30 minutes with us',
								'columns'    => 3,
								'background' => 'ivory',
								'items'      => array(
									array(
										'title' => 'A focused conversation',
										'body'  => 'We listen to your vision, lifestyle and goals for the space.',
									),
									array(
										'title' => 'Expert perspective',
										'body'  => "Honest, experienced guidance on what's possible and what it takes.",
									),
									array(
										'title' => 'Clear next steps',
										'body'  => 'A tailored proposal and timeline — with no obligation to proceed.',
									),
								),
							)
						),
						$this->block( 'booking' ),
					)
				),
			),
			'contact' => array(
				'Contact',
				fn() => implode(
					"\n\n",
					array(
						$this->block(
							'page-hero',
							array(
								'eyebrow'    => 'Contact',
								'title'      => "Let's create something exceptional",
								'accentFrom' => 2,
								'accentTo'   => 3,
								'intro'      => "Ready to transform your space? We'd love to hear from you.",
								'imageUrl'   => $u( 'photo-1556912172-45b7abe8b7e1' ),
								'imageAlt'   => 'Luxury interior detail',
							)
						),
						$this->block( 'contact' ),
					)
				),
			),
			'privacy' => array(
				'Privacy Policy',
				fn() => $this->block(
					'page-hero',
					array(
						'title'    => 'Privacy Policy',
						'imageUrl' => $u( 'photo-1600210492493-0946911123ea' ),
						'imageAlt' => 'Interior detail',
					)
				) . "\n\n" . $this->prose(
					array(
						'This is placeholder privacy content. Interior Specifics respects your privacy and is committed to protecting the personal information you share with us. We collect information you provide through our contact and newsletter forms solely to respond to your inquiries and, with your consent, to send relevant updates.',
						'We do not sell your data. Enquiries are kept while we work with you and deleted within 24 months of being closed. You may request access to, correction of, or deletion of your information at any time by contacting us. A complete, legally reviewed policy will be published before launch.',
					)
				),
			),
			'terms'   => array(
				'Terms of Use',
				fn() => $this->block(
					'page-hero',
					array(
						'title'    => 'Terms of Use',
						'imageUrl' => $u( 'photo-1615875605825-5eb9bb5d52ac' ),
						'imageAlt' => 'Interior detail',
					)
				) . "\n\n" . $this->prose(
					array(
						'This is placeholder terms content. By using the Interior Specifics website you agree to use it lawfully and not to misuse the content or materials provided. All imagery, copy and designs remain the property of Interior Specifics unless otherwise stated.',
						'Information on this site is provided for general guidance and does not constitute a binding quote. A complete, legally reviewed set of terms will be published before launch.',
					)
				),
			),
			'blog'    => array( 'Journal', static fn() => '' ),
		);
	}

	/**
	 * Create pages; set the front page, posts page and privacy page.
	 *
	 * @param bool $force Overwrite existing page content.
	 */
	private function seed_pages( bool $force ): void {
		$ids = array();
		foreach ( $this->pages() as $slug => [ $title, $content ] ) {
			$existing = $this->find( 'page', $slug );
			if ( $existing && ! $force ) {
				$ids[ $slug ] = $existing;
				WP_CLI::log( "Page kept: {$title} (use --force-pages to rebuild)" );
				continue;
			}
			$ids[ $slug ] = $this->upsert(
				array(
					'post_type'    => 'page',
					'post_name'    => $slug,
					'post_title'   => $title,
					'post_content' => $content(),
				)
			);
			WP_CLI::log( "Page: {$title}" );
		}
		$this->delete_default( 2, 'sample-page' );

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
		update_option( 'page_for_posts', $ids['blog'] );
		update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
	}

	/**
	 * Remove WordPress' install-time sample content, only if it is still the untouched default.
	 *
	 * @param int    $id   Post ID WordPress assigns on install.
	 * @param string $slug Its default slug.
	 */
	private function delete_default( int $id, string $slug ): void {
		$post = get_post( $id );
		if ( $post && $slug === $post->post_name ) {
			wp_delete_post( $id, true );
		}
	}

	/**
	 * Menus: primary (with dropdown children), footer, legal.
	 */
	private function seed_menus(): void {
		$page = fn( string $slug ) => $this->find( 'page', $slug );

		$primary = $this->reset_menu( 'Primary', 'primary' );
		$this->page_item( $primary, $page( 'home' ), 'Home' );
		$about = $this->page_item( $primary, $page( 'about' ), 'About' );
		foreach ( array(
			'Directors'              => '#directors',
			'Company Profile'        => '#profile',
			'Awards & Recognition'   => '#awards',
			'Clients & Case Studies' => '#clients',
		) as $label => $hash ) {
			$this->link_item( $primary, $label, '/about/' . $hash, $about );
		}
		$showroom = $this->archive_item( $primary, 'is_project', 'Showroom' );
		$this->link_item( $primary, 'All Projects', '/showroom/', $showroom );
		foreach ( array( 'Residential', 'Commercial', 'Hospitality' ) as $type ) {
			$this->link_item( $primary, $type, '/showroom/?type=' . strtolower( $type ), $showroom );
		}
		// Services children are filled automatically from the Services post type by the theme.
		$this->archive_item( $primary, 'is_service', 'Services' );
		$this->page_item( $primary, $page( 'blog' ), 'Blog' );
		$this->page_item( $primary, $page( 'contact' ), 'Contact' );

		$footer = $this->reset_menu( 'Footer — Explore', 'footer' );
		$this->page_item( $footer, $page( 'home' ), 'Home' );
		$this->page_item( $footer, $page( 'about' ), 'About' );
		$this->archive_item( $footer, 'is_project', 'Showroom' );
		$this->archive_item( $footer, 'is_service', 'Services' );
		$this->page_item( $footer, $page( 'blog' ), 'Blog' );
		$this->page_item( $footer, $page( 'contact' ), 'Contact' );

		$legal = $this->reset_menu( 'Legal', 'legal' );
		$this->page_item( $legal, $page( 'privacy' ), 'Privacy Policy' );
		$this->page_item( $legal, $page( 'terms' ), 'Terms' );

		WP_CLI::log( 'Menus: primary, footer, legal.' );
	}

	/**
	 * Delete-and-recreate a menu and assign it to a location.
	 *
	 * @param string $name     Menu name.
	 * @param string $location Theme location.
	 */
	private function reset_menu( string $name, string $location ): int {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			wp_delete_nav_menu( $menu->term_id );
		}
		$id                     = (int) wp_create_nav_menu( $name );
		$locations              = (array) get_theme_mod( 'nav_menu_locations', array() );
		$locations[ $location ] = $id;
		set_theme_mod( 'nav_menu_locations', $locations );
		return $id;
	}

	/**
	 * Add a page menu item.
	 *
	 * @param int    $menu    Menu ID.
	 * @param int    $page_id Page ID.
	 * @param string $label   Label.
	 * @param int    $parent  Parent item ID.
	 */
	private function page_item( int $menu, int $page_id, string $label, int $parent = 0 ): int {
		return (int) wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page_id,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			)
		);
	}

	/**
	 * Add a post-type archive menu item.
	 *
	 * @param int    $menu      Menu ID.
	 * @param string $post_type Post type.
	 * @param string $label     Label.
	 */
	private function archive_item( int $menu, string $post_type, string $label ): int {
		return (int) wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'  => $label,
				'menu-item-object' => $post_type,
				'menu-item-type'   => 'post_type_archive',
				'menu-item-status' => 'publish',
			)
		);
	}

	/**
	 * Add a custom-link menu item. Relative URLs keep working when the domain changes.
	 *
	 * @param int    $menu   Menu ID.
	 * @param string $label  Label.
	 * @param string $url    URL or path.
	 * @param int    $parent Parent item ID.
	 */
	private function link_item( int $menu, string $label, string $url, int $parent = 0 ): int {
		return (int) wp_update_nav_menu_item(
			$menu,
			0,
			array(
				'menu-item-title'     => $label,
				'menu-item-url'       => $url,
				'menu-item-type'      => 'custom',
				'menu-item-status'    => 'publish',
				'menu-item-parent-id' => $parent,
			)
		);
	}
}

WP_CLI::add_command( 'is seed', 'ISP_CLI_Seed' );
