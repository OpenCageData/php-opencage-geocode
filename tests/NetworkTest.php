<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;

class NetworkTest extends TestCase
{
    public function testInvalidHost(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        try {
            $geocoder->setHost('www.example.com');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid host: must be localhost or an opencagedata.com subdomain', $e->getMessage());
            return;
        }
        $this->fail();
    }

    public function testNetworkRequestError(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $geocoder->setHost('doesnotexist.opencagedata.com');
        $result = $geocoder->geocode('London');

        $this->assertEquals(498, $result['status']['code']);
        $this->assertStringContainsString('network issue', $result['status']['message']);
        $this->assertStringContainsString('doesnotexist.opencagedata.com', $result['status']['message']);
    }

    public function testSetTimeout(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $geocoder->setTimeout(1);
        $geocoder->setHost('doesnotexist.opencagedata.com');
        $result = $geocoder->geocode('London');
        $this->assertEquals(498, $result['status']['code']);
        $this->assertStringContainsString('network issue', $result['status']['message']);
    }

    public function testInvalidProxy(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid proxy URL');
        $geocoder->setProxy('not-a-valid-proxy');
    }

    public function testProxy(): void
    {
        $proxy = getenv('PROXY');
        if (!$proxy) {
            $this->markTestSkipped('PROXY environment variable not set');
        }
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        try {
            $geocoder->setProxy($proxy);
        } catch (\Exception $e) {
            $this->markTestSkipped('PROXY environment variable is not a valid proxy URL: ' . $proxy);
        }

        $result = $geocoder->geocode("82 Clerkenwell Road, London");
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }
}
