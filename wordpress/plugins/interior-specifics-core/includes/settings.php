<?php
/**
 * Settings → Interior Specifics: business details, integrations and lead delivery.
 * Replaces the Next.js lib/site.ts constants. Stored as one option, `isp_settings`.
 *
 * @package InteriorSpecificsCore
 */

defined( 'ABSPATH' ) || exit;

/**
 * Field definitions grouped into sections. Type drives sanitisation and the input rendered.
 *
 * @return array<string, array{title: string, description?: string, fields: array<string, array{label: string, type: string, help?: string}>}>
 */
function isp_settings_schema(): array {
	return array(
		'brand'   => array(
			'title'  => __( 'Brand', 'interior-specifics-core' ),
			'fields' => array(
				'name'              => array(
					'label' => __( 'Business name', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'legal_name'        => array(
					'label' => __( 'Legal name', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'tagline'           => array(
					'label' => __( 'Tagline', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'secondary_tagline' => array(
					'label' => __( 'Secondary tagline', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'description'       => array(
					'label' => __( 'Short description', 'interior-specifics-core' ),
					'type'  => 'textarea',
					'help'  => __( 'Used for default meta descriptions and structured data.', 'interior-specifics-core' ),
				),
				'founded_year'      => array(
					'label' => __( 'Founded year', 'interior-specifics-core' ),
					'type'  => 'text',
				),
			),
		),
		'contact' => array(
			'title'  => __( 'Contact details', 'interior-specifics-core' ),
			'fields' => array(
				'email'            => array(
					'label' => __( 'Public email', 'interior-specifics-core' ),
					'type'  => 'email',
				),
				'phone_display'    => array(
					'label' => __( 'Phone (as displayed)', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'phone_href'       => array(
					'label' => __( 'Phone (dialable, e.g. +2348012345678)', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'whatsapp'         => array(
					'label' => __( 'WhatsApp number', 'interior-specifics-core' ),
					'type'  => 'text',
					'help'  => __( 'Digits only, with country code, e.g. 2348012345678.', 'interior-specifics-core' ),
				),
				'whatsapp_message' => array(
					'label' => __( 'WhatsApp greeting', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'address_street'   => array(
					'label' => __( 'Street address', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'address_city'     => array(
					'label' => __( 'City', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'address_country'  => array(
					'label' => __( 'Country', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'hours'            => array(
					'label' => __( 'Opening hours', 'interior-specifics-core' ),
					'type'  => 'text',
				),
			),
		),
		'stats'   => array(
			'title'       => __( 'Headline stats', 'interior-specifics-core' ),
			'description' => __( 'Shown in the homepage hero. Numbers count up on scroll.', 'interior-specifics-core' ),
			'fields'      => array(
				'stat_1_value' => array(
					'label' => __( 'Stat 1 value', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'stat_1_label' => array(
					'label' => __( 'Stat 1 label', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'stat_2_value' => array(
					'label' => __( 'Stat 2 value', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'stat_2_label' => array(
					'label' => __( 'Stat 2 label', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'stat_3_value' => array(
					'label' => __( 'Stat 3 value', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'stat_3_label' => array(
					'label' => __( 'Stat 3 label', 'interior-specifics-core' ),
					'type'  => 'text',
				),
			),
		),
		'booking' => array(
			'title'  => __( 'Booking & integrations', 'interior-specifics-core' ),
			'fields' => array(
				'booking_url'      => array(
					'label' => __( 'Booking page URL', 'interior-specifics-core' ),
					'type'  => 'url_or_path',
					'help'  => __( 'Where “Book Consultation” buttons point. A path like /book/ or a full URL.', 'interior-specifics-core' ),
				),
				'header_cta_label' => array(
					'label' => __( 'Header button label', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'calendly_url'     => array(
					'label' => __( 'Calendly scheduling URL', 'interior-specifics-core' ),
					'type'  => 'url',
					'help'  => __( 'e.g. https://calendly.com/your-handle/consultation — shown on the booking page when set.', 'interior-specifics-core' ),
				),
				'map_embed_url'    => array(
					'label' => __( 'Google Maps embed URL', 'interior-specifics-core' ),
					'type'  => 'url',
					'help'  => __( 'Google Maps → Share → Embed a map → copy the src="…" URL.', 'interior-specifics-core' ),
				),
				'ga4_id'           => array(
					'label' => __( 'Google Analytics 4 measurement ID', 'interior-specifics-core' ),
					'type'  => 'ga4',
					'help'  => __( 'e.g. G-XXXXXXXXXX. Leave empty to disable analytics.', 'interior-specifics-core' ),
				),
			),
		),
		'socials' => array(
			'title'  => __( 'Social profiles', 'interior-specifics-core' ),
			'fields' => array(
				'social_instagram' => array(
					'label' => 'Instagram',
					'type'  => 'url',
				),
				'social_linkedin'  => array(
					'label' => 'LinkedIn',
					'type'  => 'url',
				),
				'social_pinterest' => array(
					'label' => 'Pinterest',
					'type'  => 'url',
				),
				'social_youtube'   => array(
					'label' => 'YouTube',
					'type'  => 'url',
				),
			),
		),
		'heroes'  => array(
			'title'       => __( 'Page heroes', 'interior-specifics-core' ),
			'description' => __( 'Background images for pages that have no editable hero of their own.', 'interior-specifics-core' ),
			'fields'      => array(
				'hero_services' => array(
					'label' => __( 'Services archive', 'interior-specifics-core' ),
					'type'  => 'image',
				),
				'hero_showroom' => array(
					'label' => __( 'Showroom archive', 'interior-specifics-core' ),
					'type'  => 'image',
				),
				'hero_journal'  => array(
					'label' => __( 'Journal', 'interior-specifics-core' ),
					'type'  => 'image',
				),
				'hero_default'  => array(
					'label' => __( 'Other pages (search, archives, fallback)', 'interior-specifics-core' ),
					'type'  => 'image',
				),
			),
		),
		'leads'   => array(
			'title'       => __( 'Lead delivery', 'interior-specifics-core' ),
			'description' => __( 'Every form submission is saved under Leads and emailed here. Email is sent through Resend when the RESEND_API_KEY constant is defined in wp-config.php, otherwise through the server mailer.', 'interior-specifics-core' ),
			'fields'      => array(
				'lead_to_email'   => array(
					'label' => __( 'Send leads to', 'interior-specifics-core' ),
					'type'  => 'email',
					'help'  => __( 'Defaults to the public email.', 'interior-specifics-core' ),
				),
				'lead_from_email' => array(
					'label' => __( 'From address', 'interior-specifics-core' ),
					'type'  => 'email',
					'help'  => __( 'Must be on a domain verified in Resend, e.g. hello@interiorspecifics.com.', 'interior-specifics-core' ),
				),
				'lead_from_name'  => array(
					'label' => __( 'From name', 'interior-specifics-core' ),
					'type'  => 'text',
				),
				'guide_url'       => array(
					'label' => __( 'Free guide PDF URL', 'interior-specifics-core' ),
					'type'  => 'media',
					'help'  => __( 'Emailed to people who request the guide. Upload the PDF to the Media Library and select it.', 'interior-specifics-core' ),
				),
			),
		),
	);
}

add_action( 'admin_init', 'isp_register_settings' );
/**
 * Register the option with a sanitiser.
 */
function isp_register_settings(): void {
	register_setting(
		'isp_settings',
		'isp_settings',
		array(
			'type'              => 'object',
			'sanitize_callback' => 'isp_sanitize_settings',
			'default'           => array(),
			'show_in_rest'      => false,
		)
	);
}

/**
 * Sanitise every known field by type; unknown keys are dropped.
 *
 * @param mixed $input Raw submitted values.
 * @return array<string, string>
 */
function isp_sanitize_settings( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$clean = array();
	foreach ( isp_settings_schema() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$raw = isset( $input[ $key ] ) ? (string) $input[ $key ] : '';
			switch ( $field['type'] ) {
				case 'email':
					$clean[ $key ] = sanitize_email( $raw );
					break;
				case 'url':
				case 'media':
					$clean[ $key ] = esc_url_raw( trim( $raw ) );
					break;
				case 'url_or_path':
					$raw           = trim( $raw );
					$clean[ $key ] = str_starts_with( $raw, '/' ) ? '/' . ltrim( sanitize_text_field( $raw ), '/' ) : esc_url_raw( $raw );
					break;
				case 'ga4':
					$raw           = strtoupper( trim( $raw ) );
					$clean[ $key ] = preg_match( '/^G-[A-Z0-9]{4,20}$/', $raw ) ? $raw : '';
					break;
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( $raw );
					break;
				case 'image':
					$clean[ $key ] = absint( $raw ) && wp_attachment_is_image( absint( $raw ) ) ? (string) absint( $raw ) : '';
					break;
				default:
					$clean[ $key ] = sanitize_text_field( $raw );
			}
		}
	}
	return $clean;
}

add_action( 'admin_menu', 'isp_add_settings_page' );
/**
 * Settings → Interior Specifics.
 */
function isp_add_settings_page(): void {
	$hook = add_options_page(
		__( 'Interior Specifics', 'interior-specifics-core' ),
		__( 'Interior Specifics', 'interior-specifics-core' ),
		'manage_options',
		'isp-settings',
		'isp_render_settings_page'
	);
	add_action( "admin_print_scripts-{$hook}", 'wp_enqueue_media' );
}

/**
 * Render the settings form.
 */
function isp_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$values = isp_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Interior Specifics', 'interior-specifics-core' ); ?></h1>
		<p><?php esc_html_e( 'Business details used across the header, footer, contact page, booking page, structured data and lead emails.', 'interior-specifics-core' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'isp_settings' ); ?>
			<?php foreach ( isp_settings_schema() as $section ) : ?>
				<h2 class="title"><?php echo esc_html( $section['title'] ); ?></h2>
				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>
				<table class="form-table" role="presentation">
					<?php
					foreach ( $section['fields'] as $key => $field ) :
						$id    = 'isp_' . $key;
						$name  = 'isp_settings[' . $key . ']';
						$value = (string) ( $values[ $key ] ?? '' );
						?>
						<tr>
							<th scope="row"><label for="<?php echo esc_attr( 'image' === $field['type'] ? $id . '_pick' : $id ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td>
								<?php if ( 'image' === $field['type'] ) : ?>
									<?php $preview = $value ? wp_get_attachment_image_url( (int) $value, 'medium' ) : ''; ?>
									<div class="isp-image-field">
										<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="hidden" value="<?php echo esc_attr( $value ); ?>">
										<img class="isp-image-preview" src="<?php echo esc_url( (string) $preview ); ?>" alt="" style="display:<?php echo $preview ? 'block' : 'none'; ?>;max-width:320px;max-height:200px;width:auto;height:auto;margin-bottom:8px;border:1px solid #c3c4c7">
										<button type="button" id="<?php echo esc_attr( $id . '_pick' ); ?>" class="button isp-image-pick" data-target="<?php echo esc_attr( $id ); ?>"><?php echo $preview ? esc_html__( 'Replace image', 'interior-specifics-core' ) : esc_html__( 'Select image', 'interior-specifics-core' ); ?></button>
										<button type="button" class="button-link button-link-delete isp-image-remove" data-target="<?php echo esc_attr( $id ); ?>" style="margin-left:8px;<?php echo $preview ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove', 'interior-specifics-core' ); ?></button>
									</div>
								<?php elseif ( 'textarea' === $field['type'] ) : ?>
									<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" rows="3" class="large-text"><?php echo esc_textarea( $value ); ?></textarea>
								<?php else : ?>
									<input id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" type="<?php echo esc_attr( 'email' === $field['type'] ? 'email' : ( 'url' === $field['type'] || 'media' === $field['type'] ? 'url' : 'text' ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
									<?php if ( 'media' === $field['type'] ) : ?>
										<button type="button" class="button isp-media-pick" data-target="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Select file', 'interior-specifics-core' ); ?></button>
									<?php endif; ?>
								<?php endif; ?>
								<?php if ( ! empty( $field['help'] ) ) : ?>
									<p class="description"><?php echo esc_html( $field['help'] ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endforeach; ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<script>
	document.querySelectorAll( '.isp-media-pick' ).forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			var frame = wp.media( { title: button.textContent, multiple: false } );
			frame.on( 'select', function () {
				document.getElementById( button.dataset.target ).value = frame.state().get( 'selection' ).first().toJSON().url;
			} );
			frame.open();
		} );
	} );
	document.querySelectorAll( '.isp-image-field' ).forEach( function ( field ) {
		var input = field.querySelector( 'input' );
		var preview = field.querySelector( '.isp-image-preview' );
		var pick = field.querySelector( '.isp-image-pick' );
		var remove = field.querySelector( '.isp-image-remove' );
		var frame;
		pick.addEventListener( 'click', function () {
			if ( ! frame ) {
				frame = wp.media( { title: pick.closest( 'tr' ).querySelector( 'th' ).textContent, library: { type: 'image' }, multiple: false } );
				frame.on( 'select', function () {
					var image = frame.state().get( 'selection' ).first().toJSON();
					input.value = String( image.id );
					preview.src = image.sizes && image.sizes.medium ? image.sizes.medium.url : image.url;
					preview.style.display = 'block';
					remove.style.display = '';
					pick.textContent = <?php echo wp_json_encode( __( 'Replace image', 'interior-specifics-core' ) ); ?>;
				} );
			}
			frame.open();
		} );
		remove.addEventListener( 'click', function () {
			input.value = '';
			preview.removeAttribute( 'src' );
			preview.style.display = 'none';
			remove.style.display = 'none';
			pick.textContent = <?php echo wp_json_encode( __( 'Select image', 'interior-specifics-core' ) ); ?>;
			pick.focus();
		} );
	} );
	</script>
	<?php
}

add_filter( 'plugin_action_links_' . plugin_basename( ISP_FILE ), 'isp_settings_link' );
/**
 * "Settings" shortcut on the Plugins screen.
 *
 * @param string[] $links Existing links.
 * @return string[]
 */
function isp_settings_link( array $links ): array {
	array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=isp-settings' ) ) . '">' . esc_html__( 'Settings', 'interior-specifics-core' ) . '</a>' );
	return $links;
}
