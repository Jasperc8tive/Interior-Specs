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
			title: __( 'Grid', 'interior-specifics' ),
			fields: [
				{
					key: 'items',
					label: __( 'Cells', 'interior-specifics' ),
					type: 'repeater',
					itemLabel: 'title',
					singular: __( 'cell', 'interior-specifics' ),
					help: __(
						'Cells are numbered 01, 02… automatically.',
						'interior-specifics'
					),
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
						},
					],
				},
			],
		},
		{
			title: __( 'Layout', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'columns',
					label: __( 'Columns on desktop', 'interior-specifics' ),
					type: 'number',
					min: 3,
					max: 4,
					help: __(
						'3 = one row of three from tablet up; 4 = two per row on tablet, four on desktop.',
						'interior-specifics'
					),
				},
				{
					key: 'background',
					label: __( 'Background', 'interior-specifics' ),
					type: 'select',
					options: [
						{
							label: __( 'Ivory', 'interior-specifics' ),
							value: 'ivory',
						},
						{
							label: __( 'White', 'interior-specifics' ),
							value: 'white',
						},
					],
				},
			],
		},
	] ),
	save,
} );
