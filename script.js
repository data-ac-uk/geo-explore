
$(document).ready(function(){
  var game;

  if (!("geolocation" in navigator)) {
    doom("Sorry, you need geolocation for this system to work. Either it's disabled or your phone don't do that.");
    return;
  }

  if( window.location.host == 'lemur.ecs.soton.ac.uk' ) {
    runGame(true);
  } else {
    runGame(false);
  }

  function initGPS() {
    var geo = navigator.geolocation;
    setInterval( function() {  
      geo.getCurrentPosition(
        getUpdate,
        geoFail,
        {enableHighAccuracy:true});
    }, 5000 );
    geo.getCurrentPosition(
      getUpdate,
      geoFail,
      {enableHighAccuracy:true});
  }
  function error_callback() {
    alert( "GEOFAIL");
  }
    

  function runGame(debug) {

    map = L.map('map').setView([50.59466,-1.20618], 18);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 19
    }).addTo(map);

   
    if( debug ) { 
      map.on('click', function(e) { setUserPos( [e.latlng.lat,e.latlng.lng] ); } );
    } else {
      getUpdate = function(p){ setUserPos( [p.coords.latitude, p.coords.longitude ] ); }
      initGPS();
    }
  }

  function ll2tile(ll,zoom) { 
    return [ 
      (Math.floor((1-Math.log(Math.tan(ll[0]*Math.PI/180) + 1/Math.cos(ll[0]*Math.PI/180))/Math.PI)/2 *Math.pow(2,zoom))),
      (Math.floor((ll[1]+180)/360*Math.pow(2,zoom))) ];
  }
 
  function tile2ll(xy,zoom) {
    var n=Math.PI-2*Math.PI*xy[0]/Math.pow(2,zoom);
    return [
      (180/Math.PI*Math.atan(0.5*(Math.exp(n)-Math.exp(-n)))),
      (xy[1]/Math.pow(2,zoom)*360-180)
    ];
  }

  var userMarker=false;
  function setUserPos( ll ) {
    // map.setView( ll ); // don't centre on the player (automatically)
    if( !userMarker ) {
      var icon = L.icon( { 
        iconUrl: 'hats.png',
        iconSize: [32, 37],
        labelAnchor: [16, -18],
        iconAnchor: [16, 37]
      } );
      userMarker = L.marker(ll,{ icon: icon } );
      userMarker.addTo(map);
    }
    userMarker.setLatLng(ll);
    userPos = L.latLng(ll);

    var SIZE=22;
    grid = ll2tile( ll, SIZE );
    L.polygon( 
      [ 
        tile2ll( [ grid[0],grid[1] ], SIZE ),
        tile2ll( [ grid[0],grid[1]+1 ], SIZE ),
        tile2ll( [ grid[0]+1,grid[1]+1 ], SIZE ),
        tile2ll( [ grid[0]+1,grid[1] ], SIZE )
      ], {
        stroke: false,
        fillOpacity: 0.5
      }
     ).addTo(map);

  }

});


