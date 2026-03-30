<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;

class GeocoderTest extends \PHPUnit\Framework\TestCase
{
    // https://opencagedata.com/api#testingkeys
    private const OPENCAGE_TEST_APIKEY_200 = '6d0e711d72d74daeb2b0bfd2a5cdfdba';
    private const OPENCAGE_TEST_APIKEY_402 = '4372eff77b8343cebfc843eb4da4ddc4';
    private const OPENCAGE_TEST_APIKEY_403_DISABLED = '2e10e5e828262eb243ec0b54681d699a';
    private const OPENCAGE_TEST_APIKEY_403_IP_REJECTED = '6c79ee8e1ca44ad58ad1fc493ba9542f';
    private const OPENCAGE_TEST_APIKEY_429 = 'd6d0f0065f4348a4bdfe4587ba02714b';

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
        // print_r($result);
        $this->assertEquals(401, $result['status']['code']);
        $this->assertEquals('invalid API key', $result['status']['message']);
    }

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
        // print_r($result);

        $this->assertEquals(498, $result['status']['code']);
        $this->assertStringContainsString('network issue', $result['status']['message']);
        $this->assertStringContainsString('doesnotexist.opencagedata.com', $result['status']['message']);
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

    public function testLondon(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $query = "82 Clerkenwell Road, London";
        $result = $geocoder->geocode($query);

        // print_r($result);

        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }

    public function testAsyncLondon(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $promise = $geocoder->geocodeAsync("82 Clerkenwell Road, London");
        /** @var array{status: array{code: int, message: string}} $result */
        $result = $promise->wait();

        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }

    public function testAsyncNetworkError(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $geocoder->setHost('doesnotexist.opencagedata.com');
        $promise = $geocoder->geocodeAsync('London');
        /** @var array{status: array{code: int, message: string}} $result */
        $result = $promise->wait();

        $this->assertEquals(498, $result['status']['code']);
        $this->assertStringContainsString('network issue', $result['status']['message']);
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
