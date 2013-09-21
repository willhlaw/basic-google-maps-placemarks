/**
 * @package BasicGoogleMapsPlacemarks
 * @author Ian Dunn <ian@iandunn.name>
 * @link http://wordpress.org/extend/plugins/basic-google-maps-placemarks/
 */


/**
 * Wrapper function to safely use $
 * @author Ian Dunn <ian@iandunn.name>
 */
function bgmp_wrapper( $ )
{
	// @todo - figure out if wrapper bad for memory consumption (https://developer.mozilla.org/en/JavaScript/Reference/Functions_and_function_scope#Efficiency_considerations)
		// ask on stackoverflow
	
	$.bgmp = 
	{
		/**
		 * Main entry point
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		init : function()
		{
			// Initialize variables
			$.bgmp.prefix				= 'bgmp_';
			$.bgmp.name					= 'Basic Google Maps Placemarks';
			$.bgmp.canvas				= document.getElementById( $.bgmp.prefix + 'map-canvas' );		// We have to use getElementById instead of a jQuery selector here in order to pass it to the Maps API.
			$.bgmp.map					= undefined;
			$.bgmp.markerClusterer		= undefined;
			$.bgmp.markers				= {};
			$.bgmp.infoWindowContent	= {};
			
			// Initialize single info window to reuse for each placemark
			$.bgmp.infoWindow = new google.maps.InfoWindow( {
				content		: '',
				maxWidth	: bgmpData.options.infoWindowMaxWidth
			} );
			
			// Format numbers
			bgmpData.options.zoom					= parseInt( bgmpData.options.zoom ),
			bgmpData.options.latitude				= parseFloat( bgmpData.options.latitude );
			bgmpData.options.longitude				= parseFloat( bgmpData.options.longitude );
			bgmpData.options.clustering.maxZoom		= parseInt( bgmpData.options.clustering.maxZoom );
			bgmpData.options.clustering.gridSize	= parseInt( bgmpData.options.clustering.gridSize );
			
			// Register event handlers
			$( '.' + $.bgmp.prefix + 'list' ).find( 'a' ).filter( '.' + $.bgmp.prefix + 'view-on-map' ).click( $.bgmp.viewOnMap ); 
								
			// Build map
			if( $.bgmp.canvas )
				$.bgmp.buildMap();
			else
				$( $.bgmp.canvas ).html( $.bgmp.name + " error: couldn't retrieve DOM elements." );
		},
		
		/**
		 * Pull in the map options from Wordpress' database and create the map
		 * @author Ian Dunn <ian@iandunn.name>
		 */
		buildMap : function()
		{
			var mapOptions;
			
			if( bgmpData.options.mapWidth == '' || bgmpData.options.mapHeight == '' || bgmpData.options.latitude == '' || bgmpData.options.longitude == '' || bgmpData.options.zoom == '' || bgmpData.options.infoWindowMaxWidth == '' )
			{
				// @todo update w/ cluster options?
				
				$( $.bgmp.canvas ).html( $.bgmp.name + " error: map options not set." );
				return;
			}
			
			mapOptions = 
			{
				'zoom'						: bgmpData.options.zoom,
				'center'					: new google.maps.LatLng( bgmpData.options.latitude, bgmpData.options.longitude ),
				'mapTypeId'					: google.maps.MapTypeId[ bgmpData.options.type ],
				'mapTypeControl'			: bgmpData.options.typeControl == 'off' ? false : true,
				'mapTypeControlOptions'		: { style: google.maps.MapTypeControlStyle[ bgmpData.options.typeControl ] },
				'navigationControl'			: bgmpData.options.navigationControl == 'off' ? false : true,
				'navigationControlOptions'	: { style: google.maps.NavigationControlStyle[ bgmpData.options.navigationControl ] },
				'streetViewControl'			: bgmpData.options.streetViewControl
			};
			
			// Override default width/heights from settings
			$( '#' + $.bgmp.prefix + 'map-canvas' ).css( 'width', bgmpData.options.mapWidth );		// @todo use $.bgmp.canvas intead of hardcoding it?
			$( '#' + $.bgmp.prefix + 'map-canvas' ).css( 'height', bgmpData.options.mapHeight );
			// @todo this prevents users from using their own stylesheet?
			
			
			// Create the map
			try
			{
				$.bgmp.map = new google.maps.Map( $.bgmp.canvas, mapOptions );
			}
			catch( e )
			{
				$( $.bgmp.canvas ).html( $.bgmp.name + " error: couln't build map." );
				if( window.console )
					console.log( $.bgmp.prefix + 'buildMap: '+ e );
					
				return;
			}
			
			$.bgmp.addPlacemarks( $.bgmp.map );		// @todo not supposed to add them when clustering is enabled? http://www.youtube.com/watch?v=Z2VF9uKbQjI
			
			
			// Activate marker clustering
			if( bgmpData.options.clustering.enabled )
			{
				// BGMP stores markers in an object for direct access (e.g., markers[ 15 ] for ID 15), but MarkerCluster requires an array instead, so we convert them 
				var markersArray = [];
				for( var m in $.bgmp.markers )
					markersArray.push( $.bgmp.markers[ m ] );
				
				$.bgmp.markerClusterer = new MarkerClusterer(
					$.bgmp.map,
					markersArray,
					{
						maxZoom		: bgmpData.options.clustering.maxZoom,
						gridSize	: bgmpData.options.clustering.gridSize,
						styles		: bgmpData.options.clustering.styles[ bgmpData.options.clustering.style ]
					}
				);
			}
		},
		
		/**
		 * Checks if the value is an integer. Slightly modified version of original.
		 * @author Invent Partners
		 * @link http://www.inventpartners.com/content/javascript_is_int
		 * @param mixed value
		 * @return bool
		 */
		isInt : function( value )
		{
			if( !isNaN( value ) && parseFloat( value ) == parseInt( value ) )
				return true;
			else
				return false;
		},

		/**
		 * Pull the placemark posts from Wordpress' database and add them to the map
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param object map Google Maps map
		 */
		addPlacemarks : function( map )
		{
			// @todo - should probably refactor this since you pulled out the ajax. update phpdoc too
			
			if( bgmpData.markers.length > 0 )
			{
				for( var m in bgmpData.markers )
				{
					$.bgmp.createMarker(
						map,
						bgmpData.markers[ m ][ 'id' ],
						bgmpData.markers[ m ][ 'title' ],
						bgmpData.markers[ m ][ 'latitude' ],
						bgmpData.markers[ m ][ 'longitude' ],
						bgmpData.markers[ m ][ 'details' ],
						bgmpData.markers[ m ][ 'icon' ],
						parseInt( bgmpData.markers[ m ][ 'zIndex' ] )
					);
				}
			}
		},

		/**
		 * Create a marker with an information window
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param object map Google Maps map
		 * @param int id ID of the marker post
		 * @param string title Placemark title
		 * @param float latituded
		 * @param float longitude
		 * @param string details Content of the infowinder
		 * @param string icon URL of the icon
		 * @param int zIndex The desired position in the placemark stacking order
		 * @return bool True on success, false on failure
		 */
		createMarker : function( map, id, title, latitude, longitude, details, icon, zIndex )
		{
			var infoWindowContent, marker;
			
			if( isNaN( latitude ) || isNaN( longitude ) )
			{
				if( window.console )
					console.log( $.bgmp.prefix + "createMarker(): "+ title +" latitude and longitude weren't valid." );
					
				return false;
			}
			
			if( icon == null )
			{
				// @todo - this check may not be needed anymore
				
				if( window.console )
					console.log( $.bgmp.prefix + "createMarker(): "+ title +"  icon wasn't passed in." );
				return false;
			}
			
			if( !$.bgmp.isInt( zIndex ) )
			{
				//if( window.console )
					//console.log( $.bgmp.prefix + "createMarker():  "+ title +" z-index wasn't valid." );	// this would fire any time it's empty
				
				zIndex = 0;
			}
			
			infoWindowContent = '<div class="'+ $.bgmp.prefix + 'placemark"> <h3>'+ title +'</h3> <div>'+ details +'</div> </div>';
			
			try
			{	
				// Replace commas with periods. Some (human) languages use commas to delimit the fraction from the whole number, but Google Maps doesn't accept that.
				latitude = parseFloat( latitude.replace( ',', '.' ) );
				longitude = parseFloat( longitude.replace( ',', '.' ) );
				
				marker = new google.maps.Marker( {
					'bgmpID'	: id,
					'position'	: new google.maps.LatLng( latitude, longitude ),
					'map'		: map,
					'icon'		: icon,
					'title'		: title,
					'zIndex'	: zIndex
				} );
				
				$.bgmp.markers[ id ] = marker;
				$.bgmp.infoWindowContent[ id ] = infoWindowContent;
				
				google.maps.event.addListener( marker, 'click', function() 
				{
					$.bgmp.openInfoWindow( map, marker, infoWindowContent );
				} );
				
				return true;
			}
			catch( e )
			{
				//$( $.bgmp.canvas ).append( '<p>' + $.bgmp.name + " error: couldn't add map placemarks.</p>");		// add class for making red? other places need this too?	// @todo - need to figure out a good way to alert user that placemarks couldn't be added
				if( window.console )
					console.log( $.bgmp.prefix + 'createMarker: '+ e );
			}
		},
		
		/**
		 * Opens an info window on the map
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param object map
		 * @param object marker
		 * @param string infoWindowContent
		 */
		openInfoWindow : function( map, marker, infoWindowContent )
		{
			$.bgmp.infoWindow.setContent( infoWindowContent );
			$.bgmp.infoWindow.open( map, marker );

			if( bgmpData.options.viewOnMapScroll )
			{			
				$( 'html, body' ).animate(
				{
					scrollTop: $( '#' + $.bgmp.prefix + 'map-canvas' ).offset().top
				}, 900 );
			}
		},
		
		/**
		 * Focuses the [bgmp-map] on the marker that corresponds to the [bgmp-list] link that was clicked
		 * @author Ian Dunn <ian@iandunn.name>
		 * @param object event
		 */
		viewOnMap : function( event )
		{
			var id = $( this ).data( 'marker-id' );
			$.bgmp.openInfoWindow( $.bgmp.map, $.bgmp.markers[ id ], $.bgmp.infoWindowContent[ id ] );
		}
	}; // end bgmp
	
	// Kick things off...
	$( document ).ready( $.bgmp.init );
	
} // end bgmp_wrapper()

bgmp_wrapper( jQuery );