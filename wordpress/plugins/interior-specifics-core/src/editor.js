/**
 * Interior Specifics custom fields in the block editor's document sidebar.
 *
 * Every value is a registered post meta key (see includes/meta.php) read and written through
 * useEntityProp( 'postType', postType, 'meta' ), so it saves with the post via the REST API.
 *
 *   is_service      Service details, Process steps, FAQs, SEO keywords, Related projects
 *   is_project      Project details, Case study, Gallery, Client testimonial
 *   is_testimonial  Testimonial details
 *   post            Reading time
 */
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import { store as coreStore, useEntityProp } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { useEffect, useRef, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import {
	BaseControl,
	Button,
	CheckboxControl,
	Spinner,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';

import './editor.css';

/**
 * Meta of the post being edited, plus a setter for one key.
 *
 * @return {[Object, Function]} [ meta, setField( key, value ) ]
 */
function useMeta() {
	const { postType, postId } = useSelect(
		( select ) => ( {
			postType: select( editorStore ).getCurrentPostType(),
			postId: select( editorStore ).getCurrentPostId(),
		} ),
		[]
	);
	const [ meta, setMeta ] = useEntityProp(
		'postType',
		postType,
		'meta',
		postId
	);
	const current = meta || {};
	const setField = ( key, value ) =>
		setMeta( { ...current, [ key ]: value } );
	return [ current, setField ];
}

/* ------------------------------------------------------------------ */
/* Simple field wrappers                                               */
/* ------------------------------------------------------------------ */

function MetaText( { meta, setField, metaKey, label, help, placeholder } ) {
	return (
		<TextControl
			label={ label }
			help={ help }
			placeholder={ placeholder }
			value={ meta[ metaKey ] ?? '' }
			onChange={ ( value ) => setField( metaKey, value ) }
			__nextHasNoMarginBottom
			__next40pxDefaultSize
		/>
	);
}

function MetaTextarea( { meta, setField, metaKey, label, help, rows = 4 } ) {
	return (
		<TextareaControl
			label={ label }
			help={ help }
			rows={ rows }
			value={ meta[ metaKey ] ?? '' }
			onChange={ ( value ) => setField( metaKey, value ) }
			__nextHasNoMarginBottom
		/>
	);
}

function MetaToggle( { meta, setField, metaKey, label, help } ) {
	return (
		<ToggleControl
			label={ label }
			help={ help }
			checked={ !! meta[ metaKey ] }
			onChange={ ( value ) => setField( metaKey, value ) }
			__nextHasNoMarginBottom
		/>
	);
}

/**
 * Vertical rhythm between controls inside a panel.
 *
 * @param {Object}  props          Component props.
 * @param {Element} props.children Controls to stack.
 */
function Stack( { children } ) {
	return <div className="isp-meta-stack">{ children }</div>;
}

/* ------------------------------------------------------------------ */
/* Repeater (process steps, FAQs)                                      */
/* ------------------------------------------------------------------ */

/**
 * Ordered list of small objects with add / remove / move up / move down.
 * Keys are indexes so typing never remounts an input; focus follows a moved item.
 *
 * @param {Object}   props
 * @param {string}   props.label     Group label.
 * @param {Object[]} props.items     Current items.
 * @param {Function} props.onChange  Receives the new array.
 * @param {Object[]} props.fields    [{ key, label, type: 'text'|'textarea', rows }].
 * @param {Function} props.itemLabel ( index ) => heading for an item.
 * @param {string}   props.addLabel  Add button text.
 * @param {Object}   props.emptyItem Shape of a new item.
 */
function Repeater( {
	label,
	items,
	onChange,
	fields,
	itemLabel,
	addLabel,
	emptyItem,
} ) {
	const list = Array.isArray( items ) ? items : [];
	const listRef = useRef();
	const focusRequest = useRef( null );

	useEffect( () => {
		if ( ! focusRequest.current || ! listRef.current ) {
			return;
		}
		const { index, selector } = focusRequest.current;
		focusRequest.current = null;
		const item = listRef.current.querySelector(
			`[data-index="${ index }"]`
		);
		const target =
			item?.querySelector(
				`${ selector }:not([aria-disabled="true"]):not(:disabled)`
			) || item?.querySelector( 'input, textarea' );
		target?.focus();
	} );

	const update = ( index, patch ) =>
		onChange(
			list.map( ( item, i ) =>
				i === index ? { ...item, ...patch } : item
			)
		);
	const remove = ( index ) => {
		focusRequest.current = {
			index: Math.min( index, list.length - 2 ),
			selector: '.isp-repeater__remove',
		};
		onChange( list.filter( ( _, i ) => i !== index ) );
	};
	const move = ( index, delta ) => {
		const target = index + delta;
		if ( target < 0 || target >= list.length ) {
			return;
		}
		const next = [ ...list ];
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
		focusRequest.current = {
			index: target,
			selector: delta < 0 ? '.isp-repeater__up' : '.isp-repeater__down',
		};
		onChange( next );
	};
	const add = () => {
		focusRequest.current = {
			index: list.length,
			selector: 'input, textarea',
		};
		onChange( [ ...list, { ...emptyItem } ] );
	};

	return (
		<fieldset className="isp-repeater">
			<legend className="isp-repeater__legend">{ label }</legend>
			{ ! list.length && (
				<p className="isp-meta-muted">
					{ __( 'Nothing added yet.', 'interior-specifics-core' ) }
				</p>
			) }
			<ol className="isp-repeater__list" ref={ listRef }>
				{ list.map( ( item, index ) => (
					<li
						className="isp-repeater__item"
						key={ index }
						data-index={ index }
					>
						<div className="isp-repeater__head">
							<span className="isp-repeater__title">
								{ itemLabel( index ) }
							</span>
							<span className="isp-repeater__tools">
								<Button
									className="isp-repeater__up"
									icon="arrow-up-alt2"
									size="small"
									label={ sprintf(
										/* translators: %s: item heading, e.g. "Step 2". */ __(
											'Move %s up',
											'interior-specifics-core'
										),
										itemLabel( index )
									) }
									disabled={ index === 0 }
									accessibleWhenDisabled
									onClick={ () => move( index, -1 ) }
								/>
								<Button
									className="isp-repeater__down"
									icon="arrow-down-alt2"
									size="small"
									label={ sprintf(
										/* translators: %s: item heading. */ __(
											'Move %s down',
											'interior-specifics-core'
										),
										itemLabel( index )
									) }
									disabled={ index === list.length - 1 }
									accessibleWhenDisabled
									onClick={ () => move( index, 1 ) }
								/>
								<Button
									className="isp-repeater__remove"
									icon="trash"
									size="small"
									isDestructive
									label={ sprintf(
										/* translators: %s: item heading. */ __(
											'Remove %s',
											'interior-specifics-core'
										),
										itemLabel( index )
									) }
									onClick={ () => remove( index ) }
								/>
							</span>
						</div>
						{ fields.map( ( field ) =>
							field.type === 'textarea' ? (
								<TextareaControl
									key={ field.key }
									label={ field.label }
									rows={ field.rows || 3 }
									value={ item?.[ field.key ] ?? '' }
									onChange={ ( value ) =>
										update( index, {
											[ field.key ]: value,
										} )
									}
									__nextHasNoMarginBottom
								/>
							) : (
								<TextControl
									key={ field.key }
									label={ field.label }
									value={ item?.[ field.key ] ?? '' }
									onChange={ ( value ) =>
										update( index, {
											[ field.key ]: value,
										} )
									}
									__nextHasNoMarginBottom
									__next40pxDefaultSize
								/>
							)
						) }
					</li>
				) ) }
			</ol>
			<Button
				variant="secondary"
				onClick={ add }
				className="isp-repeater__add"
			>
				{ addLabel }
			</Button>
		</fieldset>
	);
}

/* ------------------------------------------------------------------ */
/* SEO keywords: comma-separated text <-> string[]                     */
/* ------------------------------------------------------------------ */

const parseKeywords = ( text ) =>
	text
		.split( ',' )
		.map( ( word ) => word.trim() )
		.filter( Boolean );

function KeywordsField( { value, onChange } ) {
	const keywords = Array.isArray( value ) ? value : [];
	const joined = keywords.join( ', ' );
	const [ draft, setDraft ] = useState( joined );

	// Follow outside changes (undo, revisions) without fighting the user's in-progress typing.
	useEffect( () => {
		if ( parseKeywords( draft ).join( ', ' ) !== joined ) {
			setDraft( joined );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ joined ] );

	return (
		<TextareaControl
			label={ __( 'SEO keywords', 'interior-specifics-core' ) }
			help={ __(
				'Separate keywords with commas.',
				'interior-specifics-core'
			) }
			rows={ 3 }
			value={ draft }
			onChange={ ( text ) => {
				setDraft( text );
				onChange( parseKeywords( text ) );
			} }
			onBlur={ () => setDraft( joined ) }
			__nextHasNoMarginBottom
		/>
	);
}

/* ------------------------------------------------------------------ */
/* Related projects: checkbox list of is_project posts                 */
/* ------------------------------------------------------------------ */

function RelatedProjectsField( { value, onChange } ) {
	const selected = Array.isArray( value ) ? value.map( Number ) : [];
	const { projects, isResolving } = useSelect( ( select ) => {
		const query = {
			per_page: 100,
			orderby: 'menu_order',
			order: 'asc',
			_fields: 'id,title,status',
		};
		return {
			projects: select( coreStore ).getEntityRecords(
				'postType',
				'is_project',
				query
			),
			isResolving: select( coreStore ).isResolving( 'getEntityRecords', [
				'postType',
				'is_project',
				query,
			] ),
		};
	}, [] );

	const toggle = ( id, on ) =>
		onChange(
			on
				? [ ...selected.filter( ( x ) => x !== id ), id ]
				: selected.filter( ( x ) => x !== id )
		);

	return (
		<BaseControl
			id="isp-related-projects"
			label={ __( 'Related projects', 'interior-specifics-core' ) }
			help={ __(
				'Shown as examples on the service page.',
				'interior-specifics-core'
			) }
			__nextHasNoMarginBottom
		>
			<div
				className="isp-checklist"
				role="group"
				aria-label={ __(
					'Related projects',
					'interior-specifics-core'
				) }
			>
				{ isResolving && ! projects && <Spinner /> }
				{ projects && ! projects.length && (
					<p className="isp-meta-muted">
						{ __( 'No projects yet.', 'interior-specifics-core' ) }
					</p>
				) }
				{ projects?.map( ( project ) => (
					<CheckboxControl
						key={ project.id }
						label={
							project.title?.rendered
								? decodeEntities( project.title.rendered )
								: sprintf(
										/* translators: %d: post ID. */ __(
											'Project #%d',
											'interior-specifics-core'
										),
										project.id
								  )
						}
						checked={ selected.includes( project.id ) }
						onChange={ ( on ) => toggle( project.id, on ) }
						__nextHasNoMarginBottom
					/>
				) ) }
			</div>
		</BaseControl>
	);
}

/**
 * Decode HTML entities in rendered titles without injecting markup.
 *
 * @param {string} html Rendered title.
 */
function decodeEntities( html ) {
	const doc = new window.DOMParser().parseFromString( html, 'text/html' );
	return doc.documentElement.textContent || '';
}

/* ------------------------------------------------------------------ */
/* Gallery: attachment IDs with thumbnails, reorder and remove          */
/* ------------------------------------------------------------------ */

function GalleryThumb( { id, index, total, onMove, onRemove } ) {
	const media = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecord( 'postType', 'attachment', id, {
				context: 'view',
			} ),
		[ id ]
	);
	const src =
		media?.media_details?.sizes?.thumbnail?.source_url || media?.source_url;
	const alt = media?.alt_text || '';
	const name = sprintf(
		/* translators: 1: position, 2: total images. */ __(
			'Image %1$d of %2$d',
			'interior-specifics-core'
		),
		index + 1,
		total
	);

	return (
		<li className="isp-gallery__item" data-index={ index }>
			<div className="isp-gallery__frame">
				{ src ? <img src={ src } alt={ alt } /> : <Spinner /> }
			</div>
			<div className="isp-gallery__tools">
				<Button
					className="isp-gallery__prev"
					icon="arrow-left-alt2"
					size="small"
					label={ sprintf(
						/* translators: %s: image position. */ __(
							'Move %s earlier',
							'interior-specifics-core'
						),
						name
					) }
					disabled={ index === 0 }
					accessibleWhenDisabled
					onClick={ () => onMove( index, -1 ) }
				/>
				<Button
					className="isp-gallery__next"
					icon="arrow-right-alt2"
					size="small"
					label={ sprintf(
						/* translators: %s: image position. */ __(
							'Move %s later',
							'interior-specifics-core'
						),
						name
					) }
					disabled={ index === total - 1 }
					accessibleWhenDisabled
					onClick={ () => onMove( index, 1 ) }
				/>
				<Button
					className="isp-gallery__remove"
					icon="no-alt"
					size="small"
					isDestructive
					label={ sprintf(
						/* translators: %s: image position. */ __(
							'Remove %s',
							'interior-specifics-core'
						),
						name
					) }
					onClick={ () => onRemove( index ) }
				/>
			</div>
		</li>
	);
}

function GalleryField( { value, onChange } ) {
	const ids = Array.isArray( value )
		? value.map( Number ).filter( Boolean )
		: [];
	const listRef = useRef();
	const focusRequest = useRef( null );

	useEffect( () => {
		if ( ! focusRequest.current || ! listRef.current ) {
			return;
		}
		const { index, selector } = focusRequest.current;
		focusRequest.current = null;
		const item = listRef.current.querySelector(
			`[data-index="${ index }"]`
		);
		(
			item?.querySelector(
				`${ selector }:not([aria-disabled="true"])`
			) || item?.querySelector( 'button:not([aria-disabled="true"])' )
		)?.focus();
	} );

	const move = ( index, delta ) => {
		const target = index + delta;
		if ( target < 0 || target >= ids.length ) {
			return;
		}
		const next = [ ...ids ];
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
		focusRequest.current = {
			index: target,
			selector: delta < 0 ? '.isp-gallery__prev' : '.isp-gallery__next',
		};
		onChange( next );
	};
	const remove = ( index ) => {
		focusRequest.current = {
			index: Math.min( index, ids.length - 2 ),
			selector: '.isp-gallery__remove',
		};
		onChange( ids.filter( ( _, i ) => i !== index ) );
	};

	return (
		<BaseControl
			id="isp-gallery"
			label={ __( 'Gallery', 'interior-specifics-core' ) }
			help={ __(
				'Images shown in the case study gallery, in this order.',
				'interior-specifics-core'
			) }
			__nextHasNoMarginBottom
		>
			{ ids.length > 0 && (
				<ol className="isp-gallery" ref={ listRef }>
					{ ids.map( ( id, index ) => (
						<GalleryThumb
							key={ `${ id }-${ index }` }
							id={ id }
							index={ index }
							total={ ids.length }
							onMove={ move }
							onRemove={ remove }
						/>
					) ) }
				</ol>
			) }
			<MediaUploadCheck
				fallback={
					<p className="isp-meta-muted">
						{ __(
							'You do not have permission to upload images.',
							'interior-specifics-core'
						) }
					</p>
				}
			>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					multiple
					gallery
					addToGallery={ false }
					value={ ids }
					onSelect={ ( media ) =>
						onChange(
							( Array.isArray( media ) ? media : [ media ] ).map(
								( item ) => item.id
							)
						)
					}
					render={ ( { open } ) => (
						<div className="isp-gallery__actions">
							<Button variant="secondary" onClick={ open }>
								{ ids.length
									? __(
											'Edit gallery',
											'interior-specifics-core'
									  )
									: __(
											'Add images',
											'interior-specifics-core'
									  ) }
							</Button>
							{ ids.length > 0 && (
								<Button
									variant="tertiary"
									isDestructive
									onClick={ () => onChange( [] ) }
								>
									{ __( 'Clear', 'interior-specifics-core' ) }
								</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</BaseControl>
	);
}

/* ------------------------------------------------------------------ */
/* Panels per post type                                                */
/* ------------------------------------------------------------------ */

function ServicePanels() {
	const [ meta, setField ] = useMeta();
	const props = { meta, setField };
	return (
		<>
			<PluginDocumentSettingPanel
				name="isp-service-details"
				title={ __( 'Service details', 'interior-specifics-core' ) }
			>
				<Stack>
					<MetaText
						{ ...props }
						metaKey="isp_investment_from"
						label={ __(
							'Investment from',
							'interior-specifics-core'
						) }
						placeholder={ __(
							'e.g. ₦25M',
							'interior-specifics-core'
						) }
						help={ __(
							'Starting price shown on the service page.',
							'interior-specifics-core'
						) }
					/>
					<MetaToggle
						{ ...props }
						metaKey="isp_featured"
						label={ __(
							'Featured on homepage',
							'interior-specifics-core'
						) }
					/>
					<KeywordsField
						value={ meta.isp_seo_keywords }
						onChange={ ( value ) =>
							setField( 'isp_seo_keywords', value )
						}
					/>
				</Stack>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="isp-service-process"
				title={ __( 'Process steps', 'interior-specifics-core' ) }
			>
				<Repeater
					label={ __( 'Process steps', 'interior-specifics-core' ) }
					items={ meta.isp_process }
					onChange={ ( value ) => setField( 'isp_process', value ) }
					fields={ [
						{
							key: 'step',
							label: __( 'Step', 'interior-specifics-core' ),
						},
						{
							key: 'detail',
							label: __( 'Detail', 'interior-specifics-core' ),
							type: 'textarea',
						},
					] }
					itemLabel={ ( index ) =>
						sprintf(
							/* translators: %d: step number. */ __(
								'Step %d',
								'interior-specifics-core'
							),
							index + 1
						)
					}
					addLabel={ __( 'Add step', 'interior-specifics-core' ) }
					emptyItem={ { step: '', detail: '' } }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="isp-service-faqs"
				title={ __( 'FAQs', 'interior-specifics-core' ) }
			>
				<Repeater
					label={ __(
						'Frequently asked questions',
						'interior-specifics-core'
					) }
					items={ meta.isp_faqs }
					onChange={ ( value ) => setField( 'isp_faqs', value ) }
					fields={ [
						{
							key: 'q',
							label: __( 'Question', 'interior-specifics-core' ),
						},
						{
							key: 'a',
							label: __( 'Answer', 'interior-specifics-core' ),
							type: 'textarea',
							rows: 4,
						},
					] }
					itemLabel={ ( index ) =>
						sprintf(
							/* translators: %d: question number. */ __(
								'Question %d',
								'interior-specifics-core'
							),
							index + 1
						)
					}
					addLabel={ __( 'Add question', 'interior-specifics-core' ) }
					emptyItem={ { q: '', a: '' } }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="isp-service-related"
				title={ __( 'Related projects', 'interior-specifics-core' ) }
			>
				<RelatedProjectsField
					value={ meta.isp_related_projects }
					onChange={ ( value ) =>
						setField( 'isp_related_projects', value )
					}
				/>
			</PluginDocumentSettingPanel>
		</>
	);
}

function ProjectPanels() {
	const [ meta, setField ] = useMeta();
	const props = { meta, setField };
	return (
		<>
			<PluginDocumentSettingPanel
				name="isp-project-details"
				title={ __( 'Project details', 'interior-specifics-core' ) }
			>
				<Stack>
					<MetaText
						{ ...props }
						metaKey="isp_location"
						label={ __( 'Location', 'interior-specifics-core' ) }
						placeholder={ __(
							'e.g. Ikoyi, Lagos',
							'interior-specifics-core'
						) }
					/>
					<MetaText
						{ ...props }
						metaKey="isp_year"
						label={ __( 'Year', 'interior-specifics-core' ) }
						placeholder={ __(
							'e.g. 2024',
							'interior-specifics-core'
						) }
					/>
					<MetaText
						{ ...props }
						metaKey="isp_budget"
						label={ __( 'Budget', 'interior-specifics-core' ) }
						placeholder={ __(
							'e.g. ₦120M',
							'interior-specifics-core'
						) }
					/>
					<MetaText
						{ ...props }
						metaKey="isp_timeline"
						label={ __( 'Timeline', 'interior-specifics-core' ) }
						placeholder={ __(
							'e.g. 8 months',
							'interior-specifics-core'
						) }
					/>
					<MetaToggle
						{ ...props }
						metaKey="isp_featured"
						label={ __( 'Featured', 'interior-specifics-core' ) }
						help={ __(
							'Show this project in featured work on the homepage.',
							'interior-specifics-core'
						) }
					/>
				</Stack>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="isp-project-case-study"
				title={ __( 'Case study', 'interior-specifics-core' ) }
			>
				<Stack>
					<MetaTextarea
						{ ...props }
						metaKey="isp_challenge"
						label={ __( 'Challenge', 'interior-specifics-core' ) }
					/>
					<MetaTextarea
						{ ...props }
						metaKey="isp_solution"
						label={ __( 'Solution', 'interior-specifics-core' ) }
					/>
					<MetaTextarea
						{ ...props }
						metaKey="isp_results"
						label={ __( 'Results', 'interior-specifics-core' ) }
					/>
				</Stack>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="isp-project-gallery"
				title={ __( 'Gallery', 'interior-specifics-core' ) }
			>
				<GalleryField
					value={ meta.isp_gallery }
					onChange={ ( value ) => setField( 'isp_gallery', value ) }
				/>
			</PluginDocumentSettingPanel>
			<PluginDocumentSettingPanel
				name="isp-project-testimonial"
				title={ __( 'Client testimonial', 'interior-specifics-core' ) }
			>
				<Stack>
					<MetaTextarea
						{ ...props }
						metaKey="isp_testimonial_quote"
						label={ __( 'Quote', 'interior-specifics-core' ) }
					/>
					<MetaText
						{ ...props }
						metaKey="isp_testimonial_name"
						label={ __( 'Client name', 'interior-specifics-core' ) }
					/>
				</Stack>
			</PluginDocumentSettingPanel>
		</>
	);
}

function TestimonialPanels() {
	const [ meta, setField ] = useMeta();
	const props = { meta, setField };
	return (
		<PluginDocumentSettingPanel
			name="isp-testimonial-details"
			title={ __( 'Testimonial details', 'interior-specifics-core' ) }
		>
			<Stack>
				<p className="isp-meta-muted">
					{ __(
						'The title is the client name; the content is the quote.',
						'interior-specifics-core'
					) }
				</p>
				<MetaText
					{ ...props }
					metaKey="isp_location"
					label={ __( 'Location', 'interior-specifics-core' ) }
					placeholder={ __(
						'e.g. Ikoyi, Lagos',
						'interior-specifics-core'
					) }
				/>
				<MetaText
					{ ...props }
					metaKey="isp_project_label"
					label={ __( 'Project label', 'interior-specifics-core' ) }
					placeholder={ __(
						'e.g. Penthouse Residence',
						'interior-specifics-core'
					) }
				/>
			</Stack>
		</PluginDocumentSettingPanel>
	);
}

function PostPanels() {
	const [ meta, setField ] = useMeta();
	const auto = window.ispEditor?.autoReadTime || '';
	return (
		<PluginDocumentSettingPanel
			name="isp-post-reading-time"
			title={ __( 'Reading time', 'interior-specifics-core' ) }
		>
			<MetaText
				meta={ meta }
				setField={ setField }
				metaKey="isp_read_time"
				label={ __(
					'Reading time override',
					'interior-specifics-core'
				) }
				placeholder={ auto }
				help={
					auto
						? sprintf(
								/* translators: %s: computed reading time, e.g. "4 min read". */ __(
									'Leave empty to use the calculated value (%s, as of the last save).',
									'interior-specifics-core'
								),
								auto
						  )
						: __(
								'Leave empty to calculate it from the word count.',
								'interior-specifics-core'
						  )
				}
			/>
		</PluginDocumentSettingPanel>
	);
}

const PANELS = {
	is_service: ServicePanels,
	is_project: ProjectPanels,
	is_testimonial: TestimonialPanels,
	post: PostPanels,
};

function InteriorSpecificsFields() {
	const postType = useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);
	const Panels = PANELS[ postType ];
	return Panels ? <Panels /> : null;
}

registerPlugin( 'interior-specifics-fields', {
	render: InteriorSpecificsFields,
	icon: null,
} );
