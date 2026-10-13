/**
 * Shared editor UI for every Interior Specifics block.
 *
 * Blocks are dynamic (render.php). In the editor each block shows a live server-rendered preview
 * plus sidebar controls generated from a small field schema, so every block edits the same way:
 *
 *   registerBlockType( metadata.name, {
 *     edit: createEdit( metadata.name, [
 *       { title: 'Content', fields: [
 *         { key: 'eyebrow', label: 'Eyebrow', type: 'text' },
 *         { key: 'title', label: 'Heading', type: 'textarea' },
 *         { type: 'accent' },                       // accentFrom / accentTo word range
 *         { key: 'image', label: 'Image', type: 'image' },  // imageId / imageUrl / imageAlt
 *         { key: 'items', label: 'Items', type: 'repeater', itemLabel: 'title',
 *           fields: [ { key: 'title', label: 'Title', type: 'text' } ] },
 *         { key: 'postIds', label: 'Projects', type: 'posts', postType: 'is_project' },
 *       ] },
 *     ] ),
 *   } );
 *
 * Field types: text, textarea, url, number, toggle, select (options: [{label, value}]),
 * image, accent, repeater (fields of the above, except repeater/posts), posts.
 */
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	useBlockProps,
} from '@wordpress/block-editor';
import {
	BaseControl,
	Button,
	CheckboxControl,
	Disabled,
	Flex,
	FlexItem,
	PanelBody,
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis -- no stable number control yet.
	__experimentalNumberControl as NumberControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useRef } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import ServerSideRender from '@wordpress/server-side-render';

/**
 * Attribute keys used by an image field with the given key.
 * The conventional primary image uses key "image" → imageId / imageUrl / imageAlt.
 *
 * @param {string} key Field key.
 */
const imageKeys = ( key = 'image' ) => ( {
	id: `${ key }Id`,
	url: `${ key }Url`,
	alt: `${ key }Alt`,
} );

/**
 * Integer from a NumberControl value, clamped to min/max. Empty or partial input ('', '-')
 * returns the fallback rather than Number()'s silent 0.
 *
 * @param {string|number}    v        Raw control value.
 * @param {*}                fallback Returned when v is not a number.
 * @param {number|undefined} min      Lower bound.
 * @param {number|undefined} max      Upper bound.
 */
const toInt = ( v, fallback, min, max ) => {
	const n = parseInt( v, 10 );
	if ( Number.isNaN( n ) ) {
		return fallback;
	}
	return Math.min( max ?? Infinity, Math.max( min ?? -Infinity, n ) );
};

/** Session-unique React keys for repeater items (never saved with the block). */
let repeaterKeySeq = 0;

function ImageField( { field, value, onChange } ) {
	const keys = imageKeys( field.key );
	const url = value[ keys.url ];
	return (
		<BaseControl help={ field.help } __nextHasNoMarginBottom>
			<BaseControl.VisualLabel>{ field.label }</BaseControl.VisualLabel>
			{ url && (
				<img
					src={ url }
					alt=""
					style={ {
						display: 'block',
						width: '100%',
						aspectRatio: '16 / 10',
						objectFit: 'cover',
						marginBottom: 8,
					} }
				/>
			) }
			<Flex gap={ 2 } wrap>
				<MediaUploadCheck>
					<MediaUpload
						allowedTypes={ [ 'image' ] }
						value={ value[ keys.id ] || undefined }
						onSelect={ ( media ) =>
							onChange( {
								[ keys.id ]: media.id,
								[ keys.url ]:
									media.sizes?.large?.url || media.url,
								[ keys.alt ]:
									media.alt || value[ keys.alt ] || '',
							} )
						}
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ url
									? __(
											'Replace image',
											'interior-specifics'
									  )
									: __(
											'Choose image',
											'interior-specifics'
									  ) }
							</Button>
						) }
					/>
				</MediaUploadCheck>
				{ url && (
					<Button
						variant="tertiary"
						isDestructive
						onClick={ () =>
							onChange( { [ keys.id ]: 0, [ keys.url ]: '' } )
						}
					>
						{ __( 'Remove', 'interior-specifics' ) }
					</Button>
				) }
			</Flex>
			<TextControl
				label={ __( 'Alt text', 'interior-specifics' ) }
				help={ __(
					'Describe the image for screen readers. Leave empty if purely decorative.',
					'interior-specifics'
				) }
				value={ value[ keys.alt ] || '' }
				onChange={ ( alt ) => onChange( { [ keys.alt ]: alt } ) }
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
		</BaseControl>
	);
}

