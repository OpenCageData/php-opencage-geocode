<?php

include(dirname(__DIR__).'/src/OpenCage/Geocoder.php');

// use OpenCage\Geocoder;

$query = "82 Clerkenwell Road, London";
$key = 'YOUR-API-KEY';
$geocoder = new OpenCage\Geocoder($key);
$result = $geocoder->geocode($query);
print_r($result);
