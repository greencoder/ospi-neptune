<?php
$fp = fsockopen("127.0.0.1", 9999, $errno, $errdesc);
fputs($fp, "1,2");
fclose($fp);
?>