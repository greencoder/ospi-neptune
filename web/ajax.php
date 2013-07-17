<?php

$station = $_GET['station'];
$minutes = $_GET['minutes'];

if (!(strlen($station) > 0 || strlen($minutes) > 0)) {
	echo("Error! Stations and Minutes required.");
}
else {
	$fp = fsockopen("127.0.0.1", 9999, $errno, $errdesc);
	fputs($fp, "$station,$minutes");
	fclose($fp);
	echo("OK");
}

?>