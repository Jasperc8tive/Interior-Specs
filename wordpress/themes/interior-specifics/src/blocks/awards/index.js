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
			title: __( 'Awards', 'interior-specifics' ),
			fields: [
				{
					key: 'items',
					label: __( 'Awards & features', 'interior-specifics' ),
					type: 'repeater',
					itemLabel: 'text',
					singular: __( 'award', 'interior-specifics' ),
					fields: [
						{
							key: 'text',
							label: __( 'Award', 'interior-specifics' ),
							type: 'textarea',
							rows: 2,
						},
					],
				},
				{
					key: 'clientsLine',
					label: __( 'Clients line', 'interior-specifics' ),
					type: 'textarea',
					help: __(
						'Closing sentence under the grid. The “Clients & Case Studies” menu link (#clients) scrolls to it.',
						'interior-specifics'
					),
				},
			],
		},
	] ),
	save,
} );
