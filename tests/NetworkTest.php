<?php

namespace OpenCage\Geocoder\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use OpenCage\Geocoder\Geocoder;
use PHPUnit\Framework\Attributes\DataProvider;

class NetworkTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function invalidHostProvider(): array
    {
        return [
            'other domain'                     => ['www.example.com'],
            'subdomain, trailing newline'      => ["api.opencagedata.com\n"],
            'localhost:port, trailing newline' => ["localhost:8080\n"],
        ];
    }

    #[DataProvider('invalidHostProvider')]
    public function testInvalidHost(string $host): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        try {
            $geocoder->setHost($host);
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
        $this->assertStringNotContainsString(self::OPENCAGE_TEST_APIKEY_200, $result['status']['message']);
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

    /** @return array<string, array{string}> */
    public static function networkExceptionProvider(): array
    {
        return [
            'connect error'                    => ['GuzzleHttp\Exception\ConnectException'],
            'network timeout (Guzzle 8)'       => ['GuzzleHttp\Exception\NetworkTimeoutException'],
            'timeout after headers (Guzzle 8)' => ['GuzzleHttp\Exception\ResponseTimeoutException'],
        ];
    }

    #[DataProvider('networkExceptionProvider')]
    public function testNetworkExceptionReturns498(string $class): void
    {
        if (!class_exists($class)) {
            $this->markTestSkipped("$class exists only in Guzzle 8");
        }
        $request = new Request('GET', 'https://api.opencagedata.com/geocode/v1/json/?q=London&key=' . self::OPENCAGE_TEST_APIKEY_200);
        $message = 'cURL error 28: timed out for ' . $request->getUri();
        $exception = is_a($class, 'GuzzleHttp\Exception\ResponseTimeoutException', true)
            ? new $class($message, $request, new Response())
            : new $class($message, $request);
        if (!$exception instanceof \Throwable) {
            $this->fail("$class is not an exception");
        }

        $result = $this->geocoderFailingWith($exception)->geocode('London');
        /** @var array{status: array{code: int, message: string}} $asyncResult */
        $asyncResult = $this->geocoderFailingWith($exception)->geocodeAsync('London')->wait();

        foreach ([$result, $asyncResult] as $r) {
            $this->assertEquals(498, $r['status']['code']);
            $this->assertStringContainsString('network issue', $r['status']['message']);
            $this->assertStringNotContainsString(self::OPENCAGE_TEST_APIKEY_200, $r['status']['message']);
        }
    }

    public function testOtherTransferExceptionIsThrown(): void
    {
        $exception = new RequestException('cURL error 60: SSL certificate problem', new Request('GET', 'https://api.opencagedata.com/'));

        try {
            $this->geocoderFailingWith($exception)->geocode('London');
            $this->fail('sync: no exception thrown');
        } catch (RequestException $e) {
            $this->assertSame($exception, $e);
        }

        try {
            $this->geocoderFailingWith($exception)->geocodeAsync('London')->wait();
            $this->fail('async: no exception thrown');
        } catch (RequestException $e) {
            $this->assertSame($exception, $e);
        }
    }

    private function geocoderFailingWith(\Throwable $exception): Geocoder
    {
        return new class (self::OPENCAGE_TEST_APIKEY_200, $exception) extends Geocoder {
            public function __construct(string $key, private \Throwable $exception)
            {
                parent::__construct($key);
            }

            protected function buildClient(): Client
            {
                return new Client(['handler' => HandlerStack::create(new MockHandler([$this->exception]))]);
            }
        };
    }

    /** @return array<string, array{string}> */
    public static function invalidProxyProvider(): array
    {
        return [
            'garbage string'     => ['not-a-valid-proxy'],
            'missing scheme'     => ['//example.com:8080'],
            'unsupported scheme' => ['ftp://example.com:8080'],
            'empty host'         => ['http:'],
        ];
    }

    #[DataProvider('invalidProxyProvider')]
    public function testInvalidProxy(string $proxy): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid proxy URL');
        $geocoder->setProxy($proxy);
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
