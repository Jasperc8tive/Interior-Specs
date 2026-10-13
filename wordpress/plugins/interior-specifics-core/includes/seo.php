<?php
/**
 * SEO: structured data, titles and descriptions, social tags, sitemaps and `wp is seo-defaults`.
 *
 * Ports app/layout.tsx metadata + InteriorDesigner JSON-LD, the per-route generateMetadata()
 * calls and the Service / CreativeWork / Article JSON-LD of the Next.js site.
 *
 * Two modes, decided per request:
 * - Yoast SEO active: Yoast owns <title>, meta description, canonical, OG/Twitter and sitemaps.
 *   This module only adjusts Yoast (OG locale, project OG titles) and merges its pieces into
 *   Yoast's schema graph, so the page carries exactly one JSON-LD graph and one Organization.
 * - No SEO plugin: this module prints the title, description, canonical (archives), OG/Twitter
 *   tags and a single JSON-LD @graph itself, reading the same data Yoast would (its options
 *   and _yoast_wpseo_* post meta when present, else built-in templates mirroring the Next site).
 *
 * Any other SEO plugin (Rank Math, AIOSEO, SEOPress, The SEO Framework) is left in charge.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/*
 * -------------------------------------------------------------------------------------------
 * Mode detection
 * -------------------------------------------------------------------------------------------
 */

/**
 * Whether Yoast SEO is running. Only reliable from `plugins_loaded` on.
 */
function isp_seo_yoast_active(): bool {
	return defined( 'WPSEO_VERSION' );
}

/**
 * Whether this plugin should print head tags and JSON-LD itself (no SEO plugin active).
 */
function isp_seo_handles_head(): bool {
	$other = defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' );

	/**
	 * Whether Interior Specifics Core prints title, meta description, OG/Twitter tags and JSON-LD.
	 *
	 * @param bool $handles True when no SEO plugin is active.
	 */
	return (bool) apply_filters( 'isp_seo_handles_head', ! isp_seo_yoast_active() && ! $other );
}

/*
 * -------------------------------------------------------------------------------------------
 * Shared data
 * -------------------------------------------------------------------------------------------
 */

/**
 * Title/description templates mirroring the Next.js metadata, in Yoast's wpseo_titles format.
 * `wp is seo-defaults` writes these into Yoast; the no-plugin fallback reads them directly.
 *
 * @return array<string, mixed>
 */
function isp_seo_default_templates(): array {
	$showroom_desc = __( 'Explore case studies of luxury residential, commercial and hospitality interiors across Lagos, and book a private showroom tour with Interior Specifics.', 'interior-specifics-core' );

	return array(
		'separator'                     => 'sc-pipe',
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-home-wpseo'              => __( '%%sitename%% — Luxury Interior Designer in Lagos, Nigeria', 'interior-specifics-core' ),
		'title-page'                    => '%%title%% %%page%% %%sep%% %%sitename%%',
		'title-post'                    => '%%title%% %%page%% %%sep%% %%sitename%%',
		'metadesc-post'                 => '%%excerpt%%',
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-is_service'              => __( '%%title%% in Lagos %%sep%% %%sitename%%', 'interior-specifics-core' ),
		'metadesc-is_service'           => '%%excerpt%%',
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-is_project'              => __( '%%title%% — %%ct_is_project_type%% Case Study %%sep%% %%sitename%%', 'interior-specifics-core' ),
		'metadesc-is_project'           => '%%excerpt%%',
		'social-title-is_project'       => '%%title%% — %%sitename%%',
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-ptarchive-is_service'    => __( 'Services — End-to-End Interior Design Solutions %%page%% %%sep%% %%sitename%%', 'interior-specifics-core' ),
		'metadesc-ptarchive-is_service' => __( 'From residential and commercial design to bespoke furniture and lighting, explore the full range of luxury interior design services from Interior Specifics, Lagos.', 'interior-specifics-core' ),
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-ptarchive-is_project'    => __( 'Showroom — Our Portfolio of Luxury Interiors %%page%% %%sep%% %%sitename%%', 'interior-specifics-core' ),
		'metadesc-ptarchive-is_project' => $showroom_desc,
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-tax-is_project_type'     => __( '%%term_title%% Interiors — Showroom %%page%% %%sep%% %%sitename%%', 'interior-specifics-core' ),
		'metadesc-tax-is_project_type'  => $showroom_desc,
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-404-wpseo'               => __( 'Page not found %%sep%% %%sitename%%', 'interior-specifics-core' ),
		/* translators: Yoast SEO template; keep the %%…%% variables. */
		'title-search-wpseo'            => __( 'You searched for %%searchphrase%% %%page%% %%sep%% %%sitename%%', 'interior-specifics-core' ),
		'title-author-wpseo'            => '%%name%% %%page%% %%sep%% %%sitename%%',
		'title-archive-wpseo'           => '%%date%% %%page%% %%sep%% %%sitename%%',
		'title-tax-category'            => '%%term_title%% %%page%% %%sep%% %%sitename%%',
		'title-tax-post_tag'            => '%%term_title%% %%page%% %%sep%% %%sitename%%',
		'noindex-is_testimonial'        => true,
		'noindex-is_lead'               => true,
		'noindex-tax-is_project_scope'  => true,
		'noindex-author-wpseo'          => true,
		// Author archives expose login names; hardening.php also redirects them without Yoast.
		'disable-author'                => true,
	);
}

/**
 * Templates in effect: Yoast's stored option (when Yoast is or was installed) over the defaults.
 *
 * @return array<string, mixed>
 */
function isp_seo_templates(): array {
	$saved = get_option( 'wpseo_titles', array() );
	return array_merge( isp_seo_default_templates(), is_array( $saved ) ? $saved : array() );
}

/**
 * The title separator character for the configured Yoast separator key.
 */
function isp_seo_separator(): string {
	$map = array(
		'sc-dash'   => '-',
		'sc-ndash'  => '–',
		'sc-mdash'  => '—',
		'sc-colon'  => ':',
		'sc-middot' => '·',
		'sc-bull'   => '•',
		'sc-star'   => '*',
		'sc-smstar' => '⋆',
		'sc-pipe'   => '|',
		'sc-tilde'  => '~',
		'sc-laquo'  => '«',
		'sc-raquo'  => '»',
		'sc-lt'     => '<',
		'sc-gt'     => '>',
	);
	$key = (string) ( isp_seo_templates()['separator'] ?? 'sc-pipe' );
	return $map[ $key ] ?? '|';
}

/**
 * Plain-text excerpt for descriptions: the manual excerpt, else trimmed content.
 *
 * @param WP_Post $post Post.
 */
function isp_seo_excerpt( WP_Post $post ): string {
	$text = '' !== trim( $post->post_excerpt ) ? $post->post_excerpt : excerpt_remove_blocks( strip_shortcodes( $post->post_content ) );
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
	return '' !== trim( $post->post_excerpt ) ? $text : wp_html_excerpt( $text, 155, '…' );
}

/**
 * Replace the Yoast-style %%variables%% this site uses, then tidy separators and spacing.
 *
 * @param string               $template Template.
 * @param array<string, mixed> $ctx      Context: post (WP_Post), term (WP_Term), title, page.
 */
