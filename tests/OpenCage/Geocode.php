<?php

namespace OpenCage;

include(dirname(__DIR__).'/../src/OpenCage/Geocoder.php');

class GeocodeTest extends \PHPUnit_Framework_TestCase
{

    public function testMissingKey()
    {
        $geocoder = new Geocoder();
        try {
            $result = $geocoder->geocode('Johannesburg');
        } catch (\Exception $e) {
            $this->assertEquals('Missing API key', $e->getMessage());
            return;
        }
        $this->fail();
    }


    public function testInvalidKey()
    {
        $geocoder = new Geocoder('invalid-APIKEY');
        $result = $geocoder->geocode('Johannesburg');
        // print_r($result);
        $this->assertEquals(403, $result['status']['code']);
        $this->assertEquals('invalid API key', $result['status']['message']);
    }

    public function testOverQuota()
    {
        $geocoder = new Geocoder('514980ca9e21b830b9797818288a1f4f'); // this key has a daily quota of 1
        $result = $geocoder->geocode('Johannesburg');
        $result = $geocoder->geocode('Johannesburg'); // twice to be safe
        $this->assertEquals(402, $result['status']['code']);
        $this->assertEquals('quota exceeded', $result['status']['message']);
    }

    public function testLondon()
    {
        $key = getenv('OPENCAGE_API_KEY');
        $geocoder = new Geocoder($key);
        $query = "82 Clerkenwell Road, London";
        $result = $geocoder->geocode($query);

        // print_r($result);

        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }
}
