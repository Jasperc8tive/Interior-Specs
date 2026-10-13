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
					help: __(
						'Shown above the stacked acts, and as the pinned label on desktop.',
						'interior-specifics'
					),
					type: 'text',
				},
				{
					key: 'title',
					label: __( 'Heading', 'interior-specifics' ),
					help: __(
						'Shown above the stacked acts (phones and reduced motion).',
						'interior-specifics'
					),
					type: 'textarea',
					rows: 2,
				},
				{
					key: 'intro',
					label: __( 'Intro', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'Acts', 'interior-specifics' ),
			fields: [
				{
					key: 'acts',
					label: __( 'Acts', 'interior-specifics' ),
					help: __(
						'Each act gets one screen of scrolling on desktop. The preview shows the stacked layout.',
						'interior-specifics'
					),
					type: 'repeater',
					itemLabel: 'title',
					singular: __( 'act', 'interior-specifics' ),
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
							key: 'narrative',
							label: __( 'Narrative', 'interior-specifics' ),
							type: 'textarea',
						},
						{
							key: 'detail',
							label: __( 'Short detail', 'interior-specifics' ),
							help: __(
								'One-line summary kept with the act for compact listings. Not shown in this section.',
								'interior-specifics'
							),
							type: 'textarea',
							rows: 2,
						},
						{
							key: 'image',
							label: __( 'Image', 'interior-specifics' ),
							type: 'image',
						},
					],
				},
			],
		},
	] ),
	save,
} );
