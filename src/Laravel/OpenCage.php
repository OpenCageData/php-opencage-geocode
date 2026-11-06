<?php

namespace OpenCage\Geocoder\Laravel;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array<string, mixed> geocode(string $query, array<string, string> $optParams = [])
 * @method static array<string, mixed> geocodeReverse(float $latitude, float $longitude, array<string, string> $optParams = [])
 * @method static PromiseInterface geocodeAsync(string $query, array<string, string> $optParams = [])
 * @method static PromiseInterface geocodeReverseAsync(float $latitude, float $longitude, array<string, string> $optParams = [])
 *
 * @see \OpenCage\Geocoder\Geocoder
 */
class OpenCage extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'opencage';
    }
}
