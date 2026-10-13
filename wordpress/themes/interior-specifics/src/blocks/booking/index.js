import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Request form', 'interior-specifics' ),
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
			title: __( 'Scheduler', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'schedulerTitle',
					label: __(
						'Scheduler name (screen readers)',
						'interior-specifics'
					),
					type: 'text',
					help: __(
						'The Calendly link is set in Settings → Interior Specifics. Until it is, the card below shows instead.',
						'interior-specifics'
					),
				},
				{
					key: 'fallbackTitle',
					label: __( 'Card heading', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'fallbackBody',
					label: __( 'Card text', 'interior-specifics' ),
					type: 'textarea',
				},
				{
					key: 'callLabel',
					label: __( 'Phone link prefix', 'interior-specifics' ),
					type: 'text',
					help: __(
						'Followed by the studio phone number. Leave empty to hide the link.',
						'interior-specifics'
					),
				},
				{
					key: 'whatsappLabel',
					label: __( 'WhatsApp link text', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'showHours',
					label: __( 'Show opening hours', 'interior-specifics' ),
					type: 'toggle',
				},
			],
		},
	] ),
	save,
} );
