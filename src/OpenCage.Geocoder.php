<?php

namespace OpenCage {

    include_once 'OpenCage.AbstractGeocoder.php';

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
}
