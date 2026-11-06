<?php

namespace OpenCage\Geocoder\Laravel;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use OpenCage\Geocoder\Geocoder;

class GeocoderServiceProvider extends ServiceProvider
{
    private const CONFIG = __DIR__ . '/../../config/opencage.php';

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG, 'opencage');

        $this->app->bind(Geocoder::class, function (Container $app): Geocoder {
            $config = $app->make(Repository::class);

            $key = $config->get('opencage.key');
            $geocoder = new Geocoder(is_string($key) ? $key : null);

            $timeout = $config->get('opencage.timeout');
            if (is_numeric($timeout)) {
                $geocoder->setTimeout((int) $timeout);
            }

            $proxy = $config->get('opencage.proxy');
            if (is_string($proxy) && $proxy !== '') {
                $geocoder->setProxy($proxy);
            }

            return $geocoder;
        });

        $this->app->alias(Geocoder::class, 'opencage');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([self::CONFIG => $this->app->configPath('opencage.php')], 'opencage-config');
        }
    }
}