function isp_seo_replace_vars( string $template, array $ctx = array() ): string {
	$sep  = isp_seo_separator();
	$post = ( $ctx['post'] ?? null ) instanceof WP_Post ? $ctx['post'] : null;
	$term = ( $ctx['term'] ?? null ) instanceof WP_Term ? $ctx['term'] : null;

	$out = preg_replace_callback(
		'/%%([a-z0-9_\-]+)%%/i',
		static function ( array $m ) use ( $sep, $post, $term, $ctx ): string {
			$var = strtolower( $m[1] );
			switch ( $var ) {
				case 'sitename':
					return (string) get_bloginfo( 'name' );
				case 'sitedesc':
					return (string) get_bloginfo( 'description' );
				case 'sep':
					return $sep;
				case 'title':
					return (string) ( $ctx['title'] ?? ( $post ? get_the_title( $post ) : '' ) );
				case 'excerpt':
				case 'excerpt_only':
					return $post ? isp_seo_excerpt( $post ) : '';
				case 'term_title':
				case 'category':
					return $term ? $term->name : '';
				case 'term_description':
				case 'category_description':
					return $term ? wp_strip_all_tags( term_description( $term ) ) : '';
				case 'pt_plural':
				case 'pt_single':
					$type = get_post_type_object( (string) ( $ctx['post_type'] ?? ( $post ? $post->post_type : '' ) ) );
					if ( ! $type ) {
						return '';
					}
					return 'pt_plural' === $var ? $type->labels->name : $type->labels->singular_name;
				case 'page':
					$paged = (int) ( $ctx['page'] ?? 0 );
					$max   = (int) ( $ctx['max_page'] ?? 0 );
					if ( $paged < 2 ) {
						return '';
					}
					/* translators: 1: current page number, 2: total pages. */
					return $sep . ' ' . ( $max > 1 ? sprintf( __( 'Page %1$d of %2$d', 'interior-specifics-core' ), $paged, $max ) : sprintf( __( 'Page %d', 'interior-specifics-core' ), $paged ) );
				case 'searchphrase':
					return get_search_query( false );
				case 'currentyear':
					return wp_date( 'Y' );
				case 'date':
				case 'name':
					return (string) ( $ctx['title'] ?? '' );
			}
			// %%ct_<taxonomy>%%: the post's terms in that taxonomy.
			if ( $post && str_starts_with( $var, 'ct_' ) && ! str_starts_with( $var, 'ct_desc_' ) ) {
				$terms = get_the_terms( $post, substr( $var, 3 ) );
				return ( $terms && ! is_wp_error( $terms ) ) ? implode( ', ', wp_list_pluck( $terms, 'name' ) ) : '';
			}
			return '';
		},
		$template
	);

	$out = trim( (string) preg_replace( '/\s+/u', ' ', (string) $out ) );
	$q   = preg_quote( $sep, '/' );
	// Collapse doubled separators and strip leading/trailing ones left by empty variables.
	$out = (string) preg_replace( '/(?:\s*' . $q . '){2,}\s*/u', ' ' . $sep . ' ', $out );
	$out = (string) preg_replace( '/^(?:\s*' . $q . '\s*)+|(?:\s*' . $q . '\s*)+$/u', '', $out );
	return trim( $out );
}

/**
 * Whether a social URL points at a real profile (not a bare placeholder like https://instagram.com/).
 *
 * @param string $url URL.
 */
function isp_seo_is_profile_url( string $url ): bool {
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	return '' !== $path && (bool) wp_http_validate_url( $url );
}

/**
 * The business's logo image URL: the custom logo, else the Site Icon. Empty when neither exists.
 */
function isp_seo_logo_url(): string {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}
	return has_site_icon() ? (string) get_site_icon_url( 512 ) : '';
}

/**
 * Schema @id of the business node (the same id Yoast uses for its Organization piece).
 */
function isp_seo_org_id(): string {
	return trailingslashit( home_url() ) . '#organization';
}

/**
 * Business node (InteriorDesigner, a LocalBusiness subtype) built from the plugin settings.
 *
 * @return array<string, mixed>
 */
function isp_seo_organization(): array {
	$s    = isp_settings();
	$name = isp_setting( 'name' );

	$node = array(
		'@type'       => 'InteriorDesigner',
		'@id'         => isp_seo_org_id(),
		'name'        => $name,
		'legalName'   => isp_setting( 'legal_name' ),
		'description' => isp_setting( 'description' ),
		'url'         => trailingslashit( home_url() ),
		'email'       => isp_setting( 'email' ),
		'telephone'   => isp_setting( 'phone_display' ),
		'address'     => array_filter(
			array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => isp_setting( 'address_street' ),
				'addressLocality' => isp_setting( 'address_city' ),
				'addressCountry'  => isp_setting( 'address_country' ),
			)
		),
		'areaServed'  => 'Lagos, Nigeria',
		'priceRange'  => '₦₦₦₦',
	);

	if ( ! empty( $s['founded_year'] ) && preg_match( '/^\d{4}$/', (string) $s['founded_year'] ) ) {
		$node['foundingDate'] = (string) $s['founded_year'];
	}

	$same_as = array_values( array_filter( array_values( isp_socials() ), 'isp_seo_is_profile_url' ) );
	if ( $same_as ) {
		$node['sameAs'] = $same_as;
	}

	$logo = isp_seo_logo_url();
	if ( $logo ) {
		$node['logo']  = $logo;
		$node['image'] = $logo;
	}

	/**
	 * Filters the InteriorDesigner (Organization) structured data node.
	 *
	 * @param array<string, mixed> $node Node.
	 */
	return (array) apply_filters( 'isp_seo_organization', array_filter( $node ) );
}

/**
 * Numeric minimum price from a display string like "₦25M", "₦1.5M" or "₦800K". Null if none.
 *
 * @param string $text Display price.
 */
function isp_seo_parse_price( string $text ): ?int {
	if ( ! preg_match( '/(\d+(?:[.,]\d+)?)\s*([KMB])?/i', $text, $m ) ) {
		return null;
	}
	$number = (float) str_replace( ',', '.', $m[1] );
	$scale  = array(
		'K' => 1e3,
		'M' => 1e6,
		'B' => 1e9,
	);
	$unit   = strtoupper( $m[2] ?? '' );
	return (int) round( $number * ( $scale[ $unit ] ?? 1 ) );
}

/**
 * Service node for a single is_service (Next: Service JSON-LD), with an Offer from investment_from.
 *
 * @param WP_Post $post Service.
 * @return array<string, mixed>
 */
