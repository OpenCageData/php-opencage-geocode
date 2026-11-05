<?php

namespace OpenCage\Geocoder\Test;

use OpenCage\Geocoder\Geocoder;
use PHPUnit\Framework\Attributes\DataProvider;

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

    /** @return array<string, array{string}> */
    public static function reservedParamProvider(): array
    {
        return [
            'key'              => ['key'],
            'key, uppercase'   => ['KEY'],
            'key, mixed case'  => ['Key'],
            'query'            => ['q'],
            'query, uppercase' => ['Q'],
        ];
    }

    #[DataProvider('reservedParamProvider')]
    public function testReservedOptionalParamIsDropped(string $param): void
    {
        $geocoder = new class (self::OPENCAGE_TEST_APIKEY_200) extends Geocoder {
            /** @param array<string, string> $optParams */
            public function exposeBuildUrl(string $query, array $optParams): string
            {
                return $this->buildUrl($query, $optParams);
            }
        };
        $url = $geocoder->exposeBuildUrl('Johannesburg', [$param => 'DROPPED', 'language' => 'en']);

        $this->assertStringNotContainsString('DROPPED', $url);
        $this->assertStringContainsString('?q=Johannesburg&language=en&', $url);
        $this->assertStringEndsWith('&key=' . self::OPENCAGE_TEST_APIKEY_200, $url);
    }

    public function testOptionalKeyDoesNotReplaceConfiguredKey(): void
    {
        // before the fix the API used the optional key, because it reads the first 'key' parameter
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $result = $geocoder->geocode('Johannesburg', ['KEY' => self::OPENCAGE_TEST_APIKEY_402]);
        $this->assertEquals(200, $result['status']['code']);
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
