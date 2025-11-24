<?php

namespace OpenCage\Geocoder;

abstract class AbstractGeocoder
{
    const VERSION  = '3.3.2';  // if changing this => remember to match everything with the git tag

    const TIMEOUT = 10;
    const URL = 'https://api.opencagedata.com/geocode/v1/json/?';
    const PROXY = null;

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
        $this->proxy = $proxy;
    }

    protected function getJSON($query)
    {
        if (function_exists('curl_version') && !getenv('SKIP_CURL')) {
            $ret = $this->getJSONByCurl($query);
            return $ret;
        } elseif (ini_get('allow_url_fopen')) {
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
                ]
            ]
        );

        error_clear_last();

        $ret = @file_get_contents($query);

        if ($ret === false) {
            /** NOTE: https://github.com/phpstan/phpstan/issues/3213 */
            /** @phpstan-ignore-next-line */
            if (isset($http_response_header) && is_array($http_response_header)) {
                $error_message = $http_response_header[0];
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
            CURLOPT_RETURNTRANSFER => true
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
            return $this->generateErrorJSON(498, 'network issue '.curl_error($ch));
        }
        return $ret;
    }

    abstract public function geocode($query);
}