function isp_seo_service_node( WP_Post $post ): array {
	$url  = get_permalink( $post );
	$node = array(
		'@type'            => 'Service',
		'@id'              => $url . '#service',
		'name'             => get_the_title( $post ),
		'serviceType'      => get_the_title( $post ),
		'description'      => isp_seo_excerpt( $post ),
		'url'              => $url,
		'provider'         => array( '@id' => isp_seo_org_id() ),
		'areaServed'       => 'Lagos, Nigeria',
		'mainEntityOfPage' => array( '@id' => $url ),
	);

	$image = get_the_post_thumbnail_url( $post, 'ist-hero' );
	if ( $image ) {
		$node['image'] = $image;
	}

	$from = (string) isp_meta( $post->ID, 'investment_from' );
	if ( '' !== $from ) {
		/* translators: %s: starting price, e.g. ₦25M. */
		$label = preg_match( '/\d/', $from ) ? sprintf( __( 'From %s', 'interior-specifics-core' ), $from ) : $from;
		$spec  = array(
			'@type'         => 'PriceSpecification',
			'priceCurrency' => 'NGN',
			'description'   => $label,
		);
		$min   = isp_seo_parse_price( $from );
		if ( null !== $min && str_contains( $from, '₦' ) ) {
			$spec['minPrice'] = $min;
		}
		$node['offers'] = array(
			'@type'              => 'Offer',
			'description'        => $label,
			'priceCurrency'      => 'NGN',
			'priceSpecification' => $spec,
			'seller'             => array( '@id' => isp_seo_org_id() ),
		);
	}

	return $node;
}

/**
 * Question nodes from a service's FAQs (rendered visibly on the service page).
 *
 * @param WP_Post $post Service.
 * @return array<int, array<string, mixed>>
 */
function isp_seo_faq_questions( WP_Post $post ): array {
	$questions = array();
	foreach ( (array) isp_meta( $post->ID, 'faqs' ) as $faq ) {
		if ( ! is_array( $faq ) || empty( $faq['q'] ) || empty( $faq['a'] ) ) {
			continue;
		}
		$questions[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( (string) $faq['q'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( (string) $faq['a'] ),
			),
		);
	}
	return $questions;
}

/**
 * CreativeWork node for a single is_project (Next: CreativeWork JSON-LD).
 *
 * @param WP_Post $post Project.
 * @return array<string, mixed>
 */
function isp_seo_project_node( WP_Post $post ): array {
	$url = get_permalink( $post );
	$ids = array_merge( array( (int) get_post_thumbnail_id( $post ) ), array_map( 'absint', (array) isp_meta( $post->ID, 'gallery' ) ) );

	$images = array();
	foreach ( array_unique( array_filter( $ids ) ) as $id ) {
		$src = wp_get_attachment_image_url( $id, 'ist-hero' );
		if ( $src ) {
			$images[] = $src;
		}
	}

	$node = array(
		'@type'            => 'CreativeWork',
		'@id'              => $url . '#creativework',
		'name'             => get_the_title( $post ),
		'description'      => isp_seo_excerpt( $post ),
		'url'              => $url,
		'creator'          => array( '@id' => isp_seo_org_id() ),
		'mainEntityOfPage' => array( '@id' => $url ),
	);
	if ( $images ) {
		$node['image'] = $images;
	}
	$location = (string) isp_meta( $post->ID, 'location' );
	if ( '' !== $location ) {
		$node['locationCreated'] = array(
			'@type' => 'Place',
			'name'  => $location,
		);
	}
	$year = (string) isp_meta( $post->ID, 'year' );
	if ( preg_match( '/^\d{4}$/', $year ) ) {
		$node['dateCreated'] = $year;
	}
	$type = isp_project_type( $post->ID );
	if ( $type ) {
		$node['genre'] = $type->name;
	}
	return $node;
}

/**
 * Article node for a single post (Next: Article JSON-LD, Organization as author and publisher).
 *
 * @param WP_Post $post Post.
 * @return array<string, mixed>
 */
function isp_seo_article_node( WP_Post $post ): array {
	$url   = get_permalink( $post );
	$node  = array(
		'@type'            => 'Article',
		'@id'              => $url . '#article',
		'headline'         => get_the_title( $post ),
		'description'      => isp_seo_excerpt( $post ),
		'url'              => $url,
		'datePublished'    => get_post_time( 'c', true, $post ),
		'dateModified'     => get_post_modified_time( 'c', true, $post ),
		'author'           => array( '@id' => isp_seo_org_id() ),
		'publisher'        => array( '@id' => isp_seo_org_id() ),
		'mainEntityOfPage' => array( '@id' => $url ),
		'inLanguage'       => get_bloginfo( 'language' ),
	);
	$image = get_the_post_thumbnail_url( $post, 'ist-hero' );
	if ( $image ) {
		$node['image'] = $image;
	}
	$cats = get_the_category( $post->ID );
	if ( $cats ) {
		$node['articleSection'] = wp_list_pluck( $cats, 'name' );
	}
	return $node;
}

/**
 * BreadcrumbList for a single service/project: Home › archive › item.
 *
 * @param WP_Post $post Post.
 * @return array<string, mixed>
 */
function isp_seo_breadcrumb_node( WP_Post $post ): array {
	$url   = get_permalink( $post );
	$items = array(
		array(
			'name' => __( 'Home', 'interior-specifics-core' ),
			'item' => trailingslashit( home_url() ),
		),
	);
	$type  = get_post_type_object( $post->post_type );
	$link  = get_post_type_archive_link( $post->post_type );
	if ( $type && $link ) {
		$items[] = array(
			'name' => $type->labels->name,
			'item' => $link,
		);
	}
	$items[] = array( 'name' => get_the_title( $post ) );

	$list = array();
	foreach ( $items as $i => $item ) {
		$list[] = array_merge(
			array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
			),
			$item
		);
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumb',
		'itemListElement' => $list,
	);
}

/*
 * -------------------------------------------------------------------------------------------
 * Yoast SEO integration
 * -------------------------------------------------------------------------------------------
 */

add_filter( 'wpseo_schema_graph', 'isp_seo_yoast_graph', 20, 2 );
/**
 * Merge the business details and the page-specific pieces into Yoast's single graph.
 *
 * - Organization: Yoast only emits it when a company logo is set; extend it to
 *   ['Organization','InteriorDesigner'] with address/telephone, or add it when missing,
 *   then point WebSite/WebPage/Article references at it. Never two Organization nodes.
 * - Service (+ FAQPage on the WebPage), CreativeWork and Article author as in Next.js.
 *
 * @param array<int, array<string, mixed>> $graph   Yoast graph pieces.
 * @param object                           $context Yoast Meta_Tags_Context.
 * @return array<int, array<string, mixed>>
 */
