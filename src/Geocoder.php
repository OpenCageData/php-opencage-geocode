<?php

namespace OpenCage\Geocoder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use OpenCage\Geocoder\Exception\RequestFailedException;
use Psr\Http\Client\NetworkExceptionInterface;

class Geocoder
{
    public const VERSION  = '4.2.0';  // if changing this => remember to match everything with the git tag

    public const TIMEOUT = 10;
    public const URL = 'https://api.opencagedata.com/geocode/v1/json/?';
    public const PROXY = null;

    protected ?string $key = null;
    protected int $timeout;
    protected string $url;
    protected ?string $proxy = null;
    protected readonly string $user_agent;
    protected ?Client $client = null;

    public function __construct(?string $key = null)
    {
        if (isset($key) && !empty($key)) {
            $this->setKey($key);
        }
        $this->setTimeout(self::TIMEOUT);
        $this->url = self::URL;
        $this->user_agent = 'opencage-php/' . self::VERSION . ' (PHP ' . phpversion() . '; ' . php_uname('s') . ' ' . php_uname('r') . ')';
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
        $this->client = null;
    }

    public function setProxy(string $proxy): void
    {
        $parsed = parse_url($proxy);
        if (
            $parsed === false
            || !isset($parsed['scheme'])
            || !in_array($parsed['scheme'], ['http', 'https', 'socks5', 'socks5h'], true)
            || !isset($parsed['host'])
        ) {
            throw new \Exception('Invalid proxy URL: must include a valid scheme (http, https, socks5 or socks5h) and host');
        }
        $this->proxy = $proxy;
        $this->client = null;
    }

    public function setHost(string $host): void
    {
        if (!$this->isValidHost($host)) {
            throw new \Exception('Invalid host: must be localhost or an opencagedata.com subdomain');
        }
        if ($host === '::1') {
            // an IPv6 address needs brackets in a URL
            $host = '[::1]';
        }
        $this->url = str_replace('api.opencagedata.com', $host, self::URL);
        $this->client = null;
    }

    /**
     * @param array<string, string> $optParams
     * @return array{status: array{code: int, message: string}, results: list<mixed>, total_results: int, ...}
     */
    public function geocode(string $query, array $optParams = []): array
    {
        $url = $this->buildUrl($query, $optParams);

        $ret = json_decode($this->getJSON($url), true);
        if (!is_array($ret)) {
            throw new \Exception('Failed to decode API response');
        }
        /** @var array{status: array{code: int, message: string}, results: list<mixed>, total_results: int, ...} */
        return $ret;
    }

    /**
     * @param array<string, string> $optParams
     * @return array{status: array{code: int, message: string}, results: list<mixed>, total_results: int, ...}
     */
    public function geocodeReverse(float|string $latitude, float|string $longitude, array $optParams = []): array
    {
        return $this->geocode($latitude . ',' . $longitude, $optParams);
    }

    /** @param array<string, string> $optParams */
    public function geocodeReverseAsync(float|string $latitude, float|string $longitude, array $optParams = []): PromiseInterface
    {
        return $this->geocodeAsync($latitude . ',' . $longitude, $optParams);
    }

    /** @param array<string, string> $optParams */
    public function geocodeAsync(string $query, array $optParams = []): PromiseInterface
    {
        $url = $this->buildUrl($query, $optParams);

        if ($this->client === null) {
            $this->client = $this->buildClient();
        }

        try {
            $promise = $this->client->getAsync($url, ['http_errors' => false]);
        } catch (\InvalidArgumentException $e) {
            // Guzzle parses the URL before returning a promise
            throw $this->redactedException($e);
        }

        return $promise
            ->then(function (\Psr\Http\Message\ResponseInterface $response) {
                $ret = json_decode((string) $response->getBody(), true);
                if (!is_array($ret)) {
                    throw new \Exception('Failed to decode API response');
                }
                return $ret;
            }, function (mixed $reason) {
                if (!$reason instanceof \Throwable) {
                    return Create::rejectionFor($reason);
                }
                if ($this->isNetworkError($reason)) {
                    return json_decode($this->generateErrorJSON(498, 'network issue ' . $this->sanitizeMessage($reason->getMessage())), true);
                }
                return Create::rejectionFor($this->redactedException($reason));
            });
    }

