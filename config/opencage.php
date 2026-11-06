<?php

// Configuration for the OpenCage Laravel integration, see the README

return [
    // your OpenCage API key
    'key' => env('OPENCAGE_API_KEY'),

    // request timeout in seconds
    'timeout' => env('OPENCAGE_TIMEOUT', 10),

    // optional proxy URL, with the scheme http, https, socks5 or socks5h
    'proxy' => env('OPENCAGE_PROXY'),
];