function isp_seo_yoast_graph( $graph, $context ): array {
	$graph  = is_array( $graph ) ? array_values( $graph ) : array();
	$org_id = isp_seo_org_id();
	$ours   = isp_seo_organization();

	// 1. One Organization node, typed InteriorDesigner, carrying the NAP details.
	$org_index = null;
	foreach ( $graph as $i => $piece ) {
		if ( ( $piece['@id'] ?? '' ) === $org_id || in_array( 'Organization', (array) ( $piece['@type'] ?? array() ), true ) ) {
			if ( null === $org_index ) {
				$org_index = $i;
			} else {
				unset( $graph[ $i ] ); // Defensive: never leave a second Organization.
			}
		}
	}
	if ( null === $org_index ) {
		$ours['@type'] = array( 'Organization', 'InteriorDesigner' );
		$graph[]       = $ours;
	} else {
		$merged          = array_merge( $ours, $graph[ $org_index ] );
		$merged['@type'] = array_values( array_unique( array_merge( (array) $graph[ $org_index ]['@type'], array( 'Organization', 'InteriorDesigner' ) ) ) );
		$merged['@id']   = $graph[ $org_index ]['@id'] ?? $org_id;
		if ( ! empty( $ours['sameAs'] ) ) {
			$merged['sameAs'] = array_values( array_unique( array_merge( (array) ( $graph[ $org_index ]['sameAs'] ?? array() ), $ours['sameAs'] ) ) );
		}
		$org_id              = $merged['@id'];
		$graph[ $org_index ] = $merged;
	}
	$graph = array_values( $graph );
	$ref   = array( '@id' => $org_id );

	// Outside the main query (REST yoast_head, sitemaps) Yoast's context carries the post itself.
	$post      = is_singular() ? get_queried_object() : ( $context->post ?? null );
	$post      = $post instanceof WP_Post ? $post : null;
	$canonical = (string) ( $context->canonical ?? '' );

	// 2. Wire references to the business and adjust page-specific pieces.
	$person_ids = array();
	foreach ( $graph as $i => $piece ) {
		$types = (array) ( $piece['@type'] ?? array() );

		if ( in_array( 'WebSite', $types, true ) && empty( $piece['publisher'] ) ) {
			$graph[ $i ]['publisher'] = $ref;
		}

		if ( in_array( 'WebPage', $types, true ) ) {
			if ( is_front_page() && empty( $piece['about'] ) ) {
				$graph[ $i ]['about'] = $ref;
			}
			// The studio, not the user account, authors every page (keeps login names out of the graph).
			if ( isset( $piece['author']['@id'] ) ) {
				$person_ids[]          = $piece['author']['@id'];
				$graph[ $i ]['author'] = $ref;
			}
			if ( $post && 'is_service' === $post->post_type ) {
				$questions = isp_seo_faq_questions( $post );
				if ( $questions ) {
					$graph[ $i ]['@type']      = array_values( array_unique( array_merge( $types, array( 'FAQPage' ) ) ) );
					$graph[ $i ]['mainEntity'] = $questions;
				}
			}
		}

		if ( in_array( 'Article', $types, true ) && $post && 'post' === $post->post_type ) {
			if ( isset( $piece['author']['@id'] ) ) {
				$person_ids[] = $piece['author']['@id'];
			}
			$graph[ $i ]['author']    = $ref;
			$graph[ $i ]['publisher'] = $ref;
			if ( empty( $piece['dateModified'] ) ) {
				$graph[ $i ]['dateModified'] = get_post_modified_time( 'c', true, $post );
			}
			if ( empty( $piece['description'] ) ) {
				$graph[ $i ]['description'] = isp_seo_excerpt( $post );
			}
		}
	}

	// Drop author Person pieces that nothing references any more (Next.js credits the studio).
	if ( $person_ids ) {
		foreach ( $graph as $i => $piece ) {
			if ( in_array( $piece['@id'] ?? '', $person_ids, true ) && in_array( 'Person', (array) ( $piece['@type'] ?? array() ), true ) ) {
				$rest = $graph;
				unset( $rest[ $i ] );
				if ( ! str_contains( (string) wp_json_encode( array_values( $rest ) ), (string) wp_json_encode( $piece['@id'] ) ) ) {
					unset( $graph[ $i ] );
				}
			}
		}
		$graph = array_values( $graph );
	}

	// 3. Page-specific pieces.
	if ( $post && 'is_service' === $post->post_type ) {
		$node = isp_seo_service_node( $post );
		if ( $canonical ) {
			$node['mainEntityOfPage'] = array( '@id' => $canonical );
		}
		$node['provider'] = $ref;
		$graph[]          = $node;
	} elseif ( $post && 'is_project' === $post->post_type ) {
		$node = isp_seo_project_node( $post );
		if ( $canonical ) {
			$node['mainEntityOfPage'] = array( '@id' => $canonical );
		}
		$node['creator'] = $ref;
		$graph[]         = $node;
	}

	return $graph;
}

add_filter( 'wpseo_meta_author', 'isp_seo_meta_author' );
/**
 * The studio, not the user account, is the author in <meta name="author"> (keeps login names out).
 */
function isp_seo_meta_author(): string {
	return isp_setting( 'name' );
}

add_filter( 'wpseo_enhanced_slack_data', 'isp_seo_slack_data' );
/**
 * Credit the studio in Slack's "Written by" preview instead of the user account.
 *
 * @param array<string, string> $data Label => value pairs.
 * @return array<string, string>
 */
function isp_seo_slack_data( $data ): array {
	$data = is_array( $data ) ? $data : array();
	foreach ( array_keys( $data ) as $label ) {
		if ( __( 'Written by', 'wordpress-seo' ) === $label ) {
			$data[ $label ] = isp_setting( 'name' );
		}
	}
	return $data;
}

add_filter( 'wpseo_opengraph_image', 'isp_seo_og_image_seen', PHP_INT_MAX );
/**
 * Remember the og:image URL Yoast is presenting: its type filter runs right after this one for
 * the same image but is not given the URL.
 *
 * @param string $url Image URL.
 */
function isp_seo_og_image_seen( $url ) {
	$GLOBALS['isp_seo_og_image_url'] = (string) $url;
	return $url;
}

add_filter( 'wpseo_opengraph_image_type', 'isp_seo_og_image_type' );
/**
 * og:image:type from the actual file: WebP sub-sizes of a JPEG upload keep the attachment's
 * image/jpeg MIME type, so Yoast would mislabel them.
 *
 * @param string $type MIME type Yoast derived.
 */
function isp_seo_og_image_type( $type ): string {
	$url  = (string) ( $GLOBALS['isp_seo_og_image_url'] ?? '' );
	$real = $url ? wp_check_filetype( (string) wp_parse_url( $url, PHP_URL_PATH ) )['type'] : '';
	return $real ? $real : (string) $type;
}

add_filter( 'wpseo_og_locale', 'isp_seo_og_locale' );
/**
 * Open Graph locale, as in the Next.js metadata.
 */
function isp_seo_og_locale(): string {
	/**
	 * Filters the Open Graph locale.
	 *
	 * @param string $locale Default en_NG.
	 */
	return (string) apply_filters( 'isp_seo_og_locale', 'en_NG' );
}

add_filter( 'wpseo_opengraph_title', 'isp_seo_yoast_project_og_title' );
/**
 * Projects share as "{title} — Interior Specifics" (Next.js openGraph.title) unless the editor
 * set a social title on the project.
 *
 * @param string $title Yoast OG title.
 */
function isp_seo_yoast_project_og_title( $title ) {
	$post = is_singular( 'is_project' ) ? get_queried_object() : null;
	if ( ! $post instanceof WP_Post || '' !== (string) get_post_meta( $post->ID, '_yoast_wpseo_opengraph-title', true ) ) {
		return $title;
	}
	return isp_seo_project_og_title( $post );
}

/**
 * Social title for a project from the social-title-is_project template.
 *
 * @param WP_Post $post Project.
 */
