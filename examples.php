<!DOCTYPE html>
<html lang="en">
        <head>
                <meta charset="utf-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
                <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
                <title>Examples | Geo-Explorer</title>

                <!-- jQuery -->
                <script src="jquery.min.js"></script>

                <!-- Bootstrap -->
                <link href="styles/bootstrap.min.css" rel="stylesheet">
                <script src="js/bootstrap.min.js"></script>
                <!-- Leaflet -->
                <link rel="stylesheet" href="leaflet.css" />
                <script src="leaflet.js"></script>

                <!-- Misc -->
                <script src="script.js"></script>
                <link rel="stylesheet" type="text/css" media="all" href="style.css" />

                <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
                <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
                <!--[if lt IE 9]>
                <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
                <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
                <![endif]-->
        </head>
        <body>
                <div class="container">
                        <div class="row">
                                <div class="col-lg-12">
                                        <h2>Geo-Explorer Examples</h2>
                                </div>
                        </div>

			<div class="row">
                                <div class="col-lg-12">
                                        <h3>WFS - Web Feature Service</h3>
					<ul>
						<li><a href="index.php?url=http%3A%2F%2Finspire.dundeecity.gov.uk%2Fgeoserver%2Finspire%2Fows%3Fservice%3DWFS%26version%3D2.0.0%26request%3DgetCapabilities">Dundee City Council</a></li>
						<li><a href="index.php?url=http%3A%2F%2Fapp.newark-sherwooddc.gov.uk%2Farcgisserver%2Fservices%2FINSPIRE%2FINSPIRE_WFS%2FMapServer%2FWFSServer%3Frequest%3DGetCapabilities%26service%3DWFS">Newark-Sherwood District Council</a></li>
						<li><a href="index.php?url=http%3A%2F%2Fenvironment.data.gov.uk%2Fds%2Fwfs%3FSERVICE%3DWFS%26INTERFACE%3DENVIRONMENTWFS--0aa3ac30e4dc229fd911215e73cc844d%26request%3DGetCapabilities">UK Government Environmental Data</a></li>
					</ul>
                                </div>
                        </div>

			<div class="row">
                                <div class="col-lg-12">
                                        <h3>WMS - Web Mapping Service</h3>
                                        <ul>
                                                <li><a href="index.php?url=http%3A%2F%2Fapps.luton.gov.uk%2Finspire%2Fwms.exe%3FREQUEST%3DGETCAPABILITIES%26SERVICE%3DWMS">Luton Town Council</a></li>
						<li><a href="index.php?url=http%3A%2F%2Farcgisweb.fife.gov.uk%2Fgeoserver%2Ffife%2Fows%3Frequest%3DgetCapabilities%26version%3D1.3.0%26service%3DWMS">Fife Council</a></li>
						<li><a href="index.php?url=http%3A%2F%2Fdata.nottinghamshire.gov.uk%2Fgeoserver%2Fwms%3Frequest%3DGetCapabilities">Nottinghamshire County Council</a></li>
                                        </ul>
                                </div>
                        </div>


