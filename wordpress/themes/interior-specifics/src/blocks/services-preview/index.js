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
				{
					key: 'intro',
					label: __( 'Intro', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'Services', 'interior-specifics' ),
			initialOpen: true,
			fields: [
				{
					key: 'source',
					label: __( 'Show', 'interior-specifics' ),
					type: 'select',
					options: [
						{
							label: __(
								'Featured services (in menu order)',
								'interior-specifics'
							),
							value: 'featured',
						},
						{
							label: __(
								'Only the services ticked below',
								'interior-specifics'
							),
							value: 'selected',
						},
					],
					help: __(
						'Mark a service as featured in its sidebar under Services in the admin menu.',
						'interior-specifics'
					),
				},
				{
					key: 'count',
					label: __(
						'Number of featured services',
						'interior-specifics'
					),
					type: 'number',
					min: 1,
					max: 12,
				},
				{
					key: 'serviceIds',
					label: __( 'Selected services', 'interior-specifics' ),
					type: 'posts',
					postType: 'is_service',
					help: __(
						'Used when "Show" is set to the ticked services. They appear in the order ticked.',
						'interior-specifics'
					),
				},
				{
					key: 'cardLinkLabel',
					label: __( 'Card link text', 'interior-specifics' ),
					type: 'text',
				},
			],
		},
		{
			title: __( 'Button', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'buttonLabel',
					label: __( 'Label', 'interior-specifics' ),
					type: 'text',
					help: __(
						'Leave empty to hide the button.',
						'interior-specifics'
					),
				},
				{
					key: 'buttonUrl',
					label: __( 'Link', 'interior-specifics' ),
					type: 'url',
				},
			],
		},
	] ),
	save,
} );
