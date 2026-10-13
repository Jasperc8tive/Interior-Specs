import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Story', 'interior-specifics' ),
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
					key: 'paragraphs',
					label: __( 'Paragraphs', 'interior-specifics' ),
					type: 'repeater',
					singular: __( 'paragraph', 'interior-specifics' ),
					fields: [
						{
							key: 'text',
							label: __( 'Text', 'interior-specifics' ),
							type: 'textarea',
							rows: 5,
						},
					],
				},
			],
		},
		{
			title: __( 'Image', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'image',
					label: __( 'Story image', 'interior-specifics' ),
					type: 'image',
					help: __(
						'Shown in a 4:3 frame that drifts gently as the page scrolls. Landscape images work best.',
						'interior-specifics'
					),
				},
			],
		},
		{
			title: __( 'Mission & Vision', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'cards',
					label: __( 'Cards', 'interior-specifics' ),
					type: 'repeater',
					itemLabel: 'title',
					singular: __( 'card', 'interior-specifics' ),
					fields: [
						{
							key: 'title',
							label: __( 'Title', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'body',
							label: __( 'Text', 'interior-specifics' ),
							type: 'textarea',
							rows: 4,
						},
					],
				},
			],
		},
	] ),
	save,
} );
