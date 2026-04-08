( function () {
	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var ToggleControl = wp.components.ToggleControl;

	var withRecentlyUpdated = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			if ( props.name !== 'core/query' ) {
				return el( BlockEdit, props );
			}

			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var query = attributes.query;
			var isRecentlyUpdated = query.orderBy === 'modified';

			var toggle = el( ToggleControl, {
				label: 'Sort by most recently updated',
				checked: isRecentlyUpdated,
				onChange: function ( value ) {
					setAttributes( {
						query: Object.assign( {}, query, {
							orderBy: value ? 'modified' : 'date',
							order: 'desc',
						} ),
					} );
				},
			} );

			var panel = el(
				InspectorControls,
				null,
				el( PanelBody, { title: 'Recently Updated', initialOpen: false }, toggle )
			);

			return el( Fragment, null, el( BlockEdit, props ), panel );
		};
	}, 'withRecentlyUpdated' );

	addFilter( 'editor.BlockEdit', 'richmond-law/query-recently-updated', withRecentlyUpdated );
} )();
