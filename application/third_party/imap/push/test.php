<?php

include_once 'function.php';
//
//$ret = getLntGeo($_REQUEST['zipcode']);
//
//outputresult($ret);
date_default_timezone_set("UTC");
//$dt = new DateTime();
//echo $dt->format('Y-m-d H:i:s');

$strdate = date("Y-m-d H:i:s", time());
$date = strtotime($strdate);
$after24 = date('Y-m-d H:i:s', strtotime("+1 day", $date));

echo $strdate;      echo "<br>";
echo $after24;      echo "<br>";
?>
