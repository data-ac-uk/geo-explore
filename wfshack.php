<?php
$url = @$_GET['url'];

if( !$url ) { print "<form><input name='url' style='width:80%'> <label>(<input name='debug' type='checkbox' value='1' > debug?) <input type='submit' /></form"; exit; }

$dom = new DOMDocument('1.0','UTF-8');
if (!$dom->load($url))
{
   echo ("Error in XML document");
exit;
}
$data = dom_to_array( $dom );
//header( "Content-type: text/plain" );
list( $endpoint, $crud ) = preg_split( "/\?/", $url, 2 );

print "<table border='1' style='width:100%'>";
print "<tr><th></th><th>Name</th><th>Title</th><th></th></tr>";
show_layer( $data['WMS_Capabilities']['Capability']['Layer'], $endpoint );
print "</table>";
exit;

function show_layer( $layer, $endpoint, $depth = 0 ) {
    if( @$_GET['debug'] ) { print "<tr><td colspan='4'><div style='width: 500px; overflow-x:auto;' ><pre>".print_r( $layer, true )."</pre></div></td></tr>"; }
    print "<tr>";
    print "<td>";
    for( $i=0;$i<$depth;++$i ) { print "&bull;"; }
    print "</td>";
    print "<td>".@$layer["Name"]."</td>";
    print "<td>".@$layer["Title"]."</td>";
    
    print "<td>";
    if( @$layer["Name"] ) {
      print "<a href='wmsview.php?endpoint=$endpoint&layer=".$layer["Name"]."'>View</a>";
    }
    print "</td>";
    print "</tr>";
    if( @$layer["Layer"] ) {
        print "<tr><td colspan='4'>";
        print "<table border='1' style='width:100%'>";
        print "<tr><th></th><th>Name</th><th>Title</th><th></th></tr>";
        $list = $layer["Layer"];
        if( isAssoc($list) ) { $list = array( $list ); }
        foreach( $list as $sublayer ) { show_layer( $sublayer, $endpoint, $depth+1 ); }
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

        if (!isset($result[$child->nodeName]))
            $result[$child->nodeName] = dom_to_array($child);
        else
        {
            if (!isset($group[$child->nodeName]))
            {
                $tmp = $result[$child->nodeName];
                $result[$child->nodeName] = array($tmp);
                $group[$child->nodeName] = 1;
            }

            $result[$child->nodeName][] = dom_to_array($child);
        }
    }   
    }

    return $result;
}

