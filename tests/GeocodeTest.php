<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;

class GeocodeTest extends TestCase
{
    public function testLondon(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $result = $geocoder->geocode("82 Clerkenwell Road, London");

        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }

    public function testWithOptionalParams(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $result = $geocoder->geocode('82 Clerkenwell Road, London', [
            'language' => 'fr',
            'countrycode' => 'gb'
        ]);
        $this->assertEquals(200, $result['status']['code']);
    }

    // https://opencagedata.com/api#testingkeys - query 'NOWHERE-INTERESTING' returns 200 with zero results
    public function testNoResults(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $result = $geocoder->geocode('NOWHERE-INTERESTING');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals(0, $result['total_results']);
        $this->assertEmpty($result['results']);
    }

    public function testReverseGeocode(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $result = $geocoder->geocodeReverse(51.5227, -0.1025);
        $this->assertEquals(200, $result['status']['code']);
        $this->assertGreaterThan(0, $result['total_results']);
    }

    public function testReverseGeocodeWithStrings(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $result = $geocoder->geocodeReverse('51.5227', '-0.1025');
        $this->assertEquals(200, $result['status']['code']);
        $this->assertGreaterThan(0, $result['total_results']);
    }
}
