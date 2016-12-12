<?php

include(dirname(__DIR__).'/src/AbstractGeocoder.php');
include(dirname(__DIR__).'/src/Geocoder.php');

// use OpenCage\Geocoder;

$query = "82 Clerkenwell Road, London";
$key = getenv('OPENCAGE_API_KEY');
$geocoder = new OpenCage\Geocoder\Geocoder($key);
$result = $geocoder->geocode($query);
print_r($result);
