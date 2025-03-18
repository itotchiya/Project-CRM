<?php

namespace App\Traits;

use DeviceDetector\Parser\Client\Browser;
use Exception;

trait StoreHeaders
{

    public function storeHeaders($model): void
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        try {
            if (class_exists(Browser::class)) {

                $model->headers = json_encode(\Browser::detect()->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);

                if (!in_array(request()->ip(), $whitelist)) {
                    $model->register_ip = request()->ip();

                    if (file_exists(database_path('maxmind/GeoLite2-City.mmdb'))) {
                        if ($position = \Stevebauman\Location\Facades\Location::get(request()->ip())) {
                            $model->location_details = json_encode($position, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
                        }
                    }

                }
            }
        } catch (Exception $e) {
//            echo $e->getMessage();
        }
    }

}
