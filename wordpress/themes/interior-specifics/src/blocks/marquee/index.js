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
					key: 'items',
					label: __( 'Credentials', 'interior-specifics' ),
					type: 'repeater',
					itemLabel: 'text',
					singular: __( 'credential', 'interior-specifics' ),
					fields: [
						{
							key: 'text',
							label: __( 'Text', 'interior-specifics' ),
							type: 'text',
						},
					],
				},
				{
					key: 'speed',
					label: __(
						'Loop duration (seconds)',
						'interior-specifics'
					),
					help: __(
						'Time for one full pass of the list. Higher is slower.',
						'interior-specifics'
					),
					type: 'number',
					min: 8,
					max: 240,
				},
			],
		},
	] ),
	save,
} );
