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
					label: __( 'Headline', 'interior-specifics' ),
					type: 'textarea',
					rows: 2,
				},
				{ type: 'accent' },
				{
					key: 'body',
					label: __( 'Intro', 'interior-specifics' ),
					type: 'textarea',
					rows: 4,
				},
				{
					key: 'verticalLabel',
					label: __( 'Vertical side label', 'interior-specifics' ),
					help: __(
						'Shown on large screens. {year} is replaced with the founded year from the site settings.',
						'interior-specifics'
					),
					type: 'text',
				},
			],
		},
		{
			title: __( 'Buttons', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'primaryLabel',
					label: __( 'Primary button label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'primaryUrl',
					label: __( 'Primary button link', 'interior-specifics' ),
					help: __(
						'Leave empty to use the booking page from the site settings.',
						'interior-specifics'
					),
					type: 'url',
				},
				{
					key: 'secondaryLabel',
					label: __( 'Secondary button label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'secondaryUrl',
					label: __( 'Secondary button link', 'interior-specifics' ),
					help: __(
						'A page path like /about/, a full URL, or #method to scroll to the Method section.',
						'interior-specifics'
					),
					type: 'url',
				},
			],
		},
		{
			title: __( 'Stats', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'stats',
					label: __( 'Headline stats', 'interior-specifics' ),
					help: __(
						'Leave empty to use the stats from Settings → Interior Specifics.',
						'interior-specifics'
					),
					type: 'repeater',
					itemLabel: 'label',
					singular: __( 'stat', 'interior-specifics' ),
					max: 4,
					fields: [
						{
							key: 'value',
							label: __( 'Value', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'label',
							label: __( 'Label', 'interior-specifics' ),
							type: 'text',
						},
					],
				},
			],
		},
		{
			title: __( 'Image & scroll cue', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'image',
					label: __( 'Background image', 'interior-specifics' ),
					type: 'image',
				},
				{
					key: 'showScrollCue',
					label: __( 'Show scroll cue', 'interior-specifics' ),
					type: 'toggle',
				},
				{
					key: 'scrollLabel',
					label: __( 'Scroll cue label', 'interior-specifics' ),
					type: 'text',
				},
			],
		},
	] ),
	save,
} );
