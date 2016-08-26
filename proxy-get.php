<?php
ini_set('memory_limit','512M');

$endpoint = $_GET["endpoint"];
unset( $_GET['endpoint'] );
$entityBody = file_get_contents('php://input');

$url = add_params( $endpoint,http_build_query($_GET));
$curl=curl_init();
curl_setopt($curl,CURLOPT_URL, $url );
curl_setopt($curl,CURLOPT_POST, false );
curl_setopt($curl,CURLOPT_HTTPHEADER, array( "Content-type: text/xml" ) );
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true); 
//curl_setopt($curl,CURLOPT_VERBOSE, true );
$result = curl_exec($curl);
curl_close($curl);

header( "Content-type: text/xml" );
print preg_replace( '/null:/', '', $result );

exit;


function add_params( $url, $params ) {
    if( preg_match( "/\?/", $url ) ) { return "$url&$params"; }
    return "$url?$params";
}
