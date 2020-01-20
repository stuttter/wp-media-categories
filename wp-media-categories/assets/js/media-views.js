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

/* (c) Ian Dickety */
/*
* save-attachment-compat doesn't do as save-attachment does, in that
* while it pings ajax upon updating, there is no feedback to tell
* the user the input has been saved. This snippet provides feedback (loading/succesful only)
*/

jQuery(function($) {
	$('#wpcontent').ajaxSend(function(e,r,s) {
		var input = QueryStringToHash(s.data);
		if (input.action == "save-attachment-compat") {
			$( '.attachment-details ').removeClass('save-ready').addClass('save-waiting');
		}
	});

	$('#wpcontent').ajaxComplete(function(e,r,s) {
		var input = QueryStringToHash(s.data);
		if (input.action == "save-attachment-compat") {
			$( '.attachment-details ').removeClass('save-waiting');
			if (r.responseJSON.success === true) {
				$( '.attachment-details ').addClass("save-complete").delay(2000).queue(function(next){
					$(this).removeClass("save-complete").addClass("save-ready");
					next();
				});
			} else {
				$( '.attachment-details ').addClass("save-ready");
			}
		}
	});

	var QueryStringToHash = function QueryStringToHash (query) {
		var query_string = {};
		var vars = query.split("&");
		for (var i=0;i<vars.length;i++) {
			var pair = vars[i].split("=");
			pair[0] = decodeURIComponent(pair[0]);
			pair[1] = decodeURIComponent(pair[1]);
				// If first entry with this name
			if (typeof query_string[pair[0]] === "undefined") {
				query_string[pair[0]] = pair[1];
				// If second entry with this name
			} else if (typeof query_string[pair[0]] === "string") {
				var arr = [ query_string[pair[0]], pair[1] ];
				query_string[pair[0]] = arr;
				// If third or later entry with this name
			} else {
				query_string[pair[0]].push(pair[1]);
			}
		}
		return query_string;
	}

});
