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
					key: 'stats',
					label: __( 'Stats', 'interior-specifics' ),
					help: __(
						'Numbers count up on scroll; any prefix or suffix (₦, +, %) is kept. Text values such as "Lagos" fade in instead. Leave empty to restore the defaults.',
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
	] ),
	save,
} );
