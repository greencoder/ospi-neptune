<?php

include('config.inc.php');

$station = $_GET['station'];
$minutes = $_GET['minutes'];

if (!(strlen($station) > 0 || strlen($minutes) > 0)) {
	echo("Error! Stations and Minutes required.");
}
else {
	$fp = fsockopen($serverHost, $serverPort, $errno, $errdesc);
	$cmd = "{\"cmd\":\"operate-station\", \"args\":{\"station\":\"$station\", \"minutes\":\"$minutes\"}}";
	fputs($fp, $cmd);
	fclose($fp);
	echo("OK");
}

?>
