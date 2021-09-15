<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;

class GeocoderTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals(401, $result['status']['code']);
        $this->assertEquals('invalid API key', $result['status']['message']);
    }

    public function testNetworkRequestError()
    {
        // https://opencagedata.com/api#testingkeys
        $geocoder = new Geocoder('6d0e711d72d74daeb2b0bfd2a5cdfdba');
        $result = $geocoder->geocode('London', ['host' => 'doesnotexist.opencagedata.com']);
        // print_r($result);
            
        $this->assertEquals(498, $result['status']['code']);
        $this->assertStringContainsString('network issue', $result['status']['message']);
        $this->assertStringContainsString('doesnotexist.opencagedata.com', $result['status']['message']);
    }

    public function testOverQuota()
    {
        // https://opencagedata.com/api#testingkeys
        $geocoder = new Geocoder('4372eff77b8343cebfc843eb4da4ddc4');
        $result = $geocoder->geocode('Johannesburg');
        $this->assertEquals(402, $result['status']['code']);
        $this->assertEquals('quota exceeded', $result['status']['message']);
    }

    public function testLondon()
    {
        // https://opencagedata.com/api#testingkeys
        $geocoder = new Geocoder('6d0e711d72d74daeb2b0bfd2a5cdfdba');
        $query = "82 Clerkenwell Road, London";
        $result = $geocoder->geocode($query);

        // print_r($result);

        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }
}
