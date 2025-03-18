<?php

namespace App\Http\Controllers;

use App\Helper\Common;
use App\Helper\Files;

class ImageController extends Controller
{


    const FILE_PATH = 'quill-images';

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function store(\Illuminate\Http\Request $request)
    {

        $upload = Files::uploadLocalOrS3($request->image, self::FILE_PATH);
        $image = Common::encryptDecrypt($upload);

        return response()->json(route('image.getImage', $image));
    }

    public function getImage($imageEncrypted)
    {
        $imagePath = '';
        try {
            $decrypted = Common::encryptDecrypt($imageEncrypted, 'decrypt');
            $file_data = file_get_contents(asset_url_local_s3(self::FILE_PATH . '/' . $decrypted), false, stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]));

            $imagePath = \Image::make($file_data)->response();
        } catch (\Exception $e) {
            abort(404);
        }

        return $imagePath;
    }

    public function cropper($element)
    {
        $this->element = $element;

        return view('theme-settings.ajax.cropper', $this->data);
    }

}
