<?php
$url = "";
$format = "JSON";
$endpoint = null;
$crud = null;
if (isset($argv[1]))
{
	$urls[] = $argv[1];
      
}
elseif (isset($_GET['url']))
{
	$urls[] = $_GET['url'];
}
else {
	$urls = file(__DIR__."/data/getCapabilitiesURLs.txt");
}

if (isset($argv[2])) $format = $argv[2];
elseif (!empty($_GET['format'])) $format = $_GET['format'];

header( "Content-type: text/".strtolower($format) );
if (in_array($format, array("CSV", "TSV"))) { 
	$stdout = fopen('php://output','w');
}

$options = [
  'http' => [
    'method' => 'GET',
    'timeout' => '5'
  ]
];
$context = stream_context_create($options);
libxml_set_streams_context($context);
$processedlists = array();

foreach ($urls as $url) {
	$dom = new DOMDocument('1.0','UTF-8');
//	error_log($url);
	if (!$dom->load($url)) {
   		error_log("Error in XML document: $url");
		continue;
	}
	$data = dom_to_array( $dom );
	list( $endpoint, $crud ) = preg_split( "/\?/", $url, 2 );
	$processedlist = array();
	if (empty($_SERVER["HTTP_HOST"])) {
		$urlpath = "http://localhost/geo-explore/";
	}
	else {
		$urlpathbits = explode("/", "http://{$_SERVER['HTTP_HOST']}{$_SERVER['SCRIPT_NAME']}");
		$urlpath = implode("/", array_slice($urlpathbits, 0, -1));
	}
	if( !empty($data["WMS_Capabilities"]) )	{
	    build_wms_layer( $data['WMS_Capabilities']['Capability']['Layer'], $endpoint, $format );
	}
	elseif (!empty($data["WFS_Capabilities"])) {
		$om = $data["WFS_Capabilities"]["OperationsMetadata"]["Operation"];
		$oformats = array();
    		foreach( $om as $o ) {
        		if( $o["name"] == "GetFeature" ) {
            			foreach( $o["Parameter"] as $p ) {
                			if( $p["name"] == "outputFormat" ) {
                    				$oformats = ensureList($p["Value"]);
                			}
            			} 
        		}
    		}        
	    	build_ftlist( $data['WFS_Capabilities']['FeatureTypeList'], $endpoint, $oformats, $format );
	}
	else {
		error_log("Unrecognised file format for $url");
    		continue;
	}
	$processedlists[] = $processedlist;
}

header( "Content-type: text/".strtolower($format) );
switch ($format) {
	case "JSON":
		if (sizeof($processedlists) == 1) echo json_encode($processedlists[0]);
        	else echo json_encode($processedlists);
                break;
/*      case "CSV":
        case "TSV":
        	$stdout = fopen('php://output','w');
                foreach( $processedlist as $line ){
                if ($format == "CSV") {
                	fputcsv($stdout, $line);
                }
                else {
                        fputcsv($stdout, $line, "\t");
                     }
                     if (preg_match('/Win/', $_SERVER['HTTP_USER_AGENT'])) {
                        print "\r\n";
                     }
                }
                fflush($stdout);
                break;
*/
        default:
        if (sizeof($processedlists) == 1) echo json_encode($processedlists[0]);
        else echo json_encode($processedlists);
	
}

exit;

function ensureList($v) {
    if( !is_array($v) || isAssoc($v) ) { return array( $v ); }
    return $v;
}


function build_ftlist( $ftlist, $endpoint, $oformats, $format ) {
    global $processedlist, $urlpath;

    $rawlist = ensureList( $ftlist["FeatureType"] );
    if (in_array($format, array("CSV", "TSV"))) {
        $processedlist[] = array("Name", "Title", "Abstract", "Formats");
    }
    foreach( $rawlist as $ft ) {
        if( !empty( $ft["Abstract"] ) && is_array( $ft["Abstract"] ) ) { 
		$ft["Abstract"] = print_r( $ft["Abstract"],1 ); 
	}
	$plistitem = array();
	foreach ( array("Name", "Title", "Abstract") as $field ) {
		if (!empty($ft[$field])) $plistitem[$field] = $ft[$field];
		else  $plistitem[$field] = "";
	}	
        if( !empty($ft["Name"])) {
            $l = array();
            list( $ns, $term ) = preg_split( "/:/", $ft["Name"] );

            $l[] = "$urlpath/wfsview.php?endpoint=$endpoint&namespace=$ns&term=$term";
            foreach( $oformats as $oformat ) {
                $l[]= "$endpoint?service=wfs&request=GetFeature&typeName=".$ft["Name"]."&outputFormat=$oformat";
            }
        }
        if (in_array($format, array("CSV", "TSV"))) {
     	    $plistitem["Formats"] = join(", ", $l);
	}
	else {
		$plistitem["Formats"] = $l;
	}
#	error_log(var_export($plistitem,1));
	$processedlist[] = $plistitem;
    }
}

function build_wms_layer( $layer, $endpoint, $format, $depth = 0 ) {
    global $processedlist, $urlpath;

    if (in_array($format, array("CSV", "TSV")) && $depth == 0) {
        $processedlist[] = array("Name", "Title", "Abstract", "Link");
    }
    $plistitem = array();
    foreach ( array("Name", "Title", "Abstract") as $field ) {
    	if (!empty($layer[$field])) $plistitem[$field] = $layer[$field];
	else $plistitem[$field] = "";
    }
    if( !empty($layer["Name"]) ) {
    	$plistitem['Link'] = "$urlpath/wmsview.php?endpoint=$endpoint&layer=".$layer["Name"];
    }
    else $plistitem['Link'] = "";
    $processedlist[] = $plistitem; 
    if( !empty($layer["Layer"]) ) {
        $list = ensureList( $layer["Layer"] );
        foreach( $list as $sublayer ) { 
	    build_wms_layer( $sublayer, $endpoint, $format, $depth+1 );
	}
    }
}

function isAssoc($arr)
{
    return array_keys($arr) !== range(0, count($arr) - 1);
}


function dom_to_array($root, $depth = 0)
{
    $result = array();

    if ($root->hasAttributes())
    {
        $attrs = $root->attributes;

        foreach ($attrs as $i => $attr)
            $result[$attr->name] = $attr->value;
    }

    $children = $root->childNodes;

    if ( !empty($children) && $children->length == 1)
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


    if( !empty($children) ) {
        for($i = 0; $i < $children->length; $i++)
        {
            $child = $children->item($i);
            $childNodeName = preg_replace( '/.*:/','',$child->nodeName );

            if (!isset($result[$childNodeName]))
            {
                $result[$childNodeName] = dom_to_array($child, $depth+1);
            }
            else
            {
                if (!isset($group[$childNodeName]))
                {
                    $tmp = $result[$childNodeName];
                    $result[$childNodeName] = array($tmp);
                    $group[$childNodeName] = 1;
                }

                $result[$childNodeName][] = dom_to_array($child, $depth+1);
            }
        }   
    }

    return $result;
}

