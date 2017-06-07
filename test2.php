<?php
ini_set('memory_limit', '512M'); //Change memory use
$file_path = 'Cooper\Specimen 1 (56)\06_04_2016_12_55_43_SMA_D_56_'; //Make it dynamic
$csv = array_map('str_getcsv', file($file_path));
$csv = explode("\r", $csv[0][0]);
for ($i=0; $i < sizeof($csv); $i++) {
  $temp = explode("\t", $csv[$i]);
  var_dump($temp);
}
?>
