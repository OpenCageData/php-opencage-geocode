<?php

namespace OpenCage;

require __DIR__.'/AbstractGeocoder.php';
// use OpenCage\AbstractGeocoder;

class Geocoder extends AbstractGeocoder
{
    public function geocode($query)
    {
        $url = self::URL . 'q=' . urlencode($query);
        if (empty($this->key)) {
            throw new \Exception('Missing API key');
        }
        $url .= '&key=' . $this->key;

        $ret = json_decode($this->getJSON($url), true);
        return $ret;
    }
}
