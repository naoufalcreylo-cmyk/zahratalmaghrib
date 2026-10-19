/**
 * Editor UI for the theme's server-rendered blocks. Plain ES5-compatible JS: no build step.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var registerBlockType = wp.blocks.registerBlockType;
	var be = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;
	var useSelect = wp.data.useSelect;
	var apiFetch = wp.apiFetch;
	var addQueryArgs = wp.url.addQueryArgs;

	var NS = 'zahrat-almaghrib/';

	function decodeEntities( s ) {
		var t = document.createElement( 'textarea' );
		t.innerHTML = s || '';
		return t.value;
	}

	/* ---------- Shared controls ---------- */

	function CategorySelect( props ) {
		var cats = useSelect( function ( select ) {
			return select( 'core' ).getEntityRecords( 'taxonomy', 'category', { per_page: 100, hide_empty: false, _fields: 'id,name,slug,parent,count' } );
		}, [] );
		var options = [ { value: '', label: props.emptyLabel || __( '— Select a category —', 'zahrat-almaghrib' ) } ];
		( cats || [] ).forEach( function ( cat ) {
			var slug = cat.slug;
			try {
				slug = decodeURIComponent( cat.slug );
			} catch ( e ) {}
			options.push( { value: slug, label: decodeEntities( cat.name ) + ' (' + cat.count + ')' + ( cat.parent ? ' ↳' : '' ) } );
		} );
		var current = props.value;
		try {
			current = decodeURIComponent( props.value );
		} catch ( e ) {}
		return el( c.SelectControl, {
			label: props.label || __( 'Category', 'zahrat-almaghrib' ),
			value: current,
			options: options,
			onChange: props.onChange,
			help: cats ? null : __( 'Loading categories…', 'zahrat-almaghrib' ),
		} );
	}

	function PostPicker( props ) {
		var value = props.value || [];
		var max = props.max || 8;
		var s = useState( '' );
		var search = s[ 0 ];
		var setSearch = s[ 1 ];
		var r = useState( [] );
		var results = r[ 0 ];
		var setResults = r[ 1 ];

		var selected = useSelect(
			function ( select ) {
				if ( ! value.length ) {
					return [];
				}
				return select( 'core' ).getEntityRecords( 'postType', 'post', { include: value, per_page: value.length, _fields: 'id,title' } );
			},
			[ value.join( ',' ) ]
		);

		useEffect(
			function () {
				if ( search.length < 2 ) {
					setResults( [] );
					return;
				}
				var cancelled = false;
				var timer = setTimeout( function () {
					apiFetch( { path: addQueryArgs( '/wp/v2/search', { search: search, type: 'post', subtype: 'post', per_page: 10 } ) } ).then( function ( res ) {
						if ( ! cancelled ) {
							setResults( res || [] );
						}
					} );
				}, 300 );
				return function () {
					cancelled = true;
					clearTimeout( timer );
				};
			},
			[ search ]
		);

		function titleFor( id ) {
			var found = ( selected || [] ).filter( function ( p ) {
				return p.id === id;
			} )[ 0 ];
			return found ? decodeEntities( found.title.rendered ) : '#' + id;
		}
		function move( i, d ) {
			var next = value.slice();
			var t = next[ i ];
			next[ i ] = next[ i + d ];
			next[ i + d ] = t;
			props.onChange( next );
		}

		return el(
			'div',
			{ className: 'zam-picker' },
			el( 'p', { className: 'zam-picker__label' }, props.label ),
			value.length
				? el(
						'ol',
						{ className: 'zam-picker__list' },
						value.map( function ( id, i ) {
							return el(
								'li',
								{ key: id },
								el( 'span', null, titleFor( id ) ),
								el(
									'span',
									{ className: 'zam-picker__actions' },
									el( c.Button, { size: 'small', icon: 'arrow-up-alt2', label: __( 'Move up', 'zahrat-almaghrib' ), disabled: i === 0, onClick: function () { move( i, -1 ); } } ),
									el( c.Button, { size: 'small', icon: 'arrow-down-alt2', label: __( 'Move down', 'zahrat-almaghrib' ), disabled: i === value.length - 1, onClick: function () { move( i, 1 ); } } ),
									el( c.Button, {
										size: 'small',
										icon: 'no-alt',
										isDestructive: true,
										label: __( 'Remove', 'zahrat-almaghrib' ),
										onClick: function () {
											props.onChange( value.filter( function ( v ) { return v !== id; } ) );
										},
									} )
								)
							);
						} )
				  )
				: el( 'p', { className: 'zam-picker__empty' }, props.emptyText ),
			value.length < max
				? el( c.TextControl, {
						label: __( 'Search posts to add', 'zahrat-almaghrib' ),
						value: search,
						onChange: setSearch,
						__nextHasNoMarginBottom: true,
				  } )
				: null,
			results.length
				? el(
						'ul',
						{ className: 'zam-picker__results' },
						results
							.filter( function ( p ) {
								return value.indexOf( p.id ) === -1;
							} )
							.map( function ( p ) {
								return el(
									'li',
									{ key: p.id },
									el(
										c.Button,
										{
											variant: 'link',
											onClick: function () {
												props.onChange( value.concat( [ p.id ] ).slice( 0, max ) );
												setSearch( '' );
											},
										},
										decodeEntities( p.title )
									)
								);
							} )
				  )
				: null
		);
	}

	function Preview( name, attributes, context ) {
		var args = {};
		if ( context && context.postId ) {
			args.post_id = context.postId;
		}
		return el( ServerSideRender, {
			block: name,
			attributes: attributes,
			urlQueryArgs: args,
			EmptyResponsePlaceholder: function () {
				return el( c.Placeholder, { label: wp.blocks.getBlockType( name ).title, instructions: __( 'Nothing to show here in the editor. The block renders with real content on the site.', 'zahrat-almaghrib' ) } );
			},
		} );
	}

	function simple( name, inspector ) {
		registerBlockType( name, {
			edit: function ( props ) {
				var blockProps = be.useBlockProps();
				return el(
					Fragment,
					null,
					inspector ? el( be.InspectorControls, null, el( c.PanelBody, { title: __( 'Settings', 'zahrat-almaghrib' ) }, inspector( props ) ) ) : null,
					el( 'div', blockProps, Preview( name, props.attributes, props.context ) )
				);
			},
			save: function () {
				return null;
			},
		} );
	}

	function set( props, key ) {
		return function ( v ) {
			var o = {};
			o[ key ] = v;
			props.setAttributes( o );
		};
	}

	/* ---------- Blocks ---------- */

	// Site-wide logo picker. Saves WordPress's own `site_logo` setting (the same one the core Site Logo block uses).
	function LogoControl() {
		var st = useState( null );
		var logoId = st[ 0 ];
		var setLogoId = st[ 1 ];
		var busy = useState( false );
		useEffect( function () {
			apiFetch( { path: '/wp/v2/settings' } ).then(
				function ( s ) {
					setLogoId( s.site_logo || 0 );
				},
				function () {
					setLogoId( -1 );
				}
			);
		}, [] );
		function save( id ) {
			busy[ 1 ]( true );
			apiFetch( { path: '/wp/v2/settings', method: 'POST', data: { site_logo: id } } ).then( function ( s ) {
				setLogoId( s.site_logo || 0 );
				busy[ 1 ]( false );
				wp.data.dispatch( 'core' ).invalidateResolution( 'getEntityRecord', [ 'root', 'site' ] );
			} );
		}
		if ( logoId === null || logoId === -1 ) {
			return null; // Loading, or the user cannot manage site settings.
		}
		return el(
			be.MediaUploadCheck,
			null,
			el(
				'div',
				{ className: 'zam-logo-control' },
				el( be.MediaUpload, {
					allowedTypes: [ 'image' ],
					value: logoId || undefined,
					onSelect: function ( media ) {
						save( media.id );
					},
					render: function ( o ) {
						return el( c.Button, { variant: 'secondary', onClick: o.open, isBusy: busy[ 0 ] }, logoId ? __( 'Replace logo', 'zahrat-almaghrib' ) : __( 'Choose logo', 'zahrat-almaghrib' ) );
					},
				} ),
				logoId
					? el( c.Button, { variant: 'link', isDestructive: true, onClick: function () { save( 0 ); } }, __( 'Remove logo', 'zahrat-almaghrib' ) )
					: null
			)
		);
	}

	simple( NS + 'brand', function ( props ) {
		return el(
			Fragment,
			null,
			el( LogoControl ),
			el( c.SelectControl, {
				label: __( 'Size', 'zahrat-almaghrib' ),
				value: props.attributes.variant,
				options: [
					{ value: 'masthead', label: __( 'Masthead', 'zahrat-almaghrib' ) },
					{ value: 'compact', label: __( 'Compact', 'zahrat-almaghrib' ) },
				],
				onChange: set( props, 'variant' ),
			} ),
			el( c.ToggleControl, { label: __( 'Show tagline', 'zahrat-almaghrib' ), checked: props.attributes.showTagline, onChange: set( props, 'showTagline' ) } ),
			el( 'p', { className: 'components-base-control__help' }, __( 'The logo is shared by the whole site. Without a logo, a temporary Arabic wordmark is shown. The preview updates after reloading the editor.', 'zahrat-almaghrib' ) )
		);
	} );

	simple( NS + 'lead-stories', function ( props ) {
		var a = props.attributes;
		return el(
			Fragment,
			null,
			el( PostPicker, {
				label: __( 'Hand-picked stories (first = lead)', 'zahrat-almaghrib' ),
				emptyText: __( 'No stories picked: sticky posts, then the latest posts, are used.', 'zahrat-almaghrib' ),
				value: a.postIds,
				max: 1 + a.supporting,
				onChange: set( props, 'postIds' ),
			} ),
			el( c.RangeControl, { label: __( 'Supporting stories', 'zahrat-almaghrib' ), min: 2, max: 4, value: a.supporting, onChange: set( props, 'supporting' ) } ),
			el( c.ToggleControl, { label: __( 'Fill with sticky posts', 'zahrat-almaghrib' ), checked: a.useSticky, onChange: set( props, 'useSticky' ) } ),
			el( CategorySelect, { label: __( 'Limit automatic stories to a category', 'zahrat-almaghrib' ), emptyLabel: __( 'All categories', 'zahrat-almaghrib' ), value: a.category, onChange: set( props, 'category' ) } )
		);
	} );

	simple( NS + 'latest', function ( props ) {
		return el(
			Fragment,
			null,
			el( c.TextControl, { label: __( 'Title', 'zahrat-almaghrib' ), help: __( 'Leave empty for “Latest news”.', 'zahrat-almaghrib' ), value: props.attributes.title, onChange: set( props, 'title' ) } ),
			el( c.ToggleControl, { label: __( 'Show excerpts', 'zahrat-almaghrib' ), checked: props.attributes.excerpt, onChange: set( props, 'excerpt' ) } )
		);
	} );

	simple( NS + 'post-section', function ( props ) {
		var a = props.attributes;
		return el(
			Fragment,
			null,
			el( CategorySelect, { value: a.category, onChange: set( props, 'category' ) } ),
			el( c.TextControl, { label: __( 'Title', 'zahrat-almaghrib' ), help: __( 'Leave empty to use the category name.', 'zahrat-almaghrib' ), value: a.title, onChange: set( props, 'title' ) } ),
			el( c.SelectControl, {
				label: __( 'Layout', 'zahrat-almaghrib' ),
				value: a.layout,
				options: [
					{ value: 'feature', label: __( 'Feature + list', 'zahrat-almaghrib' ) },
					{ value: 'grid', label: __( 'Grid', 'zahrat-almaghrib' ) },
					{ value: 'portrait', label: __( 'Portrait grid', 'zahrat-almaghrib' ) },
					{ value: 'list', label: __( 'Column list', 'zahrat-almaghrib' ) },
					{ value: 'text', label: __( 'Text only (opinion)', 'zahrat-almaghrib' ) },
				],
				onChange: set( props, 'layout' ),
			} ),
			el( c.RangeControl, { label: __( 'Number of stories', 'zahrat-almaghrib' ), min: 2, max: 8, value: a.count, onChange: set( props, 'count' ) } ),
			el( c.SelectControl, {
				label: __( 'Background', 'zahrat-almaghrib' ),
				value: a.tone,
				options: [
					{ value: 'default', label: __( 'None', 'zahrat-almaghrib' ) },
					{ value: 'sand', label: __( 'Sand', 'zahrat-almaghrib' ) },
					{ value: 'night', label: __( 'Dark', 'zahrat-almaghrib' ) },
				],
				onChange: set( props, 'tone' ),
			} ),
			el( c.ToggleControl, { label: __( 'Show “More” link', 'zahrat-almaghrib' ), checked: a.showMore, onChange: set( props, 'showMore' ) } ),
			el( c.ToggleControl, { label: __( 'Show excerpts', 'zahrat-almaghrib' ), checked: a.excerpt, onChange: set( props, 'excerpt' ) } )
		);
	} );

	simple( NS + 'curated', function ( props ) {
		return el(
			Fragment,
			null,
			el( c.TextControl, { label: __( 'Title', 'zahrat-almaghrib' ), help: __( 'Leave empty for “Editors’ selection”.', 'zahrat-almaghrib' ), value: props.attributes.title, onChange: set( props, 'title' ) } ),
			el( PostPicker, {
				label: __( 'Stories', 'zahrat-almaghrib' ),
				emptyText: __( 'Pick 3, 4, 6 or 8 stories. The section stays hidden until you do.', 'zahrat-almaghrib' ),
				value: props.attributes.postIds,
				max: 8,
				onChange: set( props, 'postIds' ),
			} )
		);
	} );

	simple( NS + 'toc', function ( props ) {
		return el( c.RangeControl, { label: __( 'Minimum level-2 headings', 'zahrat-almaghrib' ), min: 2, max: 8, value: props.attributes.min, onChange: set( props, 'min' ) } );
	} );

	simple( NS + 'related', function ( props ) {
		return el( c.RangeControl, { label: __( 'Number of stories', 'zahrat-almaghrib' ), min: 2, max: 6, value: props.attributes.count, onChange: set( props, 'count' ) } );
	} );

	simple( NS + 'menu', function ( props ) {
		return el(
			Fragment,
			null,
			el( c.TextControl, { label: __( 'Menu location', 'zahrat-almaghrib' ), value: props.attributes.location, onChange: set( props, 'location' ) } ),
			el( c.TextControl, { label: __( 'Accessible label', 'zahrat-almaghrib' ), value: props.attributes.label, onChange: set( props, 'label' ) } )
		);
	} );

	simple( NS + 'archive-list', function ( props ) {
		return el( c.SelectControl, {
			label: __( 'Layout', 'zahrat-almaghrib' ),
			value: props.attributes.layout,
			options: [
				{ value: 'row', label: __( 'List', 'zahrat-almaghrib' ) },
				{ value: 'grid', label: __( 'Grid', 'zahrat-almaghrib' ) },
			],
			onChange: set( props, 'layout' ),
		} );
	} );

	[ 'breadcrumbs', 'share', 'featured-figure', 'byline', 'author-box', 'archive-header', 'copyright' ].forEach( function ( n ) {
		simple( NS + n, null );
	} );

	registerBlockType( NS + 'ad-slot', {
		edit: function ( props ) {
			var blockProps = be.useBlockProps( { className: 'zam-ad zam-ad--' + props.attributes.format + ' is-editing' } );
			return el(
				Fragment,
				null,
				el(
					be.InspectorControls,
					null,
					el(
						c.PanelBody,
						{ title: __( 'Settings', 'zahrat-almaghrib' ) },
						el( c.SelectControl, {
							label: __( 'Format (reserved space)', 'zahrat-almaghrib' ),
							value: props.attributes.format,
							options: [
								{ value: 'leaderboard', label: __( 'Leaderboard — 728 × 90 / mobile 320 × 100', 'zahrat-almaghrib' ) },
								{ value: 'rectangle', label: __( 'Rectangle — 300 × 250', 'zahrat-almaghrib' ) },
								{ value: 'inline', label: __( 'In-article — 300 × 250 / fluid', 'zahrat-almaghrib' ) },
							],
							onChange: set( props, 'format' ),
						} ),
						el( 'p', { className: 'components-base-control__help' }, __( 'Paste the ad code into the Custom HTML block. The slot and its “Advertisement” label stay hidden on the site while it is empty.', 'zahrat-almaghrib' ) )
					)
				),
				el(
					'div',
					blockProps,
					el( 'p', { className: 'zam-ad__label' }, __( 'Advertisement', 'zahrat-almaghrib' ) ),
					el( be.InnerBlocks, { allowedBlocks: [ 'core/html', 'core/shortcode' ], template: [ [ 'core/html' ] ], templateLock: false } )
				)
			);
		},
		save: function () {
			return el( be.InnerBlocks.Content );
		},
	} );
} )( window.wp );
