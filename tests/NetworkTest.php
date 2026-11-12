<?php

namespace OpenCage\Geocoder\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use OpenCage\Geocoder\Exception\RequestFailedException;
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
            'port 0'                           => ['localhost:0'],
            'port out of range'                => ['localhost:65536'],
            'port far out of range'            => ['127.0.0.1:99999'],
            'IPv6 with port, no brackets'      => ['::1:8080'],
            'IPv6, port out of range'          => ['[::1]:65536'],
        ];
    }

    /** @return array<string, array{string, string, int|null}> */
    public static function hostUrlProvider(): array
    {
        return [
            'api'              => ['api.opencagedata.com', 'api.opencagedata.com', null],
            'localhost'        => ['localhost', 'localhost', null],
            'port'             => ['127.0.0.1:8080', '127.0.0.1', 8080],
            'lowest port'      => ['localhost:1', 'localhost', 1],
            'highest port'     => ['0.0.0.0:65535', '0.0.0.0', 65535],
            'IPv6'             => ['::1', '[::1]', null],
            'IPv6 in brackets' => ['[::1]', '[::1]', null],
            'IPv6 with port'   => ['[::1]:8080', '[::1]', 8080],
        ];
    }

    #[DataProvider('hostUrlProvider')]
    public function testValidHostBuildsParsableUrl(string $host, string $expectedHost, ?int $expectedPort): void
    {
        $geocoder = new class (self::OPENCAGE_TEST_APIKEY_200) extends Geocoder {
            public function exposeBuildUrl(string $query): string
            {
                return $this->buildUrl($query);
            }
        };
        $geocoder->setHost($host);

        // Guzzle parses the URL the same way before sending the request
        $uri = new Uri($geocoder->exposeBuildUrl('London'));
        $this->assertSame($expectedHost, $uri->getHost());
        $this->assertSame($expectedPort, $uri->getPort());
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

    public function testOtherTransferExceptionIsThrownWithoutKey(): void
    {
        $request = new Request('GET', 'https://api.opencagedata.com/geocode/v1/json/?q=London&key=' . self::OPENCAGE_TEST_APIKEY_200);
        $exception = new RequestException('cURL error 60: SSL certificate problem for ' . $request->getUri(), $request);

        $expected = ['cURL error 60', 'key=6d0e71...REDACTED'];
        $this->assertThrowsWithoutKey(fn () => $this->geocoderFailingWith($exception)->geocode('London'), ...$expected);
        $this->assertThrowsWithoutKey(fn () => $this->geocoderFailingWith($exception)->geocodeAsync('London')->wait(), ...$expected);
    }

    public function testOtherExceptionIsThrownWithoutKey(): void
    {
        // not a Guzzle exception, e.g. 'Can not initialize cURL handle.'
        $exception = new \RuntimeException('some failure');

        $expected = 'request failed: some failure';
        $this->assertThrowsWithoutKey(fn () => $this->geocoderFailingWith($exception)->geocode('London'), $expected);
        $this->assertThrowsWithoutKey(fn () => $this->geocoderFailingWith($exception)->geocodeAsync('London')->wait(), $expected);
    }

    public function testUnparsableUrlIsThrownWithoutKey(): void
    {
        // setHost() rejects this port, so set the URL directly. Guzzle can't parse it and sends
        // no request. Guzzle 7's message contains the full URL, Guzzle 8's leaves out the query string
        $geocoder = new class (self::OPENCAGE_TEST_APIKEY_200) extends Geocoder {
            public function __construct(string $key)
            {
                parent::__construct($key);
                $this->url = 'https://localhost:99999/geocode/v1/json/?';
            }
        };

        $this->assertThrowsWithoutKey(fn () => $geocoder->geocode('London'), 'localhost:99999');
        $this->assertThrowsWithoutKey(fn () => $geocoder->geocodeAsync('London')->wait(), 'localhost:99999');
    }

    private function assertThrowsWithoutKey(callable $request, string ...$expectedInMessage): void
    {
        // record stack trace arguments, like PHP does without a php.ini
        $ignoreArgs = ini_set('zend.exception_ignore_args', '0');
        try {
            $request();
        } catch (\Throwable $e) {
            // not Guzzle's exception, and not chained: its message and handler context hold the key
            $this->assertSame(RequestFailedException::class, $e::class);
            $this->assertInstanceOf(\RuntimeException::class, $e);
            $this->assertNull($e->getPrevious());
            foreach ($expectedInMessage as $expected) {
                $this->assertStringContainsString($expected, $e->getMessage());
            }
            $this->assertStringNotContainsString(self::OPENCAGE_TEST_APIKEY_200, $e->getMessage());
            $this->assertNotEmpty(array_column($e->getTrace(), 'args'), 'stack trace arguments are recorded');
            foreach ($this->stringArgsInTrace($e) as $arg) {
                $this->assertStringNotContainsString(self::OPENCAGE_TEST_APIKEY_200, $arg);
            }
            return;
        } finally {
            if ($ignoreArgs !== false) {
                ini_set('zend.exception_ignore_args', $ignoreArgs);
            }
        }
        $this->fail('no exception thrown');
    }

    /** @return list<string> */
    private function stringArgsInTrace(\Throwable $e): array
    {
        $strings = [];
        foreach ($e->getTrace() as $frame) {
            foreach ($frame['args'] ?? [] as $arg) {
                if (is_string($arg)) {
                    $strings[] = $arg;
                }
            }
        }
        return $strings;
    }

    /** @return array<string, array{string}> */
    public static function configuredKeyProvider(): array
    {
        return [
            'valid key'        => [self::OPENCAGE_TEST_APIKEY_200],
            'leading space'    => [' ' . self::OPENCAGE_TEST_APIKEY_200],
            'quoted'           => ['"' . self::OPENCAGE_TEST_APIKEY_200 . '"'],
            'trailing newline' => [self::OPENCAGE_TEST_APIKEY_200 . "\n"],
        ];
    }

    #[DataProvider('configuredKeyProvider')]
    public function testNetworkErrorMessageRedactsUrlEncodedKey(string $key): void
    {
        // a key with stray characters, e.g. from a config file, is URL-encoded in the request URL
        $request = new Request('GET', 'https://api.opencagedata.com/geocode/v1/json/?q=London&key=' . urlencode($key));
        $exception = new ConnectException('cURL error 7: Failed to connect for ' . $request->getUri(), $request);

        $result = $this->geocoderFailingWith($exception)->geocode('London');

        $this->assertEquals(498, $result['status']['code']);
        $this->assertStringContainsString('...REDACTED', $result['status']['message']);
        $this->assertStringNotContainsString(substr(self::OPENCAGE_TEST_APIKEY_200, 6), $result['status']['message']);
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

    public function testInvalidProxyIsThrownWithoutCredentials(): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $ignoreArgs = ini_set('zend.exception_ignore_args', '0');
        try {
            $geocoder->setProxy('ftp://user:s3cret@proxy.example.com:8080');
        } catch (\Exception $e) {
            $this->assertStringNotContainsString('s3cret', $e->getMessage());
            $this->assertNotEmpty(array_column($e->getTrace(), 'args'), 'stack trace arguments are recorded');
            foreach ($this->stringArgsInTrace($e) as $arg) {
                $this->assertStringNotContainsString('s3cret', $arg);
            }
            return;
        } finally {
            if ($ignoreArgs !== false) {
                ini_set('zend.exception_ignore_args', $ignoreArgs);
            }
        }
        $this->fail('no exception thrown');
    }

    /** @return array<string, array{string}> */
    public static function validProxyProvider(): array
    {
        return [
            'http'    => ['http://proxy.example.com:8080'],
            'https'   => ['https://proxy.example.com:8080'],
            'socks5'  => ['socks5://proxy.example.com:1080'],
            'socks5h' => ['socks5h://proxy.example.com:1080'],
        ];
    }

    #[DataProvider('validProxyProvider')]
    public function testValidProxy(string $proxy): void
    {
        $geocoder = new Geocoder(self::OPENCAGE_TEST_APIKEY_200);
        $this->expectNotToPerformAssertions();
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
            // without the value: a proxy URL can contain credentials
            $this->markTestSkipped('PROXY environment variable is not a valid proxy URL');
        }

        $result = $geocoder->geocode("82 Clerkenwell Road, London");
        $this->assertEquals(200, $result['status']['code']);
        $this->assertEquals('OK', $result['status']['message']);
    }
}
