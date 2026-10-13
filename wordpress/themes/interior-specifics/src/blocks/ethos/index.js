import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Statement', 'interior-specifics' ),
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
					key: 'body',
					label: __( 'Paragraph', 'interior-specifics' ),
					type: 'textarea',
					rows: 4,
				},
				{
					key: 'accentLine',
					label: __( 'Italic accent line', 'interior-specifics' ),
					type: 'textarea',
					rows: 2,
				},
			],
		},
		{
			title: __( 'Pillars', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'pillars',
					label: __( 'Value pillars', 'interior-specifics' ),
					help: __(
						'Numbered automatically (01, 02, 03…).',
						'interior-specifics'
					),
					type: 'repeater',
					itemLabel: 'title',
					singular: __( 'pillar', 'interior-specifics' ),
					fields: [
						{
							key: 'title',
							label: __( 'Title', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'body',
							label: __( 'Description', 'interior-specifics' ),
							type: 'textarea',
							rows: 3,
						},
					],
				},
			],
		},
	] ),
	save,
} );
