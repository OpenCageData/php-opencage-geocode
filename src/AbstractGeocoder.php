<?php

namespace OpenCage\Geocoder;

abstract class AbstractGeocoder
{
    public const VERSION  = '3.4.0';  // if changing this => remember to match everything with the git tag

    public const TIMEOUT = 10;
    public const URL = 'https://api.opencagedata.com/geocode/v1/json/?';
    public const PROXY = null;

    protected $key;
    protected $timeout;
    protected $url;
    protected $proxy;
    protected $user_agent;

    public function __construct($key = null)
    {
        if (isset($key) && !empty($key)) {
            $this->setKey($key);
        }
        $this->setTimeout(self::TIMEOUT);
        $this->url = self::URL;
        $this->user_agent = 'opencage-php/' . self::VERSION . ' (PHP ' . phpversion() . '; ' . php_uname('s') . ' ' . php_uname('r') . ')';
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function setProxy($proxy)
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
    }

    public function setHost($host)
    {
        if (!$this->isValidHost($host)) {
            throw new \Exception('Invalid host: must be localhost or an opencagedata.com subdomain');
        }
        $this->url = str_replace('api.opencagedata.com', $host, self::URL);
    }

    protected function isValidHost($host)
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

    protected function getJSON($query)
    {
        if (function_exists('curl_version') && !getenv('SKIP_CURL')) {
            $ret = $this->getJSONByCurl($query);
            return $ret;
        } elseif (ini_get('allow_url_fopen')) {
            if ($this->proxy) {
                throw new \Exception('Proxy support requires the CURL extension');
            }
            $ret = $this->getJSONByFopen($query);
            return $ret;
        } else {
            throw new \Exception('PHP is not compiled with CURL support and allow_url_fopen is disabled; giving up');
        }
    }

    protected function getJSONByFopen($query)
    {
        $context = stream_context_create(
            [
                'http' => [
                    'user_agent' => $this->user_agent,
                    'timeout' => $this->timeout
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true
                ]
            ]
        );

        error_clear_last();

        $ret = @file_get_contents($query, false, $context);
        if ($ret === false) {
            if (function_exists('http_get_last_response_headers')) {
                $http_response_header = http_get_last_response_headers();
            }

            $response_headers = isset($http_response_header) ? $http_response_header : null;

            /** @phpstan-ignore-next-line */
            if (isset($response_headers) && is_array($response_headers)) {
                $error_message = $response_headers[0];
                if ($error = error_get_last()) {
                    $error_message = $error['message'];
                }

                // print "got an eror: $error\n";
                if (preg_match('/ 401 /', $error_message)) {
                    // failed to open stream: HTTP request failed! HTTP/1.1 401 Unauthorized
                    return $this->generateErrorJSON(401, 'invalid API key');
                } elseif (preg_match('/ 402 /', $error_message)) {
                    // failed to open stream: HTTP request failed! HTTP/1.1 402 Payment Required
                    return $this->generateErrorJSON(402, 'quota exceeded');
                }
            } else {
                // failed to open stream: php_network_getaddresses: getaddrinfo failed: No address associated with hostname
                return $this->generateErrorJSON(498, "network issue accessing $query");
            }
        }

        return $ret;
    }

    protected function generateErrorJSON($code, $message)
    {
        $response = [
                        'results' => [],
                        'total_results' => 0,
                        'status' => [
                            'code' => $code,
                            'message' => $message
                        ]
                    ];
        return json_encode($response);
    }

    protected function getJSONByCurl($query)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_URL => $query,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ];
        if ($this->proxy) {
            $options[CURLOPT_PROXY] = $this->proxy;
        }
        curl_setopt_array($ch, $options);

        $headers = [
            'User-Agent: ' . $this->user_agent
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $ret = curl_exec($ch);
        if ($ret === false) {
            return $this->generateErrorJSON(498, 'network issue ' . curl_error($ch));
        }
        return $ret;
    }

    abstract public function geocode($query);
}