function isp_seo_project_og_title( WP_Post $post ): string {
	$template = (string) ( isp_seo_templates()['social-title-is_project'] ?? '' );
	if ( '' === trim( $template ) || '%%title%%' === trim( $template ) ) {
		$template = isp_seo_default_templates()['social-title-is_project'];
	}
	return isp_seo_replace_vars( $template, array( 'post' => $post ) );
}

add_filter( 'wpseo_sitemap_exclude_post_type', 'isp_seo_yoast_sitemap_exclude', 10, 2 );
/**
 * Keep private content types out of Yoast's sitemap.
 *
 * @param bool   $excluded  Whether excluded.
 * @param string $post_type Post type.
 */
function isp_seo_yoast_sitemap_exclude( $excluded, $post_type ) {
	return in_array( $post_type, array( 'is_testimonial', 'is_lead' ), true ) ? true : $excluded;
}

add_filter( 'wpseo_sitemap_exclude_taxonomy', 'isp_seo_yoast_sitemap_exclude_tax', 10, 2 );
/**
 * Keep project taxonomies out of Yoast's sitemap (see isp_seo_sitemap_excluded_taxonomies()).
 *
 * @param bool   $excluded Whether excluded.
 * @param string $taxonomy Taxonomy.
 */
function isp_seo_yoast_sitemap_exclude_tax( $excluded, $taxonomy ) {
	return in_array( $taxonomy, isp_seo_sitemap_excluded_taxonomies(), true ) ? true : $excluded;
}

/**
 * Taxonomies kept out of sitemaps. Like app/sitemap.ts, the showroom is listed once at
 * /showroom/ (it filters by ?type=), so project-type term archives are not submitted.
 *
 * @return string[]
 */
function isp_seo_sitemap_excluded_taxonomies(): array {
	/**
	 * Filters the taxonomies excluded from the XML sitemaps.
	 *
	 * @param string[] $taxonomies Taxonomy names.
	 */
	return (array) apply_filters( 'isp_seo_sitemap_excluded_taxonomies', array( 'is_project_scope', 'is_project_type' ) );
}

// The plugin prints project CreativeWork (in Yoast's graph or its own); silence the theme's copy.
add_filter( 'ist_project_schema', '__return_false' );

/*
 * -------------------------------------------------------------------------------------------
 * Core sitemaps (used when no SEO plugin replaces them)
 * -------------------------------------------------------------------------------------------
 */

add_filter( 'wp_sitemaps_post_types', 'isp_seo_sitemap_post_types' );
/**
 * Services, projects, posts and pages only; never testimonials or leads.
 *
 * @param array<string, WP_Post_Type> $post_types Post types.
 * @return array<string, WP_Post_Type>
 */
function isp_seo_sitemap_post_types( array $post_types ): array {
	unset( $post_types['is_testimonial'], $post_types['is_lead'], $post_types['attachment'] );
	return $post_types;
}

add_filter( 'wp_sitemaps_taxonomies', 'isp_seo_sitemap_taxonomies' );
/**
 * Exclude project taxonomies.
 *
 * @param array<string, WP_Taxonomy> $taxonomies Taxonomies.
 * @return array<string, WP_Taxonomy>
 */
function isp_seo_sitemap_taxonomies( array $taxonomies ): array {
	return array_diff_key( $taxonomies, array_flip( isp_seo_sitemap_excluded_taxonomies() ) );
}

add_filter( 'wp_sitemaps_add_provider', 'isp_seo_sitemap_providers', 10, 2 );
/**
 * A single-studio site has no meaningful author archives.
 *
 * @param WP_Sitemaps_Provider|false $provider Provider.
 * @param string                     $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function isp_seo_sitemap_providers( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}

add_filter( 'wp_sitemaps_posts_entry', 'isp_seo_sitemap_entry', 10, 3 );
/**
 * Priorities and change frequencies from app/sitemap.ts.
 *
 * @param array<string, string> $entry     Entry.
 * @param WP_Post               $post      Post.
 * @param string                $post_type Post type.
 * @return array<string, mixed>
 */
function isp_seo_sitemap_entry( array $entry, $post, string $post_type ): array {
	$id = $post instanceof WP_Post ? $post->ID : 0;
	if ( 'page' === $post_type ) {
		$front               = (int) get_option( 'page_on_front' ) === $id;
		$entry['changefreq'] = 'monthly';
		$entry['priority']   = $front ? '1.0' : '0.8';
	} elseif ( in_array( $post_type, array( 'is_service', 'is_project' ), true ) ) {
		$entry['changefreq'] = 'monthly';
		$entry['priority']   = '0.7';
	} elseif ( 'post' === $post_type ) {
		$entry['changefreq'] = 'yearly';
		$entry['priority']   = '0.6';
	}
	return $entry;
}

add_action( 'init', 'isp_seo_register_archive_sitemap' );
/**
 * Core sitemaps list singular URLs only; app/sitemap.ts also listed /services and /showroom.
 * Yoast adds archive URLs to its own post type sitemaps, so this is core-only.
 */
function isp_seo_register_archive_sitemap(): void {
	if ( ! function_exists( 'wp_register_sitemap_provider' ) || ! class_exists( 'WP_Sitemaps_Provider' ) || isp_seo_yoast_active() ) {
		return;
	}
	if ( ! class_exists( 'ISP_Sitemaps_Archives' ) ) {
		/**
		 * Sitemap of the public post type archive pages (/services/, /showroom/).
		 */
		class ISP_Sitemaps_Archives extends WP_Sitemaps_Provider {

			/**
			 * Set the provider name and object type.
			 */
			public function __construct() {
				$this->name        = 'archives';
				$this->object_type = 'archive';
			}

			/**
			 * Archive URLs.
			 *
			 * @param int    $page_num       Page of results.
			 * @param string $object_subtype Unused.
			 * @return array<int, array<string, string>>
			 */
			public function get_url_list( $page_num, $object_subtype = '' ) {
				unset( $object_subtype );
				if ( (int) $page_num > 1 ) {
					return array();
				}
				$urls = array();
				foreach ( array( 'is_service', 'is_project' ) as $post_type ) {
					$link = get_post_type_archive_link( $post_type );
					if ( $link ) {
						$urls[] = array(
							'loc'        => $link,
							'changefreq' => 'monthly',
							'priority'   => '0.8',
						);
					}
				}
				return $urls;
			}

			/**
			 * One page.
			 *
			 * @param string $object_subtype Unused.
			 */
			public function get_max_num_pages( $object_subtype = '' ) {
				unset( $object_subtype );
				return 1;
			}
		}
	}
	wp_register_sitemap_provider( 'archives', new ISP_Sitemaps_Archives() );
}

/*
 * -------------------------------------------------------------------------------------------
 * Head output when no SEO plugin is active
 * -------------------------------------------------------------------------------------------
 */

/**
 * Everything the head needs for the current request: title, description, social title,
 * canonical URL, OG type and image. Computed once per request.
 *
 * @return array{title: string, description: string, og_title: string, url: string, type: string, image: array<string, mixed>}
 */
