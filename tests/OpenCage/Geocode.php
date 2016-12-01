<?php

namespace OpenCage;

include(dirname(__DIR__).'/../src/OpenCage/Geocoder.php');

class GeocodeTest extends \PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        // $this->assertEquals(1,1);
        $key = 'YOUR-API-KEY';
        $geocoder = new Geocoder($key);
        $query = "82 Clerkenwell Road, London";
        $result = $geocoder->geocode($query);

        // print_r($result);

        $this->assertEquals(403, $result['status']['code']);
    }
}
