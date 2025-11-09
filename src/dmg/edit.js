import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SearchControl,
	Button,
	Spinner,
} from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import { useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { postId } = attributes;
	const [ search, setSearch ] = useState( '' );
	const [ page, setPage ] = useState( 1 );
	const perPage = 5; // show 5 recent posts per page by default

	const trimmed = search.trim();
	const numericId = /^\d+$/.test( trimmed ) ? parseInt( trimmed, 10 ) : null;

	const query = useMemo( () => {
		if ( numericId ) {
			return {
				include: [ numericId ],
				status: 'publish',
				_fields: [ 'id', 'title', 'link', 'status' ],
				per_page: 1,
				page: 1,
			};
		}
		return {
			search: trimmed || undefined,
			per_page: perPage,
			page,
			status: 'publish',
			_fields: [ 'id', 'title', 'link', 'status' ],
			orderby: trimmed ? undefined : 'date',
			order: trimmed ? undefined : 'desc',
		};
	}, [ numericId, trimmed, page, perPage ] );

	const {
		records: postsRaw,
		isResolving,
		hasResolved,
	} = useEntityRecords( 'postType', 'post', query );

	const posts = Array.isArray( postsRaw ) ? postsRaw : [];
	const showingRecent = ! numericId && ! trimmed;
	const canPrev = ! numericId && page > 1;
	const canNext = ! numericId && posts.length === perPage;

	const onSelect = ( p ) => setAttributes( { postId: p.id } );
	const onClear = () => setAttributes( { postId: 0 } );

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Read More: Select Post', 'dmg' ) }
					initialOpen
				>
					<SearchControl
						__nextHasNoMarginBottom
						label={ __( 'Search posts', 'dmg' ) }
						hideLabelFromVision
						value={ search }
						onChange={ ( v ) => {
							setSearch( v );
							setPage( 1 );
						} }
						placeholder={ __(
							'Search by title or paste an ID',
							'dmg'
						) }
					/>

					{ isResolving && <Spinner /> }

					{ hasResolved && posts.length > 0 && (
						<div>
							{ showingRecent && (
								<h4 className="dmg-subheading">
									{ __( 'Recent Articles', 'dmg' ) }
								</h4>
							) }

							<div role="list" className="dmg-post-list">
								{ posts.map( ( post, idx ) => (
									<div
										role="listitem"
										key={ post.id }
										className="dmg-post-item"
									>
										<Button
											variant="link"
											onClick={ () => onSelect( post ) }
											className="dmg-post-link"
										>
											{ post?.title?.rendered ?? '' }
										</Button>
										{ idx < posts.length - 1 && (
											<hr className="dmg-post-sep" />
										) }
									</div>
								) ) }
							</div>

							{ ! numericId && (
								<div className="dmg-pagination">
									<Button
										disabled={ ! canPrev }
										onClick={ () => setPage( page - 1 ) }
									>
										{ __( 'Previous', 'dmg' ) }
									</Button>
									<Button
										disabled={ ! canNext }
										onClick={ () => setPage( page + 1 ) }
									>
										{ __( 'Next', 'dmg' ) }
									</Button>
								</div>
							) }
						</div>
					) }

					{ hasResolved && posts.length === 0 && ! isResolving && (
						<p>{ __( 'No results', 'dmg' ) }</p>
					) }

					{ postId ? (
						<div style={ { marginTop: 12 } }>
							<Button variant="tertiary" onClick={ onClear }>
								{ __( 'Clear selection', 'dmg' ) }
							</Button>
						</div>
					) : null }
				</PanelBody>
			</InspectorControls>

			{ postId ? (
				<SelectedPreview postId={ postId } />
			) : (
				<p>{ __( 'Select a post in the block settings →', 'dmg' ) }</p>
			) }
		</div>
	);
}

function SelectedPreview( { postId } ) {
	const { records: postArr, isResolving } = useEntityRecords(
		'postType',
		'post',
		{
			include: [ postId ],
			status: 'publish',
			_fields: [ 'id', 'title', 'link', 'status' ],
			per_page: 1,
			page: 1,
		}
	);

	if ( isResolving ) {
		return <p>{ __( 'Loading…', 'dmg' ) }</p>;
	}

	const post = Array.isArray( postArr ) ? postArr[ 0 ] : null;
	if ( ! post ) {
		return <p>{ __( 'Post not found or not published.', 'dmg' ) }</p>;
	}

	const title = ( post.title?.rendered ?? '' ).replace( /<[^>]*>/g, '' );
	return (
		<p className="dmg-read-more">
			{ __( 'Read More:', 'dmg' ) }
			<a href={ post.link } target="_blank" rel="noreferrer noopener">
				{ title }
			</a>
		</p>
	);
}
