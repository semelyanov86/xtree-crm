/**
 * Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/* exported initSample */

if ( CKEDITOR.env.ie && CKEDITOR.env.version < 9 )
	CKEDITOR.tools.enableHtml5Elements( document );

// The trick to keep the editor in the sample quite small
// unless user specified own height.
CKEDITOR.config.height = 150;
CKEDITOR.config.width = 'auto';

var initSample = ( function() {
	var wysiwygareaAvailable = isWysiwygareaAvailable(),
		isBBCodeBuiltIn = !!CKEDITOR.plugins.get( 'bbcode' );

	return function() {
		var editorElement = CKEDITOR.document.getById( 'editor' );

		// :(((
		if ( isBBCodeBuiltIn ) {
			editorElement.setHtml(
				'Hello world!\n\n' +
				'I\'m an instance of [url=https://ckeditor.com]CKEditor[/url].'
			);
		}

		// Depending on the wysiwygarea plugin availability initialize classic or inline editor.
		if ( wysiwygareaAvailable ) {
			CKEDITOR.replace( 'editor' );
		} else {
			editorElement.setAttribute( 'contenteditable', 'true' );
			CKEDITOR.inline( 'editor' );

			// TODO we can consider displaying some info box that
			// without wysiwygarea the classic editor may not work.
		}
	};

	function isWysiwygareaAvailable() {
		// If in development mode, then the wysiwygarea must be available.
		// Split REV into two strings so builder does not replace it :D.
		if ( CKEDITOR.revision == ( '%RE' + 'V%' ) ) {
			return true;
		}

		return !!CKEDITOR.plugins.get( 'wysiwygarea' );
	}
} )();

CKEDITOR.plugins.add( 'ITS4YouAutovariables', {
	requires: 'textmatch,autocomplete',

	init: function( editor ) {
		editor.on( 'instanceReady', function() {
			let config = {},
				placeholder = [
					{
						id: 1,
						name: 'name',
						value: '$value$',
						label: 'label',
						title: 'title',
						module: 'module',
						class: 'type',
					}
				];

			function textTestCallback( range ) {
				if ( !range.collapsed ) {
					return null;
				}

				return CKEDITOR.plugins.textMatch.match( range, matchCallback );
			}

			function matchCallback( text, offset ) {
				let pattern = /\$([A-z]|\$)*$/,
					match = text.slice( 0, offset ).match( pattern );

				if ( !match ) {
					return null;
				}

				return {
					start: match.index,
					end: offset
				};
			}

			function dataCallback( matchInfo, callback ) {
				let data = placeholder.filter( function( item ) {
					let itemName = '$' + item.name + '$';

					return itemName.indexOf( matchInfo.query.toLowerCase() ) === 0;
				} );

				callback( data );
			}

			config.throttle = 1000;
			config.textTestCallback = textTestCallback;
			config.dataCallback = dataCallback;
			config.itemTemplate = '<li class="{class}" data-id="{id}" title="{title}"><div class="titleAC"><b>{label}</b> <i>{module} {title}</i></div><div>{value}</div></li>';
			config.outputTemplate = '<span>{value}</span>';

			new CKEDITOR.plugins.autocomplete( editor, config ); 
		} );
	}
});

CKEDITOR.config.extraPlugins = 'wysiwygarea,textwatcher,textmatch,autocomplete,ITS4YouAutovariables';

