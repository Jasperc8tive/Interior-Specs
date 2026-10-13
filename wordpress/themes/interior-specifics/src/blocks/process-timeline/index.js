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
					label: __( 'Intro', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'Steps', 'interior-specifics' ),
			fields: [
				{
					key: 'steps',
					label: __( 'Steps', 'interior-specifics' ),
					help: __(
						'Five steps fill one row on desktop.',
						'interior-specifics'
					),
					type: 'repeater',
					itemLabel: 'title',
					singular: __( 'step', 'interior-specifics' ),
					fields: [
						{
							key: 'n',
							label: __( 'Number', 'interior-specifics' ),
							type: 'text',
							help: __( 'e.g. 01', 'interior-specifics' ),
						},
						{
							key: 'title',
							label: __( 'Title', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'detail',
							label: __( 'Detail', 'interior-specifics' ),
							type: 'textarea',
						},
					],
				},
			],
		},
	] ),
	save,
} );
