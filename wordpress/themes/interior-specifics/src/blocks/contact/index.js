import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Form', 'interior-specifics' ),
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
					key: 'submitLabel',
					label: __( 'Submit button', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'successTitle',
					label: __( 'Success title', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'successBody',
					label: __( 'Success message', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'Details card', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'cardTitle',
					label: __( 'Card heading', 'interior-specifics' ),
					type: 'text',
					help: __(
						'Address, phone, email, WhatsApp and hours come from Settings → Interior Specifics. Clear a label to hide that row.',
						'interior-specifics'
					),
				},
				{
					key: 'officeLabel',
					label: __( 'Office label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'phoneLabel',
					label: __( 'Phone label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'emailLabel',
					label: __( 'Email label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'whatsappLabel',
					label: __( 'WhatsApp label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'whatsappText',
					label: __( 'WhatsApp link text', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'hoursLabel',
					label: __( 'Hours label', 'interior-specifics' ),
					type: 'text',
				},
			],
		},
		{
			title: __( 'Map', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'showMap',
					label: __( 'Show the map', 'interior-specifics' ),
					type: 'toggle',
				},
				{
					key: 'mapLabel',
					label: __( 'Map label', 'interior-specifics' ),
					type: 'text',
					help: __(
						'Set the Google Maps embed URL in Settings → Interior Specifics. Until then this label shows in a placeholder box; afterwards it names the map for screen readers.',
						'interior-specifics'
					),
				},
			],
		},
		{
			title: __( 'FAQ', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'faqEyebrow',
					label: __( 'Eyebrow', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'faqTitle',
					label: __( 'Heading', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'faqs',
					label: __( 'Questions', 'interior-specifics' ),
					type: 'repeater',
					itemLabel: 'q',
					singular: __( 'question', 'interior-specifics' ),
					fields: [
						{
							key: 'q',
							label: __( 'Question', 'interior-specifics' ),
							type: 'text',
						},
						{
							key: 'a',
							label: __( 'Answer', 'interior-specifics' ),
							type: 'textarea',
						},
					],
				},
			],
		},
	] ),
	save,
} );
