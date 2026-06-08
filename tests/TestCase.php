<?php

namespace OpenCage\Geocoder\Test;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    // https://opencagedata.com/api#testingkeys
    protected const OPENCAGE_TEST_APIKEY_200 = '6d0e711d72d74daeb2b0bfd2a5cdfdba';
    protected const OPENCAGE_TEST_APIKEY_402 = '4372eff77b8343cebfc843eb4da4ddc4';
    protected const OPENCAGE_TEST_APIKEY_403_DISABLED = '2e10e5e828262eb243ec0b54681d699a';
    protected const OPENCAGE_TEST_APIKEY_403_IP_REJECTED = '6c79ee8e1ca44ad58ad1fc493ba9542f';
    protected const OPENCAGE_TEST_APIKEY_429 = 'd6d0f0065f4348a4bdfe4587ba02714b';
}
