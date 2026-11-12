<?php

namespace OpenCage\Geocoder\Exception;

/**
 * The request failed for a reason other than a network error.
 * Network errors and timeouts are returned as status code 498 instead.
 */
class RequestFailedException extends \RuntimeException
{
}
