<?php

$endpoint = $_GET["endpoint"];
$entityBody = file_get_contents('php://input');

$curl=curl_init();
curl_setopt($curl,CURLOPT_URL, $endpoint );
curl_setopt($curl,CURLOPT_POST, sizeof($entityBody));
curl_setopt($curl,CURLOPT_POSTFIELDS, $entityBody);
curl_setopt($curl,CURLOPT_HTTPHEADER, array( "Content-type: text/xml" ) );
curl_setopt($curl,CURLOPT_RETURNTRANSFER, true); 
//curl_setopt($curl,CURLOPT_VERBOSE, true );
$result = curl_exec($curl);
curl_close($curl);

header( "Content-type: text/xml" );
print preg_replace( '/null:/', '', $result );
