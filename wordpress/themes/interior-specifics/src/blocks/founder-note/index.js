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
					key: 'body',
					label: __( 'Paragraph', 'interior-specifics' ),
					type: 'textarea',
					rows: 4,
				},
				{
					key: 'quote',
					label: __( 'Pull quote', 'interior-specifics' ),
					help: __(
						'Curly quotation marks are added automatically.',
						'interior-specifics'
					),
					type: 'textarea',
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
					help: __(
						'A site path such as /about/#directors or a full URL.',
						'interior-specifics'
					),
				},
			],
		},
		{
			title: __( 'Portrait', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'image',
					label: __( 'Portrait image', 'interior-specifics' ),
					type: 'image',
				},
				{
					key: 'caption',
					label: __( 'Caption', 'interior-specifics' ),
					type: 'text',
				},
			],
		},
	] ),
	save,
} );