function isp_seo_current(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	global $wp_query;
	$t        = isp_seo_templates();
	$paged    = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ), 1 );
	$base_ctx = array(
		'page'     => $paged,
		'max_page' => (int) ( $wp_query->max_num_pages ?? 0 ),
	);
	$title    = '';
	$desc     = '';
	$og_title = '';
	$url      = '';
	$type     = 'website';
	$image_id = 0;
	$og_desc  = '';
	$post     = null;

	$meta = static function ( WP_Post $post, string $key ): string {
		return trim( (string) get_post_meta( $post->ID, '_yoast_wpseo_' . $key, true ) );
	};

	if ( is_front_page() || is_home() || is_singular() ) {
		$id   = is_front_page() && 'page' === get_option( 'show_on_front' ) ? (int) get_option( 'page_on_front' ) : ( is_home() ? (int) get_option( 'page_for_posts' ) : get_queried_object_id() );
		$post = $id ? get_post( $id ) : null;
	}

	if ( $post instanceof WP_Post ) {
		$ctx = array_merge( $base_ctx, array( 'post' => $post ) );
		if ( is_front_page() ) {
			$title_tpl = $meta( $post, 'title' ) ? $meta( $post, 'title' ) : (string) ( $t['title-home-wpseo'] ?? '' );
			$desc_tpl  = $meta( $post, 'metadesc' ) ? $meta( $post, 'metadesc' ) : (string) ( $t['metadesc-home-wpseo'] ?? '' );
		} else {
			$pt        = is_home() ? 'page' : $post->post_type;
			$title_tpl = $meta( $post, 'title' ) ? $meta( $post, 'title' ) : (string) ( $t[ 'title-' . $pt ] ?? '%%title%% %%page%% %%sep%% %%sitename%%' );
			$desc_tpl  = $meta( $post, 'metadesc' ) ? $meta( $post, 'metadesc' ) : (string) ( $t[ 'metadesc-' . $pt ] ?? '' );
		}
		$title    = isp_seo_replace_vars( $title_tpl, $ctx );
		$desc     = isp_seo_replace_vars( $desc_tpl, $ctx );
		$og_title = $meta( $post, 'opengraph-title' ) ? isp_seo_replace_vars( $meta( $post, 'opengraph-title' ), $ctx ) : '';
		if ( '' === $og_title && 'is_project' === $post->post_type && ! is_home() && ! is_front_page() ) {
			$og_title = isp_seo_project_og_title( $post );
		}
		if ( $meta( $post, 'opengraph-description' ) ) {
			$og_desc = isp_seo_replace_vars( $meta( $post, 'opengraph-description' ), $ctx );
		}
		$url      = is_home() || is_front_page() ? ( is_front_page() ? trailingslashit( home_url() ) : (string) get_permalink( $post ) ) : (string) wp_get_canonical_url( $post );
		$type     = ( ! is_home() && ! is_front_page() && in_array( $post->post_type, array( 'post', 'is_project' ), true ) ) ? 'article' : 'website';
		$image_id = ( is_home() || is_front_page() ) ? 0 : (int) get_post_thumbnail_id( $post );
		if ( is_home() && $paged > 1 ) {
			$url = (string) get_pagenum_link( $paged );
		}
	} elseif ( is_front_page() ) {
		// Front page showing latest posts (no static page).
		$title = isp_seo_replace_vars( (string) ( $t['title-home-wpseo'] ?? '' ), $base_ctx );
		$desc  = isp_seo_replace_vars( (string) ( $t['metadesc-home-wpseo'] ?? '' ), $base_ctx );
		$url   = $paged > 1 ? (string) get_pagenum_link( $paged ) : trailingslashit( home_url() );
	} elseif ( is_post_type_archive() ) {
		$pt    = get_query_var( 'post_type' );
		$pt    = is_array( $pt ) ? (string) reset( $pt ) : (string) $pt;
		$ctx   = array_merge( $base_ctx, array( 'post_type' => $pt ) );
		$title = isp_seo_replace_vars( (string) ( $t[ 'title-ptarchive-' . $pt ] ?? '%%pt_plural%% %%page%% %%sep%% %%sitename%%' ), $ctx );
		$desc  = isp_seo_replace_vars( (string) ( $t[ 'metadesc-ptarchive-' . $pt ] ?? '' ), $ctx );
		$url   = $paged > 1 ? (string) get_pagenum_link( $paged ) : (string) get_post_type_archive_link( $pt );
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$ctx       = array_merge( $base_ctx, array( 'term' => $term ) );
			$term_meta = get_option( 'wpseo_taxonomy_meta', array() );
			$term_meta = is_array( $term_meta ) ? ( $term_meta[ $term->taxonomy ][ $term->term_id ] ?? array() ) : array();
			$title_tpl = ! empty( $term_meta['wpseo_title'] ) ? $term_meta['wpseo_title'] : (string) ( $t[ 'title-tax-' . $term->taxonomy ] ?? '%%term_title%% %%page%% %%sep%% %%sitename%%' );
			$desc_tpl  = ! empty( $term_meta['wpseo_desc'] ) ? $term_meta['wpseo_desc'] : (string) ( $t[ 'metadesc-tax-' . $term->taxonomy ] ?? '%%term_description%%' );
			$title     = isp_seo_replace_vars( $title_tpl, $ctx );
			$desc      = isp_seo_replace_vars( $desc_tpl, $ctx );
			$link      = get_term_link( $term );
			$url       = $paged > 1 ? (string) get_pagenum_link( $paged ) : ( is_wp_error( $link ) ? '' : $link );
		}
	} elseif ( is_search() ) {
		$title = isp_seo_replace_vars( (string) ( $t['title-search-wpseo'] ?? '' ), $base_ctx );
	} elseif ( is_404() ) {
		$title = isp_seo_replace_vars( (string) ( $t['title-404-wpseo'] ?? '' ), $base_ctx );
	} elseif ( is_author() || is_date() ) {
		$ctx   = array_merge( $base_ctx, array( 'title' => wp_strip_all_tags( get_the_archive_title() ) ) );
		$title = isp_seo_replace_vars( (string) ( $t[ is_author() ? 'title-author-wpseo' : 'title-archive-wpseo' ] ?? '' ), $ctx );
	}

	if ( '' === $desc ) {
		$desc = isp_setting( 'description' );
	}

	// Image: the item's own image, else the Yoast default OG image when one is configured.
	$image = array();
	if ( ! $image_id ) {
		$social   = get_option( 'wpseo_social', array() );
		$image_id = is_array( $social ) ? (int) ( $social['og_default_image_id'] ?? 0 ) : 0;
	}
	if ( $image_id ) {
		$src = wp_get_attachment_image_src( $image_id, 'ist-hero' );
		if ( $src ) {
			$image = array(
				'url'    => $src[0],
				'width'  => (int) $src[1],
				'height' => (int) $src[2],
				'alt'    => trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ),
				'type'   => (string) get_post_mime_type( $image_id ),
			);
		}
	}

	$cache = array(
		'title'       => $title,
		'description' => wp_strip_all_tags( $desc ),
		'og_title'    => '' !== $og_title ? $og_title : $title,
		'og_desc'     => '' !== $og_desc ? wp_strip_all_tags( $og_desc ) : wp_strip_all_tags( $desc ),
		'url'         => $url,
		'type'        => $type,
		'image'       => $image,
	);
	return $cache;
}

