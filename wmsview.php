<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>WMS Viewer</title>
  <link rel="stylesheet" href="leaflet.css" />
  <link rel="stylesheet" type="text/css" media="all" href="style.css" />
  <script src="leaflet.js"></script>
  <script src="jquery.min.js"></script>
</head>

<body>
  <div id='mapView'> 
    <div id="map">
      <div id="init"></div>
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
		maxZoom: 18
    }).addTo(map);

    var wmsLayer = L.tileLayer.wms('<?php print $_GET['endpoint']; ?>', {
        layers: '<?php print $_GET['layer']; ?>', 
        format: 'image/png',
        opacity: 1,
        transparent: true
    }).addTo(map);


});

</script>
