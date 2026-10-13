import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { createEdit, save } from '../../editor';
import metadata from './block.json';

const BaseEdit = createEdit( metadata.name, [
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
				key: 'linkLabel',
				label: __( 'Link text', 'interior-specifics' ),
				type: 'text',
				help: __(
					'Leave empty to hide the link.',
					'interior-specifics'
				),
			},
			{
				key: 'linkUrl',
				label: __( 'Link', 'interior-specifics' ),
				type: 'url',
			},
		],
	},
	{
		title: __( 'Posts', 'interior-specifics' ),
		fields: [
			{
				key: 'count',
				label: __( 'Number of posts', 'interior-specifics' ),
				type: 'number',
				min: 1,
				max: 9,
			},
		],
	},
] );

/**
 * The shared editor plus a category picker (categories are site data, so the options load live).
 *
 * @param {Object} props Block edit props.
 */
function Edit( props ) {
	const { attributes, setAttributes } = props;
	const categories = useSelect(
		( select ) =>
			select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
				per_page: 100,
				hide_empty: true,
				_fields: 'id,name',
			} ),
		[]
	);
	const options = [
		{ label: __( 'All categories', 'interior-specifics' ), value: '0' },
		...( categories || [] ).map( ( term ) => ( {
			label: term.name,
			value: String( term.id ),
		} ) ),
	];

	return (
		<>
			<BaseEdit { ...props } />
			<InspectorControls>
				<PanelBody title={ __( 'Category', 'interior-specifics' ) }>
					<SelectControl
						label={ __( 'Only posts from', 'interior-specifics' ) }
						value={ String( attributes.category || 0 ) }
						options={ options }
						onChange={ ( value ) =>
							setAttributes( { category: Number( value ) } )
						}
						__nextHasNoMarginBottom
						__next40pxDefaultSize
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}

registerBlockType( metadata.name, { edit: Edit, save } );