add_filter( 'pre_get_document_title', 'isp_seo_document_title', 20 );
/**
 * The full <title> when no SEO plugin is active.
 *
 * @param string $title Title from earlier filters.
 */
function isp_seo_document_title( $title ) {
	if ( ! isp_seo_handles_head() || is_feed() ) {
		return $title;
	}
	$ours = isp_seo_current()['title'];
	return '' !== $ours ? $ours : $title;
}

add_action( 'wp_head', 'isp_seo_head_tags', 2 );
/**
 * Meta description, canonical (non-singular views), Open Graph and Twitter tags.
 */
function isp_seo_head_tags(): void {
	if ( ! isp_seo_handles_head() ) {
		return;
	}
	$seo = isp_seo_current();

	if ( '' !== $seo['description'] ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $seo['description'] ) );
	}
	// Core prints rel=canonical on singular views only.
	if ( $seo['url'] && ! is_singular() ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $seo['url'] ) );
	}

	$og = array(
		'og:locale'      => isp_seo_og_locale(),
		'og:type'        => $seo['type'],
		'og:title'       => $seo['og_title'],
		'og:description' => $seo['og_desc'],
		'og:url'         => $seo['url'],
		'og:site_name'   => get_bloginfo( 'name' ),
	);
	if ( $seo['image'] ) {
		$og['og:image']        = $seo['image']['url'];
		$og['og:image:width']  = (string) $seo['image']['width'];
		$og['og:image:height'] = (string) $seo['image']['height'];
		$og['og:image:type']   = $seo['image']['type'];
		$og['og:image:alt']    = $seo['image']['alt'];
	}
	if ( 'article' === $seo['type'] && is_singular() ) {
		$og['article:published_time'] = (string) get_post_time( 'c', true, get_queried_object() );
		$og['article:modified_time']  = (string) get_post_modified_time( 'c', true, get_queried_object() );
	}
	foreach ( array_filter( $og, 'strlen' ) as $property => $content ) {
		printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), 'og:url' === $property || 'og:image' === $property ? esc_url( $content ) : esc_attr( $content ) );
	}

	$twitter = array(
		'twitter:card'        => 'summary_large_image',
		'twitter:title'       => $seo['og_title'],
		'twitter:description' => $seo['og_desc'],
		'twitter:image'       => $seo['image']['url'] ?? '',
	);
	foreach ( array_filter( $twitter, 'strlen' ) as $name => $content ) {
		printf( '<meta name="%s" content="%s">' . "\n", esc_attr( $name ), 'twitter:image' === $name ? esc_url( $content ) : esc_attr( $content ) );
	}
}

add_action( 'wp_head', 'isp_seo_service_keywords', 3 );
/**
 * Next.js emitted `keywords` on service pages. No SEO plugin prints this tag, so it is safe in
 * both modes.
 */
function isp_seo_service_keywords(): void {
	if ( ! is_singular( 'is_service' ) ) {
		return;
	}
	$keywords = array_filter( array_map( 'trim', array_map( 'strval', (array) isp_meta( get_queried_object_id(), 'seo_keywords' ) ) ) );
	if ( $keywords ) {
		printf( '<meta name="keywords" content="%s">' . "\n", esc_attr( implode( ', ', $keywords ) ) );
	}
}

add_action( 'wp_head', 'isp_seo_print_schema', 30 );
/**
 * One JSON-LD @graph when no SEO plugin provides a graph to merge into.
 */
function isp_seo_print_schema(): void {
	if ( ! isp_seo_handles_head() ) {
		return;
	}
	$graph = isp_seo_fallback_graph();
	if ( ! $graph ) {
		return;
	}
	printf(
		'<script type="application/ld+json" class="isp-schema">%s</script>' . "\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
		)
	);
}

/**
 * Graph for the current request: business, website, and the page's own entities.
 *
 * @return array<int, array<string, mixed>>
 */
function isp_seo_fallback_graph(): array {
	$home  = trailingslashit( home_url() );
	$graph = array(
		isp_seo_organization(),
		array(
			'@type'      => 'WebSite',
			'@id'        => $home . '#website',
			'url'        => $home,
			'name'       => get_bloginfo( 'name' ),
			'publisher'  => array( '@id' => isp_seo_org_id() ),
			'inLanguage' => get_bloginfo( 'language' ),
		),
	);

	$post = is_singular() ? get_queried_object() : null;
	if ( ! $post instanceof WP_Post ) {
		return $graph;
	}

	switch ( $post->post_type ) {
		case 'is_service':
			$graph[]   = isp_seo_service_node( $post );
			$questions = isp_seo_faq_questions( $post );
			if ( $questions ) {
				$graph[] = array(
					'@type'      => 'FAQPage',
					'@id'        => get_permalink( $post ) . '#faq',
					'url'        => get_permalink( $post ),
					'isPartOf'   => array( '@id' => $home . '#website' ),
					'mainEntity' => $questions,
				);
			}
			$graph[] = isp_seo_breadcrumb_node( $post );
			break;
		case 'is_project':
			$graph[] = isp_seo_project_node( $post );
			$graph[] = isp_seo_breadcrumb_node( $post );
			break;
		case 'post':
			$graph[] = isp_seo_article_node( $post );
			break;
	}

	/**
	 * Filters the fallback JSON-LD graph (only used when no SEO plugin is active).
	 *
	 * @param array<int, array<string, mixed>> $graph Graph nodes.
	 * @param WP_Post                          $post  Current post.
	 */
	return (array) apply_filters( 'isp_seo_fallback_graph', $graph, $post );
}