    /** @param array<string, string> $optParams */
    protected function buildUrl(string $query, array $optParams = []): string
    {
        $url = $this->url . 'q=' . urlencode($query);

        if (!empty($optParams)) {
            foreach ($optParams as $param => $paramValue) {
                // the API reads parameter names case-insensitively and uses the first 'key' it finds,
                // so an optional 'key' (or 'KEY') would replace the configured API key
                if (in_array(strtolower($param), ['q', 'key'], true)) {
                    continue;
                }
                $url .= '&' . urlencode($param) . '=' . urlencode($paramValue);
            }
        }

        if (empty($this->key)) {
            throw new \Exception('Missing API key');
        }
        $url .= '&key=' . urlencode($this->key);

        return $url;
    }

    protected function isValidHost(string $host): bool
    {
        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1', '[::1]'], true)) {
            return true;
        }

        // \z rather than $, which would also accept a trailing newline

        // localhost with port. IPv6 only in brackets: in '::1:8080' the port would be part of the address
        if (preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0|\[::1\]):(\d{1,5})\z/', $host, $matches)) {
            $port = (int) $matches[2];
            return $port >= 1 && $port <= 65535;
        }

        // opencagedata.com or any subdomain
        if (preg_match('/^([a-zA-Z0-9-]+\.)*opencagedata\.com\z/', $host)) {
            return true;
        }

        return false;
    }

    protected function buildClient(): Client
    {
        $config = [
            'timeout' => $this->timeout,
            'verify' => true,
            'allow_redirects' => false,
            'headers' => [
                'User-Agent' => $this->user_agent,
            ],
        ];

        if ($this->proxy) {
            $config['proxy'] = $this->proxy;
        }

        return new Client($config);
    }

    protected function getJSON(string $query): string
    {
        if ($this->client === null) {
            $this->client = $this->buildClient();
        }

        try {
            $response = $this->client->get($query, ['http_errors' => false]);
            return (string) $response->getBody();
        } catch (GuzzleException | \InvalidArgumentException $e) {
            // \InvalidArgumentException: Guzzle's MalformedUriException isn't a GuzzleException
            if ($this->isNetworkError($e)) {
                return $this->generateErrorJSON(498, 'network issue ' . $this->sanitizeMessage($e->getMessage()));
            }
            throw $this->redactedException($e);
        }
    }

    protected function redactedException(\Throwable $e): RequestFailedException
    {
        // not chained as $previous: the original's message and handler context hold the full key
        return new RequestFailedException('request failed: ' . $this->sanitizeMessage($e->getMessage()));
    }

    protected function isNetworkError(\Throwable $e): bool
    {
        // Guzzle 7 throws ConnectException for all connection problems and timeouts.
        // Guzzle 8 adds NetworkException, and ResponseTimeoutException for timeouts
        // after the response headers arrived; that class doesn't exist in Guzzle 7.
        return $e instanceof NetworkExceptionInterface
            || is_a($e, 'GuzzleHttp\Exception\ResponseTimeoutException');
    }

    protected function sanitizeMessage(string $message): string
    {
        // redact whatever follows key=, not only alphanumeric keys: a valid key with a stray
        // character, e.g. a leading space from a config file, is URL-encoded ('+6d0e...')
        return (string) preg_replace('/([?&])key=([^&]{0,6})[^&]*/', '$1key=$2...REDACTED', $message);
    }

    protected function generateErrorJSON(int $code, string $message): string
    {
        $response = [
                        'results' => [],
                        'total_results' => 0,
                        'status' => [
                            'code' => $code,
                            'message' => $message
                        ]
                    ];
        return (string) json_encode($response);
    }
}
