<?php
$url = @trim($_GET['url']);

if( !$url ) { print "<form><input name='url' style='width:80%'> <label>(<input name='debug' type='checkbox' value='1' > debug?) <input type='submit' /></form"; exit; }

$dom = new DOMDocument('1.0','UTF-8');
if (!$dom->load($url))
{
    echo ("Error in XML document");
    exit;
}
$data = dom_to_array( $dom );
//header( "Content-type: text/plain" );
$endpoint = preg_replace( "/\?.*$/", "", $url );
$get_endpoint = $endpoint;
$post_endpoint = $endpoint;
$version;
print "<h1>$endpoint</h1>";
if( @$data["WMS_Capabilities"] )
{
    $version = $data['WMS_Capabilities']['version'];
    print "<table border='1' style='width:100%'>";
    print "<tr><th></th><th>Name</th><th>Title</th><th>Abstract</th><th>Link</th></tr>";
    show_wms_layer( $data['WMS_Capabilities']['Capability']['Layer'], $get_endpoint );
    print "</table>";
}
elseif( @$data["WFS_Capabilities"] )
{
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
            
    print "<table border='1' style='width:100%'>";
    print "<tr><th></th><th>Name</th><th>Title</th><th>Abstract</th><th>Link</th></tr>";
    show_ftlist( $data['WFS_Capabilities']['FeatureTypeList'], $get_endpoint,$post_endpoint,$version,$oformats );
    print "</table>";
}
else 
{
    print "no sure about this file!";
    print "<pre>";
    print_r( $data );
}
exit;

function ensureList($v) {
    if( !is_array($v) || isAssoc($v) ) { return array( $v ); }
    return $v;
}


function show_ftlist( $ftlist, $get_endpoint, $post_endpoint, $version, $oformats, $depth = 0 ) {
    if( @$_GET['debug'] ) { print "<tr><td colspan='4'><div style='width: 500px; overflow-x:auto;' ><pre>".print_r( $ftlist, true )."</pre></div></td></tr>"; }
    $list = ensureList( $ftlist["FeatureType"] );

    foreach( $list as $ft ) {
if( @is_array( $ft["Abstract"] ) ) { $ft["Abstract"] = print_r( $ft["Abstract"],1 ); }
        print "<tr>";
        print "<td>";
        for( $i=0;$i<$depth;++$i ) { print "&bull;"; }
        print "</td>";
        print "<td>".@$ft["Name"]."</td>";
        print "<td>".@$ft["Title"]."</td>";
        print "<td>".@$ft["Abstract"]."</td>";
        
        print "<td>";
        if( @$ft["Name"] ) {
            $l = array();
            $ns = "";
            $term = $ft["Name"];
            if( preg_match( "/:/", $ft["Name"] ) ) {
                list( $ns, $term ) = preg_split( "/:/", $ft["Name"] );
            }
            $l[]= "<a href='wfsview.php?endpoint=$post_endpoint&namespace=$ns&term=$term'>View</a>";
            $url = add_params($get_endpoint,"version=$version&service=wfs&request=GetFeature&typeName=".$ft["Name"]);
            $l[]= "<a href='$url'>Default</a>";
            foreach( $oformats as $oformat ) {
                $url = add_params($get_endpoint,"version=$version&service=wfs&request=GetFeature&typeName=".$ft["Name"]."&outputFormat=$oformat");
                $l[]= "<a href='$url'>$oformat</a>";
            }
            print join( ", ", $l );
        }
        print "</td>";
        print "</tr>";
    }
}

function add_params( $url, $params ) {
    if( preg_match( "/\?/", $url ) ) { return "$url&$params"; }
    return "$url?$params";
}



function show_wms_layer( $layer, $endpoint, $depth = 0 ) {
    if( @$_GET['debug'] ) { print "<tr><td colspan='4'><div style='width: 500px; overflow-x:auto;' ><pre>".print_r( $layer, true )."</pre></div></td></tr>"; }
    print "<tr>";
    print "<td>";
    for( $i=0;$i<$depth;++$i ) { print "&bull;"; }
    print "</td>";
    print "<td>".@$layer["Name"]."</td>";
    print "<td>".@$layer["Title"]."</td>";
    print "<td>".@$layer["Abstract"]."</td>";
    
    print "<td>";
    if( @$layer["Name"] ) {
      print "<a href='wmsview.php?endpoint=$endpoint&layer=".$layer["Name"]."'>View</a>";
    }
    print "</td>";
    print "</tr>";
    if( @$layer["Layer"] ) {
        print "<tr><td colspan='4'>";
        print "<table border='1' style='width:100%'>";
        print "<tr><th></th><th>Name</th><th>Title</th><th>Abstract</th><th>Link</th></tr>";
        $list = ensureList( $layer["Layer"] );
        foreach( $list as $sublayer ) { show_wms_layer( $sublayer, $endpoint, $depth+1 ); }
        print "</table>";
        print "</td>";
        print "</tr>";
    }
}

function isAssoc($arr)
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}


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


    if( @$children ) {
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