/*
 * -------------------------------------------------------------------------------------------
 * WP-CLI: wp is seo-defaults
 * -------------------------------------------------------------------------------------------
 */

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	/**
	 * Configure titles, descriptions and social defaults to mirror the Next.js metadata.
	 */
	class ISP_CLI_SEO_Defaults {

		/**
		 * Apply SEO defaults: Yoast templates and settings, plus per-page titles and descriptions.
		 *
		 * Safe to re-run. Yoast templates are always (re)applied; per-page titles and descriptions are only written where empty unless --force is given, so editors' changes survive.
		 *
		 * ## OPTIONS
		 *
		 * [--force]
		 * : Overwrite existing per-page SEO titles and descriptions.
		 *
		 * ## EXAMPLES
		 *
		 *     wp is seo-defaults
		 *     wp is seo-defaults --force
		 *
		 * @param array<int, string>   $args       Positional args (unused).
		 * @param array<string, mixed> $assoc_args Flags.
		 */
		public function __invoke( array $args, array $assoc_args ): void {
			unset( $args );
			$force = (bool) \WP_CLI\Utils\get_flag_value( $assoc_args, 'force', false );

			$this->configure_yoast();
			$this->configure_pages( $force );

			WP_CLI::success( 'SEO defaults applied.' );
		}

		/**
		 * Yoast search appearance + social options.
		 */
		private function configure_yoast(): void {
			$titles_exist = false !== get_option( 'wpseo_titles', false );
			if ( ! isp_seo_yoast_active() && ! $titles_exist ) {
				WP_CLI::log( 'Yoast SEO is not active: skipping its settings (the plugin\'s built-in templates apply).' );
				return;
			}

			$titles = get_option( 'wpseo_titles', array() );
			$titles = is_array( $titles ) ? $titles : array();
			$wanted = isp_seo_default_templates();

			// Business identity: fill only when empty so a Yoast-configured name/logo is kept.
			$name = isp_setting( 'name' );
			foreach ( array( 'company_name', 'website_name' ) as $key ) {
				if ( empty( $titles[ $key ] ) ) {
					$wanted[ $key ] = $name;
				}
			}
			$wanted['company_or_person'] = 'company';
			$logo_id                     = (int) get_theme_mod( 'custom_logo' );
			$logo_id                     = $logo_id ? $logo_id : (int) get_option( 'site_icon' );
			if ( $logo_id && empty( $titles['company_logo_id'] ) ) {
				$wanted['company_logo']    = (string) wp_get_attachment_url( $logo_id );
				$wanted['company_logo_id'] = $logo_id;
			}

			update_option( 'wpseo_titles', array_merge( $titles, $wanted ) );
			WP_CLI::log( sprintf( 'Yoast titles: %d settings applied.', count( $wanted ) ) );

			$social = get_option( 'wpseo_social', array() );
			$social = is_array( $social ) ? $social : array();

			$social['opengraph']         = true;
			$social['twitter']           = true;
			$social['twitter_card_type'] = 'summary_large_image';

			$profiles = array_values( array_filter( array_values( isp_socials() ), 'isp_seo_is_profile_url' ) );
			if ( $profiles ) {
				$social['other_social_urls'] = array_values( array_unique( array_merge( (array) ( $social['other_social_urls'] ?? array() ), $profiles ) ) );
			}

			// Default share image (the source had none): the first project's hero image.
			if ( empty( $social['og_default_image_id'] ) ) {
				$project = get_posts(
					array(
						'post_type'      => 'is_project',
						'posts_per_page' => 1,
						'orderby'        => array(
							'menu_order' => 'ASC',
							'date'       => 'DESC',
						),
						'meta_key'       => '_thumbnail_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- one-off CLI query.
						'no_found_rows'  => true,
					)
				);
				$thumb   = $project ? (int) get_post_thumbnail_id( $project[0] ) : 0;
				if ( $thumb ) {
					$social['og_default_image']    = (string) wp_get_attachment_url( $thumb );
					$social['og_default_image_id'] = $thumb;
				}
			}
			update_option( 'wpseo_social', $social );
			WP_CLI::log( 'Yoast social: Open Graph + Twitter cards on, default share image set.' );
		}

		/**
		 * Per-page SEO title / description (stored as Yoast post meta, also read by the fallback).
		 *
		 * @param bool $force Overwrite existing values.
		 */
		private function configure_pages( bool $force ): void {
			$sep  = ' %%sep%% %%sitename%%';
			$home = (int) get_option( 'page_on_front' );
			$blog = (int) get_option( 'page_for_posts' );

			$pages = array(
				'home'    => array(
					'id'       => $home,
					'title'    => isp_seo_default_templates()['title-home-wpseo'],
					'metadesc' => isp_setting( 'description' ),
					'og'       => '%%sitename%% — ' . isp_setting( 'tagline' ),
				),
				'about'   => array(
					'title'    => __( 'About — Crafting Legacies Through Design', 'interior-specifics-core' ) . $sep,
					'metadesc' => __( "Meet the team behind Interior Specifics, Lagos' premier luxury interior design firm. Our story, values, leadership and the Interior Specifics Method™.", 'interior-specifics-core' ),
				),
				'book'    => array(
					'title'    => __( 'Book a Consultation — Private & Complimentary', 'interior-specifics-core' ) . $sep,
					'metadesc' => __( "Book your complimentary 30-minute design consultation with Interior Specifics, Lagos' premier luxury interior design firm. In-person, virtual or by phone.", 'interior-specifics-core' ),
				),
				'contact' => array(
					'title'    => __( "Contact — Let's Create Something Exceptional", 'interior-specifics-core' ) . $sep,
					'metadesc' => __( "Get in touch with Interior Specifics, Lagos' premier luxury interior design firm. Book a consultation or send us your project details.", 'interior-specifics-core' ),
				),
				'privacy' => array(
					'metadesc' => __( 'How Interior Specifics collects, uses and protects your information.', 'interior-specifics-core' ),
				),
				'terms'   => array(
					'metadesc' => __( 'The terms governing use of the Interior Specifics website.', 'interior-specifics-core' ),
				),
				'blog'    => array(
					'id'       => $blog,
					'title'    => __( 'Journal — Design Insights & Luxury Living', 'interior-specifics-core' ) . ' %%page%%' . $sep,
					'metadesc' => __( 'Expert advice for creating exceptional spaces. Insights on luxury interior design, materials, trends and budgeting from Interior Specifics, Lagos.', 'interior-specifics-core' ),
				),
			);

			foreach ( $pages as $slug => $fields ) {
				$id = (int) ( $fields['id'] ?? 0 );
				if ( ! $id ) {
					$page = get_page_by_path( $slug );
					$id   = $page instanceof WP_Post ? (int) $page->ID : 0;
				}
				if ( ! $id ) {
					WP_CLI::warning( sprintf( 'Page "%s" not found; skipped.', $slug ) );
					continue;
				}
				$map = array(
					'title'    => '_yoast_wpseo_title',
					'metadesc' => '_yoast_wpseo_metadesc',
					'og'       => '_yoast_wpseo_opengraph-title',
				);
				$set = array();
				foreach ( $map as $field => $meta_key ) {
					if ( empty( $fields[ $field ] ) ) {
						continue;
					}
					$current = (string) get_post_meta( $id, $meta_key, true );
					if ( '' !== $current && ! $force ) {
						continue;
					}
					if ( $current !== $fields[ $field ] ) {
						update_post_meta( $id, $meta_key, wp_slash( $fields[ $field ] ) );
						$set[] = $field;
					}
				}
				WP_CLI::log( sprintf( '%-8s #%d: %s', $slug, $id, $set ? 'set ' . implode( ', ', $set ) : 'unchanged' ) );
			}

			// Services: Next.js used the short description (excerpt) — the metadesc-is_service
			// template (%%excerpt%%) keeps that in sync; report any service without an excerpt.
			$missing = get_posts(
				array(
					'post_type'      => 'is_service',
					'posts_per_page' => -1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				)
			);
			$missing = array_filter( $missing, static fn( $id ) => '' === trim( (string) get_post_field( 'post_excerpt', $id ) ) );
			if ( $missing ) {
				WP_CLI::warning( sprintf( 'Services without an excerpt (meta description falls back to content): %s', implode( ', ', $missing ) ) );
			}
		}
	}

	WP_CLI::add_command( 'is seo-defaults', 'ISP_CLI_SEO_Defaults' );
}
