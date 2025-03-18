<?php

namespace App\Http\Controllers;

use App\Helper\Common;

class FileController extends Controller
{

    public function getFile($type, $path)
    {
        abort_if(!in_array($type, ['file', 'image']), 404);

        try {
            $path = str($path)->replace('_masked.png', '')->__toString();
            $decrypted = Common::encryptDecrypt($path, 'decrypt');

            return response()->redirectTo(asset_url_local_s3($decrypted));
        } catch (\Exception $e) {
            abort(404);
        }

    }

}
