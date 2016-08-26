<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

	$url = null;
	$debug = false;
	
	if (isset($_GET['url'])) 
	{
		$url = $_GET['url'];
	}
	
	if (isset($_GET['debug']))
	{
		$debug = $_GET['debug'];
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<title>Geo-Explorer</title>
		
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
					<h1>Geo-Explorer</h2>							
				</div>
			</div>
						
			<div class="row">
				<div class="col-lg-12">
					<form action="wmshack.php" method="get" class="form-horizontal">
						<fieldset>
							<legend>Data Settings</legend>
							<div class="form-group">
								<label for="url" class="col-lg-2 control-label">URL</label>
								<div class="col-lg-10">
									<input class="form-control" id="url" name="url" type="text"
											<?php if (!isset($url)) { print "placeholder=\"e.g. http://www.southampton.gov.uk/geoserver/Inspire/wms?service=wfs&version=1.3.0&request=GetCapabilities\""; } ?>
											<?php if (isset($url)) { print "value=\"" . $url . "\""; } ?> 
									/>
								</div>
							</div>
							<div class="form-group">			      
								<div class="col-lg-10 col-lg-offset-2">			        
									<div class="checkbox">
										<label>
											<input type="checkbox" name="debug" value="1"> Debug?
										</label>
									</div>
								</div>
							</div>
							 
							<div class="form-group">
								<div class="col-lg-10 col-lg-offset-2">
									<button type="submit" class="btn btn-primary">Submit</button>
								</div>
							</div>
						</fieldset>
					</form>
				</div>		
			</div>
			
			<?php
				if (isset($url))
				{
					?>
					<div class="row">						
						<div class="col-lg-12">
							<h2>Available Datsets</h2>
							<?php
								$dom = new DOMDocument('1.0','UTF-8');
			
								if (!$dom->load($url))
								{
									?>
									<div class="alert alert-warning">									
										<strong>Error Processing Data!</strong> There was an error retrieving or processing the data.
									</div>
									<?php
								}
								else 
								{
									$data = dom_to_array( $dom );
									//header( "Content-type: text/plain" );
									$endpoint = preg_replace( "/\?.*$/", "", $url );
									$get_endpoint = $endpoint;
									$post_endpoint = $endpoint;
									
									if( @$data["WMS_Capabilities"] )
									{
										?>
										<table class=table table-striped>
											<thead>
												<tr>
													<th colspan="2">Name</th>
													<th>Title</th>
													<th>Link</th>
												</tr>
											</thead>
											<tbody>
											<?php
												show_wms_layer( $data['WMS_Capabilities']['Capability']['Layer'], $get_endpoint ); 
											?>
											</tbody>										
										</table>
										<?php									    
									}		
									elseif( @$data["WFS_Capabilities"] )
									{
									    $om = $data["WFS_Capabilities"]["OperationsMetadata"]["Operation"];
									   
									    $oformats = array();
									    foreach( $om as $o ) {
									        if( $o["name"] == "GetFeature" ) {
									            foreach( $o["Parameter"] as $p ) {
									                if( $p["name"] == "outputFormat" ) {
									                    if( @$p["Value"] ) {
									                      $oformats = ensureList($p["Value"]);
									                    } elseif( @$p["AllowedValues"]["Value"] ) {
									                      $oformats = ensureList($p["AllowedValues"]["Value"]);
									                    }
									                }
									            } 
									            $get_endpoint = @$o["DCP"]["HTTP"]["Get"]["href"];
									            $post_endpoint = @$o["DCP"]["HTTP"]["Post"]["href"];
									        }
									    }
									
									#<ows:DCP><ows:HTTP><ows:Get xlink:href="http://inspire.misoportal.com:80/geoserver/gateshead_council_conservationareas/wfs"/><ows:Post xlink:href="http://inspire.misoportal.com:80/geoserver/gateshead_council_conservationareas/wfs"/></ows:HTTP></ows:DCP>
									    ?>        
									    <table class="table table-striped">
											<thead>
												<tr>
													<th colspan="2">Name</th>
													<th>Title</th>
													<th>Abstract</th>
													<th>Link</th>
												</tr>
											</thead>
											<tbody>
											<?php
												show_ftlist( $data['WFS_Capabilities']['FeatureTypeList'], $get_endpoint,$post_endpoint,$oformats ); 
											?>
											</tbody>										
										</table>
									    <?php
									}
									else 
									{
										?>
										<div class="alert alert-warning">									
											<strong>Not sure about this file!</strong>
											<pre>
											<?php print_r( $data ); ?>
											</pre> 
										</div>
										<?php									    
									}
								}						
								?>					
						</div>					
					</div>
					<?php
				}
			?>
		</div>
	</body>
</html>

<?php

	function dom_to_array($root)
	{
	    $result = array();
	
	    if ($root->hasAttributes())
	    {
	        $attrs = $root->attributes;
	
	        foreach ($attrs as $i => $attr)
	            $result[$attr->name] = $attr->value;
	    }
	
	    $children = $root->childNodes;
	
	    if ( @$children && $children->length == 1)
	    {
	        $child = $children->item(0);
	
	        if ($child->nodeType == XML_TEXT_NODE)
	        {
	            $result['_value'] = $child->nodeValue;
	
	            if (count($result) == 1)
	                return $result['_value'];
	            else
	                return $result;
	        }
	    }
	
	    $group = array();
	
	
	    if( @$children ) 
	    {
		    for($i = 0; $i < $children->length; $i++)
		    {
		        $child = $children->item($i);
		        $childNodeName = preg_replace( '/.*:/','',$child->nodeName );
		
		        if (!isset($result[$childNodeName]))
		        {
		            $result[$childNodeName] = dom_to_array($child);
		        }
		        else
		        {
		            if (!isset($group[$childNodeName]))
		            {
		                $tmp = $result[$childNodeName];
		                $result[$childNodeName] = array($tmp);
		                $group[$childNodeName] = 1;
		            }
		
		            $result[$childNodeName][] = dom_to_array($child);
		        }
		    }   
	    }
	
	    return $result;
	 }
 ?>
 
<?php
	function isAssoc($arr)
	{
	    return array_keys($arr) !== range(0, count($arr) - 1);
	}
	
	function ensureList($v) 
	{
		if( !is_array($v) || isAssoc($v) ) { return array( $v ); }
    	return $v;
	}
?>
 
<?php 
	function show_wms_layer($layer, $endpoint, $depth = 0)
	{
		if (@$_GET['debug'])
		{
			print "<tr><td colspan='4'><div style='width: 500px; overflow-x:auto;' ><pre>" . print_r($layer, true) . "</pre></div></td></tr>";
		}
	
		print "<tr>";
		print "<td>";
		for ($i = 0; $i < $depth; ++$i)
		{
			print "&bull;";
		}
	
		print "</td>";
		print "<td>" . @$layer["Name"] . "</td>";
		print "<td>" . @$layer["Title"] . "</td>";
		print "<td>" . @$layer["Abstract"] . "</td>";
		print "<td>";
		if (@$layer["Name"])
		{
			print "<a class=\"btn btn-sm btn-primary\" href='wmsview.php?endpoint=$endpoint&layer=" . $layer["Name"] . "'>View</a>";
		}
	
		print "</td>";
		print "</tr>";
		if (@$layer["Layer"])
		{
			print "<tr><td colspan='4'>";
			print "<table class=\"table table-striped\" border='1' style='width:100%'>";
			print "<tr><th></th><th>Name</th><th>Title</th><th>Abstract</th><th>Link</th></tr>";
			$list = ensureList($layer["Layer"]);
			foreach($list as $sublayer)
			{
				show_wms_layer($sublayer, $endpoint, $depth + 1);
			}
	
			print "</table>";
			print "</td>";
			print "</tr>";
		}
	}
?>

<?php

	function show_ftlist( $ftlist, $get_endpoint, $post_endpoint, $oformats, $depth = 0 ) {
    if( @$_GET['debug'] ) 
    { 
    	print "<tr><td colspan='4'><div style='width: 500px; overflow-x:auto;' ><pre>".print_r( $ftlist, true )."</pre></div></td></tr>"; 
 	 }
    
    $list = ensureList( $ftlist["FeatureType"] );

    foreach( $list as $ft ) 
    {
		if( @is_array( $ft["Abstract"] ) ) 
		{ 
			$ft["Abstract"] = print_r( $ft["Abstract"],1 ); 
		}
     print "<tr>";
     print "<td>";
     for( $i=0;$i<$depth;++$i ) { print "&bull;"; }
     print "</td>";
     print "<td>".@$ft["Name"]."</td>";
     print "<td>".@$ft["Title"]."</td>";
     print "<td>".@$ft["Abstract"]."</td>";
     
     print "<td>";
     if( @$ft["Name"] ) 
     {
         $l = array();
         $ns = "";
         $term = $ft["Name"];
         if( preg_match( "/:/", $ft["Name"] ) ) {
             list( $ns, $term ) = preg_split( "/:/", $ft["Name"] );
         }
         print "<a class=\"btn btn-sm btn-primary\" href='wfsview.php?endpoint=$post_endpoint&namespace=$ns&term=$term'>View Map</a>";
         
			?>						
			<div class="btn-group">
			  <a href="#" class="btn btn-sm btn-default">View Data</a>
			  <a aria-expanded="false" href="#" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
			  <ul class="dropdown-menu">			    
			    <?php
				  	foreach( $oformats as $oformat ) 
				  	{
				  		?>
				  		<li><a href="<?php print "$get_endpoint?service=wfs&request=GetFeature&typeName=".$ft["Name"]."&outputFormat=$oformat" ?>"><?php print $oformat ?></a></li>
				  		<?php		             
		         }
			  	 ?>
			  </ul>
			</div>			  	 	
		    
			<?php      
         
     }
     print "</td>";
     print "</tr>";
    }
}



?>