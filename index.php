<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$url = null;
$debug = false;

if (isset($_GET['view'])) 
{
	$view = trim($_GET['view']);
	if( $view == 'wfs' ) {
		include "wfsview.php";
		exit;
	}
	if( $view == 'wms' ) {
		include "wmsview.php";
		exit;
	}
}

if (isset($_GET['url'])) 
{
	$url = trim($_GET['url']);
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
					<h1><a href='?'>Geo-Explorer</a></h2>							
				</div>
			</div>
						
			<?php
				RenderFindDataForm($url);
			?>
			
			<?php
				if (isset($url))
				{
					RenderResults($url, $debug);
				}
				else 
				{
					include( "examples.php" );
				}
			?>

			<?php
				RenderAbout();
			?>
			
		</div>
	</body>
</html>

<?php

	function RenderDebugOutput($debugInfo)
	{
		?>
		<h2>Debug Information</h2>
		<pre><?php print_r($debugInfo, false) ?></pre>
		<?php
	}
 
?>

<?php

	function RenderAlertForErrorLoadingData()
	{
		?>
		<div class="alert alert-warning">									
			<strong>Error Getting Data.</strong> 
			An error occurred either when retrieving the data, or parsing it.
		</div>
		<?php
	}
	
	function RenderWmsData($data, $get_endpoint, $version)
	{
		?>
		<div class='panel panel-info'>
		<div class='panel-heading'>Available Datasets</div>
		<div class='panel-body'>
  		<p>Please note, we don't currently have the code to zoom to the correct location for these maps so you will have to do it by hand. Sorry.</p>
		<table class="table table-striped">
			<thead>
				<tr>
					<th colspan="2">Name</th>
					<th>Title</th>
					<th>Abstract</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
			<?php			
				show_wms_layer( $data['WMS_Capabilities']['Capability']['Layer'], $get_endpoint, $version ); 
			?>
			</tbody>										
		</table>
		</div>
		</div>
		<?php
	}	
	
	function RenderWfsData($data, $get_endpoint, $post_endpoint, $version, $oformats)
	{
		?>
		<div class='panel panel-primary'>
		<div class='panel-heading'>Available Datasets</div>
		<div class='panel-body'>
		<table class="table table-striped">
			<thead>
				<tr>
					<th colspan="2">Name</th>
					<th>Title</th>
					<th>Abstract</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php				
				show_ftlist( $data['WFS_Capabilities']['FeatureTypeList'], $get_endpoint, $post_endpoint, $version, $oformats ); 
				?>
			</tbody>										
		</table>
		</div>
		</div>
		<?php
	}
	
	function RenderAlertForIffyData($data)
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
	
	function RenderResults($url, $debug)
	{
		?>
		<div class="row">						
			<div class="col-lg-12">				
				<?php
					$dom = new DOMDocument('1.0','UTF-8');

					if (!$dom->load($url))
					{
						RenderAlertForErrorLoadingData();
					}
					else 
					{						
						$data = dom_to_array( $dom );
						//header( "Content-type: text/plain" );
						$endpoint = preg_replace( "/\?.*$/", "", $url );
						$get_endpoint = $endpoint;
						$post_endpoint = $endpoint;
						
						$version;
						
						if( @$data["WMS_Capabilities"] )
						{
							RenderInfoBlock( "wms-info", "Web Mapping Service (WMS) Information", "<p>What is WMS?</p> from Wikipedia: <blockquote>A Web Map Service (WMS) is a standard protocol for serving (over the Internet) georeferenced map images which a map server generates using data from a GIS database. The Open Geospatial Consortium developed the specification and first published it in 1999.</blockquote> Or check the <a href='http://www.opengeospatial.org/standards/wms'>specs</a> ");			
							RenderXMLBlock( "service-info", "Service Information", @$data["WMS_Capabilities"]["Service"] );
							$version = $data['WMS_Capabilities']['version'];
							if ($debug)
							{
								RenderDebugOutput($data['WMS_Capabilities']['Capability']['Layer']);
							}
							RenderWmsData($data, $get_endpoint, $version);									    							
						}		
						elseif( @$data["WFS_Capabilities"] )
						{
							RenderInfoBlock( "wfs-info", "Web Feature Service (WFS) Information", "<p>What is WFS?</p> from Wikipedia: <blockquote>the Open Geospatial Consortium Web Feature Service Interface Standard (WFS) provides an interface allowing requests for geographical features across the web using platform-independent calls.</blockquote> Or check the <a href='http://www.opengeospatial.org/standards/wfs'>specs</a>" );	
							RenderXMLBlock( "service-id", "Service Identification", @$data["WFS_Capabilities"]["ServiceIdentification"] );
							RenderXMLBlock( "service-provider", "Service Provider", @$data["WFS_Capabilities"]["ServiceProvider"] );
							 $version = $data['WFS_Capabilities']['version'];
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
							 
							if ($debug)
							{
								RenderDebugOutput($data);
							}
							 RenderWfsData($data, $get_endpoint, $post_endpoint, $version, $oformats);
						}
						else 
						{
							RenderAlertForIffyData($data);				    
						}
					}						
					?>					
			</div>					
		</div>

		<?php
	}

?>

<?php
	function RenderAbout()
	{
		?>
		<div class="row">
			<div class="col-lg-12">
				<div class='panel panel-info'>
						<div class='panel-heading'>About this service</div>
						<div class='panel-body'>
<p>This service was created as a mini-project at the 2016 Research Festival of the <a href='http://www.wais.ecs.soton.ac.uk'>Web and Internet Science Research Group</a> at the <a href="http://www.southampton.ac.uk/">University of Southampton</a>. It is provided as-is and has no formal support model. The code is available at <a href='https://github.com/data-ac-uk/geo-explore'>Github</a>.</p>
<p>You are encouraged to deep link to this service to help people explore specific services.</p>
<p>There are still many features we could add. This service rather assumes UK map projections (27700) and has a number of fiddles and compromises to make certain services work (some spit out invalid XML!).</p>
<h3>Team Members</h3>
<ul>
<li><a href='http://www.ecs.soton.ac.uk/person/cjg'>Christopher Gutteridge</a> - Team Leader</li>
<li><a href='http://www.ecs.soton.ac.uk/people/sza1g10'>Said Aljaloud</a></li>
<li><a href='http://www.ecs.soton.ac.uk/people/mwra1g13'>Mark Anderson</a></li>
<li>Caroline Halcrow</li>
<li><a href='http://www.ecs.soton.ac.uk/people/drn'>David Newman</a></li>
</ul>
</div>
</div>
</div>
</div>
<?  
	}

	function RenderFindDataForm($url)
	{
		?>
		<div class="row">
			<div class="col-lg-12">
		<div class='panel panel-info'>
		<div class='panel-heading'>Select Dataset</div>
		<div class='panel-body'>
       				<p>Please enter the URL of a WFS or WMS "GetCapabilities" request.</p>
				<form action="" method="get" class="form-horizontal">
					<fieldset>
						<div class="form-group">
							<label for="url" class="col-lg-2 control-label">URL</label>
							<div class="col-lg-10">
								<input class="form-control" id="url" name="url" type="text"
										<?php if (!isset($url)) { print "placeholder='e.g. http://www.southampton.gov.uk/geoserver/Inspire/wms?service=wfs&version=1.3.0&request=GetCapabilities'"; } ?>
										<?php if (isset($url)) { print "value='" . $url . "'"; } ?> 
								/>
							</div>
						</div>
<!--
						<div class="form-group">			      
							<div class="col-lg-10 col-lg-offset-2">			        
								<div class="checkbox">
									<label>
										<input type="checkbox" name="debug" value="1"> Debug?
									</label>
								</div>
							</div>
						</div>
-->
						 
						<div class="form-group">
							<div class="col-lg-10 col-lg-offset-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</fieldset>
				</form>
                            </div></div>
			</div>		
		</div>
		<?php	
	}

?>

<?php
        /* adapted from an example in the comments on php.net */
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
	
	function add_params( $url, $params ) 
	{
   	if( preg_match( "/\?/", $url ) ) { return "$url&$params"; }
   	return "$url?$params";
	}
?>
 
<?php 
	function show_wms_layer($layer, $endpoint, $version, $depth = 0)
	{			
		print "<tr>";
		print "<td>";		
		for ($i = 0; $i < $depth; ++$i)
		{
			#print "&bull;";
		}
	
		print "</td>";
     		if( !@$layer["Name"] ) {
          		print "<td></td>";
  		} elseif( is_qname( @$layer["Name"] ) ) {
          		print "<td>".@$layer["Name"]."</td>";
     		} else {
          		print "<td><div><span class='label label-warning'>Warning - this code is invalid and may not work</span></div>".@$layer["Name"]."</td>";
     		}
                if( @$layer["Title"] && is_array( $layer["Title"] ) && count($layer["Title"])==0 ) { $layer["Title"] = ""; }
                if( @$layer["Abstract"] && is_array( $layer["Abstract"] ) && count($layer["Abstract"])==0 ) { $layer["Abstract"] = ""; }
		print "<td>" . @$layer["Title"] . "</td>";
		print "<td>" . @$layer["Abstract"] . "</td>";
		print "<td>";
		if (@$layer["Name"])
		{
			print "<a class='btn btn-sm btn-primary' href='?view=wms&endpoint=$endpoint&layer=" . $layer["Name"] . "'>View</a>";
		}
	
		print "</td>";
		print "</tr>";
		if (@$layer["Layer"])
		{
			print "<tr><td colspan='5'>";
			print "<table class='table table-striped' border='1' style='width:100%'>";
			print "<tr><th></th><th>Name</th><th>Title</th><th>Abstract</th><th></th></tr>";
			$list = ensureList($layer["Layer"]);
			foreach($list as $sublayer)
			{
				show_wms_layer($sublayer, $endpoint, $version, $depth + 1);
			}
	
			print "</table>";
			print "</td>";
			print "</tr>";
		}
	}
?>

<?php

	function show_ftlist( $ftlist, $get_endpoint, $post_endpoint, $version, $oformats, $depth = 0 ) {
        
    $list = ensureList( $ftlist["FeatureType"] );

    foreach( $list as $ft ) 
    {
                if( @$ft["Title"] && is_array( $ft["Title"] ) && count($ft["Title"])==0 ) { $ft["Title"] = ""; }
                if( @$ft["Abstract"] && is_array( $ft["Abstract"] ) && count($ft["Abstract"])==0 ) { $ft["Abstract"] = ""; }
     print "<tr>";
     print "<td>";     
     for( $i=0;$i<$depth;++$i ) { print "&bull;"; }
     print "</td>";
     if( is_qname( $ft["Name"] ) ) {
          print "<td>".@$ft["Name"]."</td>";
     } else {
          print "<td><div><span class='label label-warning'>Warning - this code is invalid and may not work</span></div>".@$ft["Name"]."</td>";
     }
 
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
         print "<a class='btn btn-sm btn-primary' href='?view=wfs&endpoint=$post_endpoint&namespace=$ns&term=$term'>View Map</a>";                  
         
			?>						
			<div class="btn-group">
			  <a href="#" class="btn btn-sm btn-default">View Data</a>
			  <a aria-expanded="false" href="#" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></a>
			  <ul class="dropdown-menu">					  	    
			    <?php
				   $linkUrl = add_params($get_endpoint,"version=$version&service=wfs&request=GetFeature&typeName=".$ft["Name"]);
					?>
					<li><a href="<?php print $linkUrl ?>">Default Data Format</a></li>
					<li class="divider"></li>					
					<?php
				  	foreach( $oformats as $oformat ) 
				  	{
						$linkUrl = add_params($get_endpoint,"version=$version&service=wfs&request=GetFeature&typeName=".$ft["Name"]."&outputFormat=$oformat");				  		
				  		?>				  		                
				  		<li><a href="<?php print $linkUrl ?>"><?php print $oformat ?></a></li>
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

function is_qname( $string ) {
  # QName = ( NCName+":" )? + NCName
  # NCName = [_A-Za-z][-._A-Za-z0-9]* 
  # nb ignoring unicode!
  return preg_match( "/^([_A-Za-z][-._A-Za-z0-9]*:)?[_A-Za-z][-._A-Za-z0-9]*$/", $string );
}


function RenderInfoBlock( $id, $title, $html ) {
	print "<div class='panel panel-info'>";
	print "<div class='panel-heading'>";
	print "<a class='geo-panel-ct collapse-trigger' data-toggle='collapse' data-target='#".$id."-expandable'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> $title</a>";
	print "</div>";
	print "<div  id='".$id."-expandable' class='panel-body collapse'>";	
	print $html;
	print "</div>";
	print "</div>";
}
function RenderXMLBlock( $id, $title, $data ) {
	$list = tree2pairs( $data );
        if( !count($list) ) { return; }
	
	print "<div class='panel panel-primary'>";
	print "<div class='panel-heading'>";
	print "<a class='geo-panel-ct collapse-trigger' data-toggle='collapse' data-target='#".$id."-expandable'><span class='glyphicon glyphicon-plus' aria-hidden='true'></span> $title</a>";
	print "</div>";
	print "<div  id='".$id."-expandable' class='panel-body collapse'>";	
	print "<table class='table table-condensed'>";
        foreach( $list as $row ) {
		print "<tr>";
		print "<td>".$row[0]."</td>";
		print "<td> - </td>";
		print "<td>".$row[1]."</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
	print "</div>";
}
function tree2pairs( $tree, $prefix="" ) {
	$list = array();
	foreach( $tree as $k=>$v ) {
		$v = ensureList($v);
		foreach( $v as $v2 ) {
			if( !is_scalar($v2) &&  isAssoc($v2) ) {
				$list = array_merge( $list, tree2Pairs( $v2, "$k." ));
                	} else {
				$list []=  array( $prefix.$k, $v2 );
                	}
		}
	}
	return $list;
}

?>
