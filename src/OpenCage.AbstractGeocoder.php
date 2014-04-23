<?php

namespace OpenCage {
	abstract class AbstractGeocoder {
		const TIMEOUT = 10;
		const URL = 'http://prototype.opencagedata.com/geocode/v1/json/?';

		protected $key;
		protected $timeout;
		protected $url;

		public function __construct($key=NULL) {
			if (isset($key) && !empty($key)) {
				$this->setKey($key);
			}
			$this->setTimeout(self::TIMEOUT);
		}

		public function setKey($key) {
			$this->key = $key;
		}

		public function setTimeout($timeout) {
			$this->timeout = $timeout;
		}

		protected function getJSON($query) {
			if (function_exists('curl_version')) {
				$ret = $this->getJSONByCurl($query);
				return $ret;
			}

			else if (ini_get('allow_url_fopen')) {
				$ret = $this->getJSONByFopen($query);
				return $ret;
			}

			else {
				throw new \Exception('PHP is not compiled with CURL support and allow_url_fopen is disabled; giving up');
			}
		}

		protected function getJSONByFopen($query) {
			$context = stream_context_create(array(
				'http' => array(
					'timeout' => $this->timeout
				)
			));

			$ret =  file_get_contents($query);
			return $ret;
		}

		protected function getJSONByCurl($query) {
			$ch = curl_init();
			$options = array(
				CURLOPT_TIMEOUT => $this->timeout,
				CURLOPT_URL => $query,
				CURLOPT_RETURNTRANSFER => 1
			);
			curl_setopt_array($ch, $options);

			$ret = curl_exec($ch);
			return $ret;
		}

		abstract public function geocode($query);
	}
}

?>
