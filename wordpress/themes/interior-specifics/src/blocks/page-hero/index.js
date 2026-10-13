import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: createEdit( metadata.name, [
		{
			key: 'eyebrow',
			label: __( 'Eyebrow', 'interior-specifics' ),
			type: 'text',
		},
		{
			key: 'title',
			label: __( 'Title', 'interior-specifics' ),
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
			key: 'image',
			label: __( 'Background image', 'interior-specifics' ),
			type: 'image',
		},
	] ),
	save,
} );
