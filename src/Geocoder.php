<?php

namespace OpenCage\Geocoder;

use OpenCage\Geocoder\AbstractGeocoder;

class Geocoder extends AbstractGeocoder
{
    public function geocode($query, $optParams = [])
    {
        $base_url = self::URL;
        if (is_array($optParams) && !empty($optParams)) {
            if (array_key_exists('host', $optParams)) {
                $base_url = str_replace('api.opencagedata.com', $optParams['host'], $base_url);
                unset($optParams['host']);
            }
            if (array_key_exists('proxy', $optParams)) {
                $base_url = str_replace('api.opencagedata.com', $optParams['host'], $base_url);
                unset($optParams['proxy']);
            }
        }

        $url = $base_url . 'q=' . urlencode($query);

        if (is_array($optParams) && !empty($optParams)) {
            foreach ($optParams as $param => $paramValue) {
                $url .= '&' . $param . '=' . urlencode($paramValue);
            }
        }

        if (empty($this->key)) {
            throw new \Exception('Missing API key');
        }
        $url .= '&key=' . $this->key;

        $ret = json_decode($this->getJSON($url), true);
        return $ret;
    }
}
