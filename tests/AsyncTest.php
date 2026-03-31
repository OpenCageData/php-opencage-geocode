<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;

class AsyncTest extends TestCase
{
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

    public function testAsyncOverQuota(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_402);
        $promise = $geocoder->geocodeAsync('Johannesburg');
        /** @var array{status: array{code: int, message: string}} $result */
        $result = $promise->wait();

        $this->assertEquals(402, $result['status']['code']);
        $this->assertEquals('quota exceeded', $result['status']['message']);
    }

    public function testAsyncDisabledKey(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_403_DISABLED);
        $promise = $geocoder->geocodeAsync('Johannesburg');
        /** @var array{status: array{code: int, message: string}} $result */
        $result = $promise->wait();

        $this->assertEquals(403, $result['status']['code']);
        $this->assertStringContainsString('disabled', $result['status']['message']);
    }

    public function testAsyncMissingKey(): void
    {
        $geocoder = new Geocoder();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing API key');
        $geocoder->geocodeAsync('London');
    }

    public function testReverseGeocodeAsync(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $promise = $geocoder->geocodeReverseAsync(51.5227, -0.1025);
        /** @var array{status: array{code: int, message: string}} $result */
        $result = $promise->wait();

        $this->assertEquals(200, $result['status']['code']);
    }
}
