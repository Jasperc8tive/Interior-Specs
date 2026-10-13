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
			],
		},
		{
			title: __( 'Rows', 'interior-specifics' ),
			fields: [
				{
					key: 'rows',
					label: __( 'Reasons', 'interior-specifics' ),
					help: __(
						'Rows are numbered automatically (01, 02…).',
						'interior-specifics'
					),
					type: 'repeater',
					itemLabel: 'title',
					singular: __( 'reason', 'interior-specifics' ),
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
	] ),
	save,
} );
