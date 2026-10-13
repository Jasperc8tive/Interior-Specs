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
					key: 'linkLabel',
					label: __( 'Link label', 'interior-specifics' ),
					type: 'text',
				},
				{
					key: 'linkUrl',
					label: __( 'Link URL', 'interior-specifics' ),
					type: 'url',
					help: __(
						'A site path such as /showroom/ or a full URL.',
						'interior-specifics'
					),
				},
			],
		},
		{
			title: __( 'Projects', 'interior-specifics' ),
			fields: [
				{
					key: 'source',
					label: __( 'Show', 'interior-specifics' ),
					type: 'select',
					options: [
						{
							label: __(
								'Featured projects (in menu order)',
								'interior-specifics'
							),
							value: 'featured',
						},
						{
							label: __(
								'Hand-picked projects',
								'interior-specifics'
							),
							value: 'selected',
						},
					],
					help: __(
						'Up to six projects: a lead pair, a trio and a wide closer. Mark projects as featured on the project edit screen.',
						'interior-specifics'
					),
				},
				{
					key: 'projectIds',
					label: __(
						'Hand-picked projects (used when "Hand-picked" is chosen, in the order ticked)',
						'interior-specifics'
					),
					type: 'posts',
					postType: 'is_project',
				},
			],
		},
	] ),
	save,
} );
