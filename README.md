# OpenCage Geocoding API Library for PHP

A [PHP](http://php.net/) library to use the [OpenCage geocoding API](https://opencagedata.com/api).

## Build Status / Code Quality

[![Build Status](https://github.com/OpenCageData/php-opencage-geocode/actions/workflows/ci.yml/badge.svg)](https://github.com/OpenCageData/php-opencage-geocode/actions/workflows/ci.yml)
[![PHP version](https://badge.fury.io/ph/opencage%2Fgeocode.svg)](https://badge.fury.io/ph/opencage%2Fgeocode)
![Mastodon Follow](https://img.shields.io/mastodon/follow/109287663468501769?domain=https%3A%2F%2Fen.osm.town%2F&style=social)

## Overview
This library attempts to use the [CURL](http://www.php.net/manual/en/book.curl.php)
extension to access the OpenCage Geocoding API. If CURL support is not available, the
library falls back to using [fopen wrappers](http://uk3.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen).

To use the library you must either have the CURL extension compiled into your version
of PHP. Alternatively configure the use of fopen wrappers via the `allow_url_fopen`
directive in your `php.ini`.

### Authentication

You need an API key, which you can sign up for [here](https://opencagedata.com).

### Tutorial

You can find [a comprehensive tutorial for using this module on the OpenCage site](https://opencagedata.com/tutorials/geocode-in-php).

## Installation

### With Composer

The recommended - and easiest way - to install is via [Composer](https://getcomposer.org/).
Require the library in your project's `composer.json` file.

```php
$ composer require opencage/geocode
```

Import the Geocoder class.

```php
require "vendor/autoload.php";
```

### The old fashioned way

See the file `demo/geocode.php`


## Geocoding

```php
$geocoder = new \OpenCage\Geocoder\Geocoder('YOUR-API-KEY');
$result = $geocoder->geocode('82 Clerkenwell Road, London');
print_r($result);
```

### Reverse geocoding

```php
$geocoder = new \OpenCage\Geocoder\Geocoder('YOUR-API-KEY');
$result = $geocoder->geocode('43.831,4.360'); # latitude,longitude (y,x)
print $result['results'][0]['formatted'];
// 3 Rue de Rivarol, 30020 Nîmes, France
```

### Set optional parameters

See the full list at:
[https://opencagedata.com/api#optional-params](https://opencagedata.com/api#optional-params)

```php
$result = $geocoder->geocode('6 Rue Massillon, 30020 Nîmes, France', [
    'language' => 'fr',
    'countrycode' => 'fr'
]);
if ($result && $result['total_results'] > 0) {
  $first = $result['results'][0];
  print $first['geometry']['lng'] . ';' . $first['geometry']['lat'] . ';' . $first['formatted'] . "\n";
  // 4.360081;43.8316276;6 Rue Massillon, 30020 Nîmes, Frankreich
}
```

### Set a proxy URL

```php
$geocoder->setProxy('http://proxy.example.com:1234');
$result = $geocoder->geocode("Brandenburger Tor, Berlin");
print_r($result['results'][0]['formatted']);
// Brandenburger Tor, Unter den Linden, 10117 Berlin, Germany
print_r($result['results'][0]['geometry']);
// Array
// (
//    [lat] => 52.5166047
//    [lng] => 13.3809897
// )
```

## Example results


```php
Array
(
    [total_results] => 2
    [status] => Array
        (
            [message] => OK
            [code] => 200
        )
    [results] => Array
        (
            [0] => Array
                (
                    [annotations] => Array
                        (
                            [DMS] => Array
                                (
                                    [lat] => 51° 31' 21.60894'' N
                                    [lng] => 0° 6' 8.95198'' E
                                )

                            [MGRS] => 30UYC0100511930
                            [Maidenhead] => IO91wm75qk
                            [Mercator] => Array
                                (
                                    [x] => -11408.763
                                    [y] => 6680801.955
                                )

                            [OSGB] => Array
                                (
                                    [easting] => 531628.199
                                    [gridref] => TQ 316 821
                                    [northing] => 182177.015
                                )

                            [OSM] => Array
                                (
                                    [url] => http://www.openstreetmap.org/?mlat=51.52267&mlon=-0.10249#map=17/51.52267/-0.10249
                                )

                            [callingcode] => 44
                            [geohash] => gcpvjemm7csmhczg9cvt
                            [sun] => Array
                                (
                                    [rise] => Array
                                        (
                                            [apparent] => 1452931140
                                            [astronomical] => 1452923940
                                            [civil] => 1452928800
                                            [nautical] => 1452926280
                                        )

                                    [set] => Array
                                        (
                                            [apparent] => 1452961320
                                            [astronomical] => 1452968520
                                            [civil] => 1452963660
                                            [nautical] => 1452966120
                                        )

                                )

                            [timezone] => Array
                                (
                                    [name] => Europe/London
                                    [now_in_dst] => 0
                                    [offset_sec] => 0
                                    [offset_string] => 0
                                    [short_name] => GMT
                                )

                            [what3words] => Array
                                (
                                    [words] => gallons.trim.tips
                                )

                        )

                    [bounds] => Array
                        (
                            [northeast] => Array
                                (
                                    [lat] => 51.5227563
                                    [lng] => -0.1023801
                                )

                            [southwest] => Array
                                (
                                    [lat] => 51.5226042
                                    [lng] => -0.1025907
                                )

                        )

                    [components] => Array
                        (
                            [city] => London
                            [country] => United Kingdom
                            [country_code] => gb
                            [house_number] => 82
                            [postcode] => EC1M 5RF
                            [road] => Clerkenwell Road
                            [state] => England
                            [state_district] => Greater London
                            [suburb] => Clerkenwell
                        )

                    [confidence] => 10
                    [formatted] => 82 Clerkenwell Road, London EC1M 5RF, United Kingdom
                    [geometry] => Array
                        (
                            [lat] => 51.52266915
                            [lng] => -0.10248666188363
                        )

                )

            [1] => Array
                (
                    [annotations] => Array
                        (
                            [DMS] => Array
                                (
                                    [lat] => 51° 30' 30.70800'' N
                                    [lng] => 0° 7' 32.66400'' E
                                )

                            [MGRS] => 30UXC9945410295
                            [Maidenhead] => IO91wm42vb
                            [Mercator] => Array
                                (
                                    [x] => -13997.313
                                    [y] => 6678279.278
                                )

                            [OSGB] => Array
                                (
                                    [easting] => 530055.544
                                    [gridref] => TQ 300 805
                                    [northing] => 180563.298
                                )

                            [OSM] => Array
                                (
                                    [url] => http://www.openstreetmap.org/?mlat=51.50853&mlon=-0.12574#map=17/51.50853/-0.12574
                                )

                            [geohash] => gcpvj0u6yjcmwxz8bn43
                            [sun] => Array
                                (
                                    [rise] => Array
                                        (
                                            [apparent] => 1452931140
                                            [astronomical] => 1452923940
                                            [civil] => 1452928800
                                            [nautical] => 1452926340
                                        )

                                    [set] => Array
                                        (
                                            [apparent] => 1452961320
                                            [astronomical] => 1452968520
                                            [civil] => 1452963660
                                            [nautical] => 1452966120
                                        )

                                )

                            [timezone] => Array
                                (
                                    [name] => Europe/London
                                    [now_in_dst] => 0
                                    [offset_sec] => 0
                                    [offset_string] => 0
                                    [short_name] => GMT
                                )

                            [what3words] => Array
                                (
                                    [words] => thing.then.link
                                )

                        )

                    [bounds] => Array
                        (
                            [northeast] => Array
                                (
                                    [lat] => 51.7202301025
                                    [lng] => 0.336111992598
                                )

                            [southwest] => Array
                                (
                                    [lat] => 51.2786598206
                                    [lng] => -0.523222982883
                                )

                        )

                    [components] => Array
                        (
                            [country] => United Kingdom
                            [county] => Greater London
                            [state] => England
                            [town] => London
                        )

                    [confidence] => 1
                    [formatted] => London, Greater London, United Kingdom
                    [geometry] => Array
                        (
                            [lat] => 51.50853
                            [lng] => -0.12574
                        )

                )

        )
)
```

## Copyright

Copyright (c) OpenCage GmbH. See LICENSE for details.

## Who is OpenCage GmbH?

<a href="https://opencagedata.com"><img src="opencage_logo_300_150.png"></a>

We run a worldwide [geocoding API](https://opencagedata.com/api) and [geosearch](https://opencagedata.com/geosearch) service based on open data. 
Learn more [about us](https://opencagedata.com/about). 

We also run [Geomob](https://thegeomob.com), a series of regular meetups for location based service creators, where we do our best to highlight geoinnovation. If you like geo stuff, you will probably enjoy [the Geomob podcast](https://thegeomob.com/podcast/).

-- end --
