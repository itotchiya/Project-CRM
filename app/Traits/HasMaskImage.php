<?php

namespace App\Traits;

use App\Helper\Common;
use App\Models\StorageSetting;
use Exception;

trait HasMaskImage
{

    private function generateMaskedImageAppUrl($path): string
    {
        // Return local or s3 url for local storage and do not generate
        if (!in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {

            return asset_url_local_s3($path);
        }

        $filePath = Common::encryptDecrypt($path) . '_masked.png';
        try {
            return route('file.getFile', ['type' => 'image', 'path' => $filePath]);
        } catch (Exception $exception) {
            return config('app.url') . '/file/image/' . $filePath;
        }
    }

}
