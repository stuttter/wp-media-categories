/* global _, filters, wp_media_categories_taxonomies */
window.wp = window.wp || { };

( function ( $ ) {

	var media = wp.media,
		curAttachmentsBrowser = media.view.AttachmentsBrowser;

	media.view.AttachmentFilters.Taxonomy = media.view.AttachmentFilters.extend( {
		tagName: 'select',
		createFilters: function () {
			var filters = { },
				that    = this;

			_.each( that.options.termList || { }, function ( term, key ) {
				var term_id = term['term_id'],
					term_name = $( '<div/>' ).html( term['term_name'] ).text();

				filters[ term_id ] = {
					text:     term_name,
					priority: key + 2
				};

				filters[term_id]['props'] = { };
				filters[term_id]['props'][that.options.taxonomy] = term_id;
			} );

			filters.all = {
				text:     that.options.termListTitle,
				priority: 1
			};

			filters['all']['props'] = { };
			filters['all']['props'][that.options.taxonomy] = null;

			this.filters = filters;
		}
	} );

	media.view.AttachmentsBrowser = media.view.AttachmentsBrowser.extend( {
		createToolbar: function () {
			var that    = this,
				i       = 1,
				filters = that.options.filters;

			curAttachmentsBrowser.prototype.createToolbar.apply( this, arguments );

			$.each( wp_media_categories_taxonomies, function ( taxonomy, values ) {
				if ( values.term_list && filters ) {
					that.toolbar.set( taxonomy + '-filter', new media.view.AttachmentFilters.Taxonomy( {
						controller:    that.controller,
						model:         that.collection.props,
						priority:      -80 + 10 * i++,
						taxonomy:      taxonomy,
						termList:      values.term_list,
						termListTitle: values.list_title,
						className:     'attachment-filters'
					} ).render() );
				}
			} );
		}
	} );

	// Relocate the new dropdown
	$( document ).ready( function() {
		$( '.media-toolbar.wp-filter .delete-selected-button' ).before( $('.media-toolbar.wp-filter .select-mode-toggle-button' ) );
	} );
} )( jQuery );
