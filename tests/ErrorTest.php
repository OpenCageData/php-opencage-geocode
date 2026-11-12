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
    public static function missingKeyProvider(): array
    {
        return [
            'empty string' => [''],
            'zero'         => ['0'],
        ];
    }

    #[DataProvider('missingKeyProvider')]
    public function testKeyIsMissing(string $key): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing API key');
        (new Geocoder($key))->geocode('Johannesburg');
    }

    #[DataProvider('missingKeyProvider')]
    public function testSetKeyIsMissing(string $key): void
    {
        $geocoder = new Geocoder();
        $geocoder->setKey($key);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing API key');
        $geocoder->geocode('Johannesburg');
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

    public function testOptionalParamValuesAreConvertedToStrings(): void
    {
        $geocoder = new class (self::OPENCAGE_TEST_APIKEY_200) extends Geocoder {
            /** @param array<string, scalar|null> $optParams */
            public function exposeBuildUrl(string $query, array $optParams): string
            {
                return $this->buildUrl($query, $optParams);
            }
        };
        $url = $geocoder->exposeBuildUrl('Johannesburg', [
            'limit' => 1,
            'no_annotations' => true,
            'abbrv' => false,
            'min_confidence' => 2.5,
            'language' => null,
            'countrycode' => 'za',
        ]);

        $this->assertStringContainsString('?q=Johannesburg&limit=1&no_annotations=1&abbrv=0&min_confidence=2.5&language=&countrycode=za&key=', $url);
    }

    public function testOptionalParamArrayValueIsRejected(): void
    {
        // what an application gets from ?language[]=en when it passes request input through
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid value for optional parameter 'language'");
        $geocoder->geocode('Johannesburg', ['language' => ['en']]); // @phpstan-ignore argument.type
    }

    public function testOptionalKeyDoesNotReplaceConfiguredKey(): void
    {
        // before the fix the API used the optional key: it reads parameter names case-insensitively
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
