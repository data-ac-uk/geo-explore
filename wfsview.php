<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>GEOPAINT</title>
  <link rel="stylesheet" href="leaflet.css" />
  <link rel="stylesheet" type="text/css" media="all" href="style.css" />
  <script src="leaflet.js"></script>
  <script src="jquery.min.js"></script>
  <script src="Leaflet-WFST.src.js"></script>
</head>

<body>
  <div id='mapView'> 
    <div id="map">
      <div id="init">Initialising Data Stuff</div>
    </div>
  </div>
</body>
</html>
<script>
$(document).ready(function(){

    var map = L.map('map').setView( [50.93548,-1.396150], 12 );

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
        maxZoom: 17
    }).addTo(map);
    L.tileLayer('https://tiles.maps.southampton.ac.uk/aer/{z}/{x}/{y}.png', {
		attribution: 'Aerial photography &copy; Hampshire County Council',
		maxZoom: 19
    }).addTo(map);


L.WFST.include(MultiEditableMixin);

var wfst = new L.WFST({
    url: '<?php print $_GET['endpoint']; ?>',
    typeNS: '<?php print $_GET['namespace']; ?>',
    typeName: '<?php print $_GET['term']; ?>',
    style: {
        color: 'blue',
        weight: 2
    }
}).addTo(map).once('load', function () {
       alert(23);
            map.fitBounds(wfst);
            wfst.enableEdit();
        });

map.on('editable:created', function (e) {
    wfst.addLayer(e.layer);
});

map.on('editable:editing', function (e) {
    wfst.editLayer(e.layer);
});





});

</script>
