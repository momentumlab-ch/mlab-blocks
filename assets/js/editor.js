/* MomentumLab Blocks: ein Formular für alle Blöcke. Ausgewählt zeigt der Block seine Felder, sonst die Vorschau. */
( function ( wp, config ) {
	'use strict';

	if ( ! wp || ! config ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var ServerSideRender = wp.serverSideRender;

	var presetOptions = [ { value: '', label: __( '— kein —', 'mlab-blocks' ) } ].concat(
		Object.keys( config.presets || {} ).map( function ( key ) {
			return { value: key, label: config.presets[ key ] };
		} )
	);
	var offerOptions = [ { value: '', label: __( '— ohne Vorauswahl —', 'mlab-blocks' ) } ].concat(
		( config.offers || [] ).map( function ( offer ) {
			return { value: offer, label: offer };
		} )
	);

	function move( list, index, step ) {
		var copy = list.slice();
		var target = index + step;
		if ( target < 0 || target >= copy.length ) {
			return copy;
		}
		var item = copy.splice( index, 1 )[ 0 ];
		copy.splice( target, 0, item );
		return copy;
	}

	function rowTools( list, index, onChange, label ) {
		return el( 'div', { className: 'mlab-b-rowtools' },
			el( c.Button, { icon: 'arrow-up-alt2', label: __( 'Nach oben', 'mlab-blocks' ), disabled: index === 0, onClick: function () {
				onChange( move( list, index, -1 ) );
			} } ),
			el( c.Button, { icon: 'arrow-down-alt2', label: __( 'Nach unten', 'mlab-blocks' ), disabled: index === list.length - 1, onClick: function () {
				onChange( move( list, index, 1 ) );
			} } ),
			el( c.Button, { icon: 'trash', isDestructive: true, label: label, onClick: function () {
				var copy = list.slice();
				copy.splice( index, 1 );
				onChange( copy );
			} } )
		);
	}

	function imageField( field, value, onChange ) {
		value = value || {};
		return el( 'div', { className: 'mlab-b-field mlab-b-image' },
			el( 'span', { className: 'mlab-b-label' }, field.label ),
			value.url ? el( 'img', { src: value.url, alt: '' } ) : null,
			el( be.MediaUploadCheck, null,
				el( be.MediaUpload, {
					allowedTypes: [ 'image' ],
					value: value.id,
					onSelect: function ( media ) {
						var size = media.sizes && ( media.sizes.large || media.sizes.full );
						onChange( { id: media.id, url: size ? size.url : media.url, alt: media.alt || '' } );
					},
					render: function ( props ) {
						return el( 'div', { className: 'mlab-b-imagebuttons' },
							el( c.Button, { variant: 'secondary', onClick: props.open }, value.url ? __( 'Bild ändern', 'mlab-blocks' ) : __( 'Bild wählen', 'mlab-blocks' ) ),
							value.url ? el( c.Button, { variant: 'link', isDestructive: true, onClick: function () {
								onChange( {} );
							} }, __( 'Entfernen', 'mlab-blocks' ) ) : null
						);
					}
				} )
			)
		);
	}

	function listField( field, value, onChange ) {
		var list = Array.isArray( value ) ? value : [];
		return el( 'div', { className: 'mlab-b-field mlab-b-list' },
			el( 'span', { className: 'mlab-b-label' }, field.label ),
			list.map( function ( item, index ) {
				return el( 'div', { className: 'mlab-b-listrow', key: index },
					el( c.TextControl, { __nextHasNoMarginBottom: true, value: item, label: field.label + ' ' + ( index + 1 ), hideLabelFromVision: true, onChange: function ( text ) {
						var copy = list.slice();
						copy[ index ] = text;
						onChange( copy );
					} } ),
					rowTools( list, index, onChange, __( 'Entfernen', 'mlab-blocks' ) )
				);
			} ),
			el( c.Button, { variant: 'secondary', icon: 'plus', onClick: function () {
				onChange( list.concat( [ '' ] ) );
			} }, field.add || __( 'Hinzufügen', 'mlab-blocks' ) )
		);
	}

	function repeaterField( field, value, onChange ) {
		var list = Array.isArray( value ) ? value : [];
		return el( 'div', { className: 'mlab-b-field mlab-b-repeater' },
			el( 'span', { className: 'mlab-b-label' }, field.label ),
			list.map( function ( item, index ) {
				var title = '';
				field.fields.some( function ( sub ) {
					if ( typeof item[ sub.key ] === 'string' && item[ sub.key ] !== '' && sub.type !== 'image' ) {
						title = item[ sub.key ];
						return true;
					}
					return false;
				} );
				return el( c.PanelBody, { key: index, className: 'mlab-b-item', initialOpen: ! title, title: ( index + 1 ) + '. ' + ( title || __( 'Neuer Eintrag', 'mlab-blocks' ) ) },
					field.fields.map( function ( sub ) {
						return control( sub, item[ sub.key ], function ( next ) {
							var copy = list.slice();
							copy[ index ] = Object.assign( {}, item );
							copy[ index ][ sub.key ] = next;
							onChange( copy );
						} );
					} ),
					rowTools( list, index, onChange, __( 'Eintrag entfernen', 'mlab-blocks' ) )
				);
			} ),
			el( c.Button, { variant: 'secondary', icon: 'plus', onClick: function () {
				var fresh = {};
				field.fields.forEach( function ( sub ) {
					if ( sub.default !== undefined ) {
						fresh[ sub.key ] = sub.default;
					}
				} );
				onChange( list.concat( [ fresh ] ) );
			} }, field.add || __( 'Hinzufügen', 'mlab-blocks' ) )
		);
	}

	function control( field, value, onChange ) {
		var common = { key: field.key, label: field.label, help: field.help, __nextHasNoMarginBottom: true };
		switch ( field.type ) {
			case 'textarea':
				return el( c.TextareaControl, Object.assign( common, { value: value || '', rows: 4, onChange: onChange } ) );
			case 'url':
				return el( c.TextControl, Object.assign( common, { value: value || '', type: 'text', inputMode: 'url', placeholder: '/seite/ oder https://…', onChange: onChange } ) );
			case 'toggle':
				return el( c.ToggleControl, Object.assign( common, { checked: !! value, onChange: onChange } ) );
			case 'select':
				return el( c.SelectControl, Object.assign( common, { value: value || field.default || '', options: field.options.map( function ( option ) {
					return { value: option[ 0 ], label: option[ 1 ] };
				} ), onChange: onChange } ) );
			case 'preset':
				return presetOptions.length > 1 ? el( c.SelectControl, Object.assign( common, { value: value || '', options: presetOptions, onChange: onChange } ) ) : null;
			case 'angebot':
				return offerOptions.length > 1 ?
					el( c.SelectControl, Object.assign( common, { value: value || '', options: offerOptions, onChange: onChange } ) ) :
					el( c.TextControl, Object.assign( common, { value: value || '', onChange: onChange } ) );
			case 'image':
				return el( Fragment, { key: field.key }, imageField( field, value, onChange ) );
			case 'list':
				return el( Fragment, { key: field.key }, listField( field, value, onChange ) );
			case 'repeater':
				return el( Fragment, { key: field.key }, repeaterField( field, value, onChange ) );
			default:
				return el( c.TextControl, Object.assign( common, { value: value || '', onChange: onChange } ) );
		}
	}

	function form( block, props ) {
		return el( 'div', { className: 'mlab-b-form' },
			el( 'div', { className: 'mlab-b-formtitle' }, block.title ),
			block.fields.map( function ( field ) {
				return control( field, props.attributes[ field.key ], function ( next ) {
					var change = {};
					change[ field.key ] = next;
					props.setAttributes( change );
				} );
			} ),
			el( 'p', { className: 'mlab-b-hint' }, __( 'Zum Ansehen ausserhalb des Blocks klicken.', 'mlab-blocks' ) )
		);
	}

	/* Abschnitte zeigen statt einer leeren Vorschau eine Zeile mit Titel und Hintergrund. */
	function bar( block, props ) {
		var a = props.attributes;
		var parts = [ a.title || a.kicker || '' ];
		var bg = ( block.fields.filter( function ( f ) {
			return f.key === 'background';
		} )[ 0 ] || { options: [] } ).options.filter( function ( o ) {
			return o[ 0 ] === ( a.background || 'hell' );
		} )[ 0 ];
		if ( bg ) {
			parts.push( bg[ 1 ] );
		}
		return el( 'div', { className: 'mlab-b-bar' }, el( 'b', null, block.title ), ' ', parts.filter( Boolean ).join( ' · ' ) );
	}

	function preview( name, props ) {
		return el( 'div', { className: 'mlab-b-preview' },
			el( ServerSideRender, { block: name, attributes: props.attributes, httpMethod: 'POST' } )
		);
	}

	( config.blocks || [] ).forEach( function ( block ) {
		block.title = block.title || block.name;

		wp.blocks.registerBlockType( block.name, {
			edit: function ( props ) {
				var blockProps = be.useBlockProps( { className: 'mlab-b-block' } );
				if ( block.container ) {
					return el( 'div', blockProps,
						props.isSelected ? form( block, props ) : ( block.preview === 'bar' ? bar( block, props ) : preview( block.name, props ) ),
						el( 'div', { className: 'mlab-b-inner' },
							block.innerLabel ? el( 'span', { className: 'mlab-b-innerlabel' }, block.innerLabel ) : null,
							el( be.InnerBlocks, { allowedBlocks: block.allowed || undefined, renderAppender: be.InnerBlocks.ButtonBlockAppender } )
						)
					);
				}
				return el( 'div', blockProps, props.isSelected ? form( block, props ) : preview( block.name, props ) );
			},
			save: function () {
				return block.container ? el( be.InnerBlocks.Content ) : null;
			}
		} );
	} );
}( window.wp, window.mlabBlocks ) );