function PostsField( { field, value, onChange } ) {
	const posts = useSelect(
		( select ) =>
			select( 'core' ).getEntityRecords( 'postType', field.postType, {
				per_page: 100,
				status: 'publish',
				_fields: 'id,title',
			} ),
		[ field.postType ]
	);
	const selected = Array.isArray( value ) ? value : [];
	const toggle = ( id, on ) =>
		onChange(
			on ? [ ...selected, id ] : selected.filter( ( x ) => x !== id )
		);

	return (
		<BaseControl help={ field.help } __nextHasNoMarginBottom>
			<BaseControl.VisualLabel>{ field.label }</BaseControl.VisualLabel>
			{ ! posts && <p>{ __( 'Loading…', 'interior-specifics' ) }</p> }
			{ posts?.map( ( post ) => (
				<CheckboxControl
					key={ post.id }
					label={
						decodeEntities( post.title?.rendered || '' ) ||
						`#${ post.id }`
					}
					checked={ selected.includes( post.id ) }
					onChange={ ( on ) => toggle( post.id, on ) }
					__nextHasNoMarginBottom
				/>
			) ) }
		</BaseControl>
	);
}

/**
 * One simple control. `value` is the attribute (or repeater item) object; `onChange` merges a patch.
 *
 * @param {Object}   props
 * @param {Object}   props.field
 * @param {Object}   props.value
 * @param {Function} props.onChange
 */
function Field( { field, value, onChange } ) {
	const common = {
		label: field.label,
		help: field.help,
		__nextHasNoMarginBottom: true,
	};
	const set = ( v ) => onChange( { [ field.key ]: v } );

	switch ( field.type ) {
		case 'textarea':
			return (
				<TextareaControl
					{ ...common }
					rows={ field.rows || 3 }
					value={ value[ field.key ] ?? '' }
					onChange={ set }
				/>
			);
		case 'url':
			return (
				<TextControl
					{ ...common }
					type="url"
					value={ value[ field.key ] ?? '' }
					onChange={ set }
					__next40pxDefaultSize
				/>
			);
		case 'number':
			return (
				<NumberControl
					{ ...common }
					min={ field.min }
					max={ field.max }
					value={ value[ field.key ] ?? 0 }
					onChange={ ( v ) => {
						// Ignore a cleared or half-typed field; keep the last valid number.
						const n = toInt( v, null, field.min, field.max );
						if ( null !== n ) {
							set( n );
						}
					} }
					__next40pxDefaultSize
				/>
			);
		case 'toggle':
			return (
				<ToggleControl
					{ ...common }
					checked={ !! value[ field.key ] }
					onChange={ set }
				/>
			);
		case 'select':
			return (
				<SelectControl
					{ ...common }
					options={ field.options }
					value={ value[ field.key ] }
					onChange={ set }
					__next40pxDefaultSize
				/>
			);
		case 'image':
			return (
				<ImageField
					field={ field }
					value={ value }
					onChange={ onChange }
				/>
			);
		case 'accent':
			return (
				<BaseControl
					help={ __(
						'Word positions in the heading to set in gold italic, counting from 0. Use -1 for none.',
						'interior-specifics'
					) }
					__nextHasNoMarginBottom
				>
					<BaseControl.VisualLabel>
						{ field.label ||
							__( 'Gold italic accent', 'interior-specifics' ) }
					</BaseControl.VisualLabel>
					<Flex>
						<FlexItem>
							<NumberControl
								label={ __(
									'From word',
									'interior-specifics'
								) }
								min={ -1 }
								value={
									value[ field.fromKey || 'accentFrom' ] ?? -1
								}
								onChange={ ( v ) =>
									onChange( {
										[ field.fromKey || 'accentFrom' ]:
											toInt( v, -1, -1 ),
									} )
								}
								__next40pxDefaultSize
							/>
						</FlexItem>
						<FlexItem>
							<NumberControl
								label={ __( 'To word', 'interior-specifics' ) }
								min={ -1 }
								value={
									value[ field.toKey || 'accentTo' ] ?? -1
								}
								onChange={ ( v ) =>
									onChange( {
										[ field.toKey || 'accentTo' ]: toInt(
											v,
											-1,
											-1
										),
									} )
								}
								__next40pxDefaultSize
							/>
						</FlexItem>
					</Flex>
				</BaseControl>
			);
		default:
			return (
				<TextControl
					{ ...common }
					value={ value[ field.key ] ?? '' }
					onChange={ set }
					__next40pxDefaultSize
				/>
			);
	}
}

