import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Invitation', 'interior-specifics' ),
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
					key: 'bodyBefore',
					label: __(
						'Paragraph: text before the guide title',
						'interior-specifics'
					),
					type: 'textarea',
				},
				{
					key: 'guideTitle',
					label: __( 'Guide title', 'interior-specifics' ),
					type: 'text',
					help: __(
						'Shown in gold italic within the paragraph.',
						'interior-specifics'
					),
				},
				{
					key: 'bodyAfter',
					label: __(
						'Paragraph: text after the guide title',
						'interior-specifics'
					),
					type: 'textarea',
					rows: 2,
				},
			],
		},
		{
			title: __( 'Contact links', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'whatsappLabel',
					label: __( 'WhatsApp link text', 'interior-specifics' ),
					type: 'text',
					help: __(
						'The number and greeting come from Settings → Interior Specifics. Leave empty to hide.',
						'interior-specifics'
					),
				},
				{
					key: 'showPhone',
					label: __(
						'Show the studio phone number',
						'interior-specifics'
					),
					type: 'toggle',
				},
			],
		},
		{
			title: __( 'Form', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'submitLabel',
					label: __( 'Submit button', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'finePrint',
					label: __( 'Fine print', 'interior-specifics' ),
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
			title: __( 'Backdrop', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'image',
					label: __( 'Backdrop image', 'interior-specifics' ),
					type: 'image',
					help: __(
						'Shown very faintly behind the section. Decorative, so alt text can stay empty.',
						'interior-specifics'
					),
				},
			],
		},
	] ),
	save,
} );
