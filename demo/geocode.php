<?php

require_once('OpenCage.Geocoder.php');

$query = "82 Clerkenwell Road, London";
$key = 'dummy-api-key';
$geocoder = new OpenCage\Geocoder($key);
$result = $geocoder->geocode($query);
print_r($result);

?>
