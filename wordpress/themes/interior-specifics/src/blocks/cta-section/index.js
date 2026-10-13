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
					key: 'intro',
					label: __( 'Intro', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'Buttons', 'interior-specifics' ),
			fields: [
				{
					key: 'primaryLabel',
					label: __( 'Primary button label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'primaryUrl',
					label: __( 'Primary button link', 'interior-specifics' ),
					type: 'url',
					help: __(
						'Leave empty to use the booking page from Settings → Interior Specifics. Site paths like /contact/ work.',
						'interior-specifics'
					),
				},
				{
					key: 'secondaryLabel',
					label: __( 'WhatsApp button label', 'interior-specifics' ),
					type: 'text',
					help: __(
						'Opens a WhatsApp chat with the studio number from settings. Leave empty to hide the button.',
						'interior-specifics'
					),
				},
			],
		},
	] ),
	save,
} );
