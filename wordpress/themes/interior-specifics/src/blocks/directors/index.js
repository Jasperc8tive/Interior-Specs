import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Heading', 'interior-specifics' ),
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
					label: __( 'Intro (optional)', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'People', 'interior-specifics' ),
			fields: [
				{
					key: 'people',
					label: __( 'Directors', 'interior-specifics' ),
					type: 'repeater',
					itemLabel: 'name',
					singular: __( 'person', 'interior-specifics' ),
					fields: [
						{
							key: 'name',
							label: __( 'Name', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'role',
							label: __( 'Role', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'image',
							label: __( 'Portrait', 'interior-specifics' ),
							type: 'image',
							help: __(
								'Cropped to a 3:4 portrait. If the alt text is empty, the name is used.',
								'interior-specifics'
							),
						},
					],
				},
			],
		},
	] ),
	save,
} );