function RepeaterField( { field, items, onChange } ) {
	const list = Array.isArray( items ) ? items : [];

	// Stable keys travel with their item, so an open panel stays with it after a move or remove.
	// Kept in step with the list length for changes made elsewhere (undo, another block instance).
	const keys = useRef( [] );
	while ( keys.current.length < list.length ) {
		keys.current.push( ++repeaterKeySeq );
	}
	keys.current.length = list.length;

	const update = ( index, patch ) =>
		onChange(
			list.map( ( item, i ) =>
				i === index ? { ...item, ...patch } : item
			)
		);
	const move = ( index, delta ) => {
		const next = [ ...list ];
		const [ item ] = next.splice( index, 1 );
		next.splice( index + delta, 0, item );
		const [ key ] = keys.current.splice( index, 1 );
		keys.current.splice( index + delta, 0, key );
		onChange( next );
	};
	const remove = ( index ) => {
		keys.current.splice( index, 1 );
		onChange( list.filter( ( _, i ) => i !== index ) );
	};
	const blank = () =>
		Object.fromEntries(
			field.fields.flatMap( ( f ) => {
				if ( f.type === 'image' ) {
					const k = imageKeys( f.key );
					return [
						[ k.id, 0 ],
						[ k.url, '' ],
						[ k.alt, '' ],
					];
				}
				return [ [ f.key, f.type === 'toggle' ? false : '' ] ];
			} )
		);

	return (
		<BaseControl help={ field.help } __nextHasNoMarginBottom>
			<BaseControl.VisualLabel>{ field.label }</BaseControl.VisualLabel>
			{ list.map( ( item, index ) => (
				<PanelBody
					key={ keys.current[ index ] }
					title={
						( field.itemLabel && item[ field.itemLabel ] ) ||
						`${
							field.singular || __( 'Item', 'interior-specifics' )
						} ${ index + 1 }`
					}
					initialOpen={ false }
				>
					{ field.fields.map( ( sub ) => (
						<div
							key={ sub.key || sub.type }
							style={ { marginBottom: 12 } }
						>
							<Field
								field={ sub }
								value={ item }
								onChange={ ( patch ) => update( index, patch ) }
							/>
						</div>
					) ) }
					<Flex justify="flex-start" gap={ 1 }>
						<Button
							size="small"
							variant="secondary"
							disabled={ index === 0 }
							onClick={ () => move( index, -1 ) }
						>
							{ __( 'Move up', 'interior-specifics' ) }
						</Button>
						<Button
							size="small"
							variant="secondary"
							disabled={ index === list.length - 1 }
							onClick={ () => move( index, 1 ) }
						>
							{ __( 'Move down', 'interior-specifics' ) }
						</Button>
						<Button
							size="small"
							variant="tertiary"
							isDestructive
							onClick={ () => remove( index ) }
						>
							{ __( 'Remove', 'interior-specifics' ) }
						</Button>
					</Flex>
				</PanelBody>
			) ) }
			<Button
				variant="primary"
				style={ { marginTop: 8 } }
				disabled={ field.max && list.length >= field.max }
				onClick={ () => onChange( [ ...list, blank() ] ) }
			>
				{ __( 'Add', 'interior-specifics' ) }{ ' ' }
				{ field.singular || __( 'item', 'interior-specifics' ) }
			</Button>
		</BaseControl>
	);
}

/**
 * Build an edit component: sidebar panels from the schema + a live server-rendered preview.
 *
 * @param {string} name   Block name.
 * @param {Array}  panels [{ title, initialOpen?, fields: [...] }] — or a flat fields array.
 */
export function createEdit( name, panels = [] ) {
	// A group is { title, fields } with no `type`; a flat list holds fields (a repeater field also
	// has `fields`, so test for `type` too).
	const isGrouped =
		panels.length > 0 &&
		Array.isArray( panels[ 0 ].fields ) &&
		! panels[ 0 ].type;
	const groups = isGrouped
		? panels
		: [ { title: __( 'Content', 'interior-specifics' ), fields: panels } ];

	return function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		return (
			<>
				<InspectorControls>
					{ groups.map( ( group, g ) => (
						<PanelBody
							key={ group.title }
							title={ group.title }
							initialOpen={ group.initialOpen ?? g === 0 }
						>
							{ group.fields.map( ( field ) => (
								<div
									key={ field.key || field.type }
									style={ { marginBottom: 16 } }
								>
									{ field.type === 'repeater' && (
										<RepeaterField
											field={ field }
											items={ attributes[ field.key ] }
											onChange={ ( v ) =>
												setAttributes( {
													[ field.key ]: v,
												} )
											}
										/>
									) }
									{ field.type === 'posts' && (
										<PostsField
											field={ field }
											value={ attributes[ field.key ] }
											onChange={ ( v ) =>
												setAttributes( {
													[ field.key ]: v,
												} )
											}
										/>
									) }
									{ field.type !== 'repeater' &&
										field.type !== 'posts' && (
											<Field
												field={ field }
												value={ attributes }
												onChange={ setAttributes }
											/>
										) }
								</div>
							) ) }
						</PanelBody>
					) ) }
				</InspectorControls>
				<div { ...blockProps }>
					<Disabled>
						<ServerSideRender
							block={ name }
							attributes={ attributes }
							httpMethod="POST"
						/>
					</Disabled>
				</div>
			</>
		);
	};
}

/** Every block is dynamic: nothing is saved but the comment delimiter + attributes. */
export const save = () => null;
