# OpenCage Geocoding API Library for PHP

A [PHP](http://php.net/) library to use the [OpenCage geocoding API](https://opencagedata.com).

## Build Status / Code Quality

[![Build Status](https://travis-ci.org/OpenCageData/php-opencage-geocode.svg?branch=master)](https://travis-ci.org/OpenCageData/php-opencage-geocode)
[![Kritika Analysis Status](https://kritika.io/users/freyfogle/repos/7252234193599842/heads/master/status.svg)](https://kritika.io/users/freyfogle/repos/7252234193599842/heads/master/)
[![PHP version](https://badge.fury.io/ph/opencage%2Fgeocode.svg)](https://badge.fury.io/ph/opencage%2Fgeocode)

## Overview
This library attempts to use the [CURL](http://www.php.net/manual/en/book.curl.php)
extension to access the OpenCage Geocoding API. If CURL support is not available, the
library falls back to using [fopen wrappers](http://uk3.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen).

PHP 7 and 8 are supported.

To use the library you must either have the CURL extension compiled into your version
of PHP or configure the use of fopen wrappers via the `allow_url_fopen` directive in
your `php.ini`.

### Authentication

You need an API key, which you can sign up for [here](https://opencagedata.com).

## Installation

### With Composer

The recommended - and easiest way - to install is via [Composer](https://getcomposer.org/).
Require the library in your project's composer.json file.

```
$ composer require opencage/geocode
```

Import the Geocoder class.

```
require "vendor/autoload.php";
```

Start geocoding

```php
$geocoder = new \OpenCage\Geocoder\Geocoder('YOUR-API-KEY');
$result = $geocoder->geocode('82 Clerkenwell Road, London');
print_r($result);
```

### The old fashioned way

See the file `demo/geocode.php`


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

We run the [OpenCage Geocoder](https://opencagedata.com). Learn more [about us](https://opencagedata.com/about). 

We also run [Geomob](https://thegeomob.com), a series of regular meetups for location based service creators, where we do our best to highlight geoinnovation. If you like geo stuff, you will probably enjoy [the Geomob podcast](https://thegeomob.com/podcast/).


-- end --
