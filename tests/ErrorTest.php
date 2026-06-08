<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;

class ErrorTest extends TestCase
{
    public function testMissingKey(): void
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

    public function testInvalidKey(): void
    {
        $geocoder = new Geocoder('invalid-APIKEY');
        $result = $geocoder->geocode('Johannesburg');
        $this->assertEquals(401, $result['status']['code']);
        $this->assertEquals('invalid API key', $result['status']['message']);
    }

    public function testOverQuota(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_402);
        $result = $geocoder->geocode('Johannesburg');
        $this->assertEquals(402, $result['status']['code']);
        $this->assertEquals('quota exceeded', $result['status']['message']);
    }

    public function testRateLimited(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_429);
        $result = $geocoder->geocode('Johannesburg');
        $this->assertEquals(429, $result['status']['code']);
    }

    public function testIpRejected(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_403_IP_REJECTED);
        $result = $geocoder->geocode('Johannesburg');
        $this->assertEquals(403, $result['status']['code']);
        $this->assertStringContainsString('IP address rejected', $result['status']['message']);
    }

    public function testDisabledKey(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_403_DISABLED);
        $result = $geocoder->geocode('Johannesburg');
        $this->assertEquals(403, $result['status']['code']);
        $this->assertStringContainsString('disabled', $result['status']['message']);
    }
}
