import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

const DEFAULT_HELP = __(
	'Leave empty to use the standard wording for the chosen form.',
	'interior-specifics'
);

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			title: __( 'Form', 'interior-specifics' ),
			fields: [
				{
					key: 'type',
					label: __( 'Form', 'interior-specifics' ),
					type: 'select',
					options: [
						{
							label: __(
								'Free guide request',
								'interior-specifics'
							),
							value: 'lead-magnet',
						},
						{
							label: __(
								'Contact enquiry',
								'interior-specifics'
							),
							value: 'contact',
						},
						{
							label: __(
								'Consultation request',
								'interior-specifics'
							),
							value: 'booking',
						},
						{
							label: __(
								'Newsletter signup',
								'interior-specifics'
							),
							value: 'newsletter',
						},
					],
					help: __(
						'Fields and options for each form are fixed so every submission can be validated.',
						'interior-specifics'
					),
				},
				{
					key: 'variant',
					label: __( 'Style', 'interior-specifics' ),
					type: 'select',
					options: [
						{
							label: __(
								'Ivory section, centred',
								'interior-specifics'
							),
							value: 'section',
						},
						{
							label: __(
								'Light (Contact page style)',
								'interior-specifics'
							),
							value: 'light',
						},
						{
							label: __( 'Dark card', 'interior-specifics' ),
							value: 'dark',
						},
						{
							label: __(
								'Charcoal, compact',
								'interior-specifics'
							),
							value: 'footer',
						},
					],
				},
			],
		},
		{
			title: __( 'Heading', 'interior-specifics' ),
			initialOpen: false,
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
					help: __(
						'Leave empty for a form without a heading.',
						'interior-specifics'
					),
				},
				{
					key: 'intro',
					label: __( 'Intro', 'interior-specifics' ),
					type: 'textarea',
				},
			],
		},
		{
			title: __( 'Button & messages', 'interior-specifics' ),
			initialOpen: false,
			fields: [
				{
					key: 'submitLabel',
					label: __( 'Submit button', 'interior-specifics' ),
					type: 'text',
					help: DEFAULT_HELP,
				},
				{
					key: 'successTitle',
					label: __( 'Success title', 'interior-specifics' ),
					type: 'text',
					help: DEFAULT_HELP,
				},
				{
					key: 'successBody',
					label: __( 'Success message', 'interior-specifics' ),
					type: 'textarea',
					help: DEFAULT_HELP,
				},
				{
					key: 'finePrint',
					label: __(
						'Fine print under the button',
						'interior-specifics'
					),
					type: 'text',
				},
			],
		},
	] ),
	save,
} );
