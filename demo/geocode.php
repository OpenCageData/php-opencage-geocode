<?php

// run `composer install` in the repository root first
require dirname(__DIR__) . '/vendor/autoload.php';

use OpenCage\Geocoder\Geocoder;

$query = "82 Clerkenwell Road, London";
$key = getenv('OPENCAGE_API_KEY') ?: null;
$geocoder = new Geocoder($key);
$result = $geocoder->geocode($query);
print_r($result);
