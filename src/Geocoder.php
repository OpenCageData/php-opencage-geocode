<?php

namespace OpenCage\Geocoder;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class Geocoder
{
    public const VERSION  = '3.4.0';  // if changing this => remember to match everything with the git tag

    public const TIMEOUT = 10;
    public const URL = 'https://api.opencagedata.com/geocode/v1/json/?';
    public const PROXY = null;

    protected ?string $key = null;
    protected int $timeout;
    protected string $url;
    protected ?string $proxy = null;
    protected string $user_agent;
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
            || !in_array($parsed['scheme'], ['http', 'https', 'socks5'], true)
            || !isset($parsed['host'])
        ) {
            throw new \Exception('Invalid proxy URL: must include a valid scheme (http, https, or socks5) and host');
        }
        $this->proxy = $proxy;
        $this->client = null;
    }

    public function setHost(string $host): void
    {
        if (!$this->isValidHost($host)) {
            throw new \Exception('Invalid host: must be localhost or an opencagedata.com subdomain');
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
        $url = $this->url . 'q=' . urlencode($query);

        if (!empty($optParams)) {
            foreach ($optParams as $param => $paramValue) {
                $url .= '&' . urlencode($param) . '=' . urlencode($paramValue);
            }
        }

        if (empty($this->key)) {
            throw new \Exception('Missing API key');
        }
        $url .= '&key=' . urlencode($this->key);

        $ret = json_decode($this->getJSON($url), true);
        if (!is_array($ret)) {
            throw new \Exception('Failed to decode API response');
        }
        /** @var array{status: array{code: int, message: string}, results: list<mixed>, total_results: int, ...} */
        return $ret;
    }

    protected function isValidHost(string $host): bool
    {
        if (in_array($host, ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)) {
            return true;
        }

        // localhost with port
        if (preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0)\:\d+$/', $host)) {
            return true;
        }

        // opencagedata.com or any subdomain
        if (preg_match('/^([a-zA-Z0-9-]+\.)*opencagedata\.com$/', $host)) {
            return true;
        }

        return false;
    }

    protected function buildClient(): Client
    {
        $config = [
            'timeout' => $this->timeout,
            'verify' => true,
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
        } catch (ConnectException $e) {
            return $this->generateErrorJSON(498, 'network issue ' . $e->getMessage());
        }
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
