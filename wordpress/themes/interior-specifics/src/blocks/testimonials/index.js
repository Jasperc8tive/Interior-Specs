import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Content', 'interior-specifics' ),
			fields: [
				{
					key: 'eyebrow',
					label: __( 'Eyebrow', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'title',
					label: __( 'Heading', 'interior-specifics' ),
					type: 'textarea',
					rows: 2,
				},
				{ type: 'accent' },
			],
		},
		{
			title: __( 'Testimonials', 'interior-specifics' ),
			initialOpen: true,
			fields: [
				{
					key: 'source',
					label: __( 'Show', 'interior-specifics' ),
					type: 'select',
					options: [
						{
							label: __(
								'All testimonials (in menu order)',
								'interior-specifics'
							),
							value: 'all',
						},
						{
							label: __(
								'Only the testimonials ticked below',
								'interior-specifics'
							),
							value: 'selected',
						},
					],
					help: __(
						'Edit quotes, names and locations under Testimonials in the admin menu.',
						'interior-specifics'
					),
				},
				{
					key: 'testimonialIds',
					label: __( 'Selected testimonials', 'interior-specifics' ),
					type: 'posts',
					postType: 'is_testimonial',
					help: __(
						'Used when "Show" is set to the ticked testimonials. They appear in the order ticked.',
						'interior-specifics'
					),
				},
			],
		},
		{
			title: __( 'Autoplay', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'autoplay',
					label: __( 'Advance automatically', 'interior-specifics' ),
					type: 'toggle',
					help: __(
						'Pauses while a visitor hovers or focuses the carousel, and is skipped for visitors who prefer reduced motion.',
						'interior-specifics'
					),
				},
				{
					key: 'interval',
					label: __(
						'Seconds per testimonial',
						'interior-specifics'
					),
					type: 'number',
					min: 4,
					max: 30,
				},
			],
		},
	] ),
	save,
} );
