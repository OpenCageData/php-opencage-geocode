# OpenCage Data Geocoding Library for PHP

A [PHP](http://php.net/) library that uses [OpenCage Data's](http://www.opencagedata.com/)
geocoder.

## Dependencies and Requirements

* PHP 5
* CURL extension or ...
* fopen wrappers

The OpenCage Data Geocoding Library for PHP attempts to use the [CURL](http://www.php.net/manual/en/book.curl.php)
extension to access the OpenCage Data Geocoder. If CURL support is not available, the library
falls back to using [fopen wrappers](http://uk3.php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen).

To use the library you must either have the CURL extension compiled into your version of PHP
or configure the use of fopen wrappers via the `allow_url_fopen` directive in your `php.ini`.

## Usage

Load the library:

```PHP
require_once('OpenCage.Geocoder.php');
```

Create an instance of the geocoder library, passing a valid OpenCage Data Geocoder API key
as a parameter to the geocoder library's constructor:

```PHP
$key = 'your-api-key-here';
$geocoder = new OpenCage\Geocoder($key);
```

Pass a string containing the query or address to be geocoded to the library's `geocoder` method:

```PHP
$query = "82 Clerkenwell Road, London";
$result = $geocoder->geocode($query);
```

Putting all of this together, a complete sample would look like this:

```PHP
<?php

require_once('OpenCage.Geocoder.php');

$query = "82 Clerkenwell Road, London";
$key = 'your-api-key-here';
$geocoder = new OpenCage\Geocoder($key);
$result = $geocoder->geocode($query);
print_r($result);

?>
```

Running the above code sample will produce an output similar to this:

```PHP
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
                    [formatted] => 82, Clerkenwell Road, Clerkenwell, London Borough of Islington, London, EC1M 5RF, Greater London, England, United Kingdom, gb, European Union
                    [components] => Array
                        (
                            [country] => United Kingdom
                            [state_district] => Greater London
                            [country_code] => gb
                            [state] => England
                            [house_number] => 82
                            [suburb] => Clerkenwell
                            [city] => London Borough of Islington
                            [continent] => European Union
                            [county] => London
                            [road] => Clerkenwell Road
                            [postcode] => EC1M 5RF
                        )
                    [geometry] => Array
                        (
                            [lat] => 51.5226746412658
                            [lng] => -0.102485925377665
                        )
                    [bounds] => Array
                        (
                            [southwest] => Array
                                (
                                    [lat] => 51.5226042
                                    [lng] => -0.1025908
                                )
                            [northeast] => Array
                                (
                                    [lat] => 51.5227563
                                    [lng] => -0.1023802
                                )
                        )
                    [annotations] => Array
                        (
                        )
                )
            [1] => Array
                (
                    [formatted] => 82, Lokku Ltd, Clerkenwell Road, Clerkenwell, London Borough of Islington, London, EC1M 5RF, Greater London, England, United Kingdom, gb, European Union
                    [components] => Array
                        (
                            [country] => United Kingdom
                            [state_district] => Greater London
                            [house] => Lokku Ltd
                            [country_code] => gb
                            [state] => England
                            [house_number] => 82
                            [suburb] => Clerkenwell
                            [city] => London Borough of Islington
                            [continent] => European Union
                            [county] => London
                            [postcode] => EC1M 5RF
                            [road] => Clerkenwell Road
                        )
                    [geometry] => Array
                        (
                            [lat] => 51.5226295
                            [lng] => -0.1024389
                        )
                    [bounds] => Array
                        (
                            [southwest] => Array
                                (
                                    [lat] => 51.5225795
                                    [lng] => -0.1024889
                                )

                            [northeast] => Array
                                (
                                    [lat] => 51.5226795
                                    [lng] => -0.1023889
                                )
                        )
                    [annotations] => Array
                        (
                        )
                )
        )
)
```
