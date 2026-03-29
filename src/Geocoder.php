<?php

namespace OpenCage\Geocoder;

use OpenCage\Geocoder\AbstractGeocoder;

class Geocoder extends AbstractGeocoder
{
    public function geocode($query, $optParams = [])
    {
        $url = $this->url . 'q=' . urlencode($query);

        if (is_array($optParams) && !empty($optParams)) {
            foreach ($optParams as $param => $paramValue) {
                $url .= '&' . urlencode($param) . '=' . urlencode($paramValue);
            }
        }

        if (empty($this->key)) {
            throw new \Exception('Missing API key');
        }
        $url .= '&key=' . urlencode($this->key);

        $ret = json_decode($this->getJSON($url), true);
        return $ret;
    }
}
