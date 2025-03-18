<?php

namespace App\Helper;

use App\Models\Company;
use App\Models\FileStorage;
use App\Models\StorageSetting;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Froiden\RestAPI\Exceptions\ApiException;
use Intervention\Image\ImageManagerStatic as Image;

class Files
{

    const UPLOAD_FOLDER = 'user-uploads';
    const IMPORT_FOLDER = 'import-files';

    const REQUIRED_FILE_UPLOAD_SIZE = 20;

    /**
     * @param mixed $image
     * @param string $dir
     * @param null $width
     * @param int $height
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    public static function upload($image, string $dir, $width = null, int $height = 800)
    {
        // To upload files to local server
        config(['filesystems.default' => 'local']);

        $uploadedFile = $image;
        $folder = $dir . '/';

        self::validateUploadedFile($uploadedFile);

        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());

        $tempPath = public_path(self::UPLOAD_FOLDER . '/temp/' . $newName);

        /** Check if folder exits or not. If not then create the folder */
        self::createDirectoryIfNotExist($folder);

        $newPath = $folder . '/' . $newName;

        $uploadedFile->storeAs('temp', $newName);

        if (($width && $height) && File::extension($uploadedFile->getClientOriginalName()) !== 'svg') {
            Image::make($tempPath)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save();
        }

        Storage::put($newPath, File::get($tempPath), ['public']);

        // Deleting temp file
        File::delete($tempPath);


        return $newName;
    }

    /**
     * @throws ApiException
     */
    public static function validateUploadedFile($uploadedFile)
    {
        if (!$uploadedFile->isValid()) {
            throw new ApiException('File was not uploaded correctly');
        }

        if ($uploadedFile->getClientOriginalExtension() === 'php' || $uploadedFile->getMimeType() === 'text/x-php') {
            throw new ApiException('You are not allowed to upload the php file on server', null, 422, 422, 2023);
        }

        if ($uploadedFile->getClientOriginalExtension() === 'sh' || $uploadedFile->getMimeType() === 'text/x-shellscript') {
            throw new ApiException('You are not allowed to upload the shell script file on server', null, 422, 422, 2023);
        }

        if ($uploadedFile->getClientOriginalExtension() === 'htaccess') {
            throw new ApiException('You are not allowed to upload the htaccess file on server', null, 422, 422, 2023);
        }

        if ($uploadedFile->getClientOriginalExtension() === 'xml') {
            throw new ApiException('You are not allowed to upload XML FILE', null, 422, 422, 2023);
        }

        if ($uploadedFile->getSize() <= 10) {
            throw new ApiException('You are not allowed to upload a file with filesize less than 10 bytes', null, 422, 422, 2023);
        }
    }

    public static function generateNewFileName($currentFileName)
    {
        $ext = strtolower(File::extension($currentFileName));
        $newName = md5(microtime());

        return ($ext === '') ? $newName : $newName . '.' . $ext;
    }

    /**
     * @throws \Exception
     */
    public static function uploadLocalOrS3($uploadedFile, $dir, $width = null, int $height = 400)
    {
        self::validateUploadedFile($uploadedFile);

        try {
            // If width and height is provided then upload image
            if (($width && $height)) {
                return self::uploadImage($uploadedFile, $dir, $width, $height);
            }

            // Add data to file_storage table
            $newName = self::fileStore($uploadedFile, $dir);

            $fileVisibility = [];

            if (config('filesystems.default') == 'local') {
                $fileVisibility = ['directory_visibility' => 'public', 'visibility' => 'public'];
            }

            // We have given 2 options of upload for now s3 and local
            Storage::disk(config('filesystems.default'))->putFileAs($dir, $uploadedFile, $newName, $fileVisibility);

            // Upload files to aws s3 or digitalocean or wasabi or minio
            Storage::disk(config('filesystems.default'))->missing($dir . '/' . $newName);

            return $newName;
        } catch (\Exception $e) {
            throw new \Exception(__('app.fileNotUploaded') . ' ' . $e->getMessage() . ' on ' . config('filesystems.default'));
        }
    }

    public static function fileStore($file, $folder, $generateNewName = '')
    {
        // Generate a new name if $generateNewName is empty
        $newName = $generateNewName ?: self::generateNewFileName($file->getClientOriginalName());

        // Retrieve enabled storage setting
        $setting = StorageSetting::where('status', 'enabled')->firstOrFail();
        $storageLocation = $setting->filesystem;

        // Store file information in the database
        $fileStorage = new FileStorage();
        $fileStorage->filename = $newName;
        $fileStorage->size = $file->getSize();
        $fileStorage->type = $file->getClientMimeType();
        $fileStorage->path = $folder;
        $fileStorage->storage_location = $storageLocation;
        $fileStorage->save();

        return $newName;
    }

    public static function deleteFile($filename, $folder)
    {
        $dir = trim($folder, '/');

        // Check and delete file record from database
        if ($fileExist = FileStorage::where('filename', $filename)->first()) {
            $fileExist->delete();
        }

        $filePath = $dir . '/' . $filename;
        $disk = Storage::disk(config('filesystems.default'));

        // Delete from Cloud
        if (in_array(config('filesystems.default'), StorageSetting::S3_COMPATIBLE_STORAGE)) {
            try {
                if ($disk->exists($filePath)) {
                    $disk->delete($filePath);
                }
            } catch (\Exception $e) {
                return true;
            }

            return true;
        }

        // Delete from Local
        $path = public_path(Files::UPLOAD_FOLDER . '/' . $filePath);
        if (!File::exists($path)) {
            return true;
        }

        try {
            File::delete($path);
        } catch (\Throwable) {
            return true;
        }

        return true;
    }


    public static function deleteDirectory($folder)
    {
        $dir = trim($folder);
        try {
            Storage::deleteDirectory($dir);
        } catch (\Exception $e) {
            return true;
        }


        return true;
    }

    public static function copy($from, $to)
    {
        Storage::disk(config('filesystems.default'))->copy($from, $to);
    }

    public static function createDirectoryIfNotExist($folder)
    {
        $directoryPath = public_path(self::UPLOAD_FOLDER . '/' . $folder);

        if (!File::exists($directoryPath)) {
            File::makeDirectory($directoryPath, 0775, true);
        }
    }

    public static function uploadImage($uploadedFile, string $folder, $width = null, int $height = 800)
    {
        $newName = self::generateNewFileName($uploadedFile->getClientOriginalName());

        $tempPath = public_path(self::UPLOAD_FOLDER . '/temp/' . $newName);

        /** Check if folder exits or not. If not then create the folder */
        self::createDirectoryIfNotExist($folder);

        $newPath = $folder . '/' . $newName;

        $uploadedFile->storeAs('temp', $newName, 'local');

        // Resizing image if width and height is provided
        $svgNot = File::extension($uploadedFile->getClientOriginalName()) !== 'svg';
        $webPNot = File::extension($uploadedFile->getClientOriginalName()) !== 'webp';

        if ($width && $height && $svgNot && $webPNot) {
            Image::make($tempPath)
                ->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->save();
        }

        Storage::disk(config('filesystems.default'))->put($newPath, File::get($tempPath));
        self::fileStore($uploadedFile, $folder, $newName);

        // Deleting temp file
        File::delete($tempPath);

        return $newName;
    }

    public static function uploadLocalFile($fileName, $path, $companyId = null): void
    {
        if (!File::exists(public_path(Files::UPLOAD_FOLDER . '/' . $path . '/' . $fileName))) {
            return;
        }

        self::saveFileInfo($fileName, $path, $companyId);
        self::storeLocalFileOnCloud($fileName, $path);
    }

    public static function saveFileInfo($fileName, $path, $companyId = null)
    {
        $filePath = public_path(Files::UPLOAD_FOLDER . '/' . $path . '/' . $fileName);

        $fileStorage = FileStorage::where('filename', $fileName)->first() ?: new FileStorage();
        $fileStorage->company_id = $companyId;
        $fileStorage->filename = $fileName;
        $fileStorage->size = File::size($filePath);
        $fileStorage->type = File::mimeType($filePath);
        $fileStorage->path = $path;
        $fileStorage->storage_location = config('filesystems.default');
        $fileStorage->save();
    }

    public static function storeLocalFileOnCloud($fileName, $path)
    {
        if (config('filesystems.default') != 'local') {
            $filePath = public_path(Files::UPLOAD_FOLDER . '/' . $path . '/' . $fileName);
            try {
                $contents = File::get($filePath);
                Storage::disk(config('filesystems.default'))->put($path . '/' . $fileName, $contents);
                // TODO: Delete local file in Next release
                // File::delete($filePath);
                return true;
            } catch (\Exception $e) {
                info($e->getMessage());
            }
        }

        return false;
    }

    /**
     * fixLocalUploadFiles is used to fix the local upload files
     *
     * Example of $model
     * $model = Company::class;
     *
     * Example of $columns
     * $columns = [
     *     [
     *        'name' => 'logo',
     *       'path' => 'company'
     *    ]
     * ];
     *
     * @param mixed $model
     * @param array $columns
     * @return void
     */
    public static function fixLocalUploadFiles($model, array $columns)
    {
        foreach ($columns as $column) {
            $name = $column['name'];
            $path = $column['path'];

            $filesData = $model::withoutGlobalScopes()->whereNotNull($name)->get();

            foreach ($filesData as $item) {
                /** @phpstan-ignore-next-line */
                $fileName = $item->{$name};
                /** @phpstan-ignore-next-line */
                $companyId = ($model == Company::class) ? $item->id : $item->company_id;

                $filePath = public_path(self::UPLOAD_FOLDER . '/' . $path . '/' . $fileName);

                if (!File::exists($filePath)) {
                    continue;
                }

                self::saveFileInfo($fileName, $path, $companyId);
                self::storeLocalFileOnCloud($fileName, $path);
            }
        }
    }

    public static function getFormattedSizeAndStatus($maxSizeKey)
    {
        try {
            // Retrieve the raw value from php.ini
            $maxSize = ini_get($maxSizeKey);

            // Convert the size to bytes
            $sizeInBytes = self::returnBytes($maxSize);

            // Format the size in either MB or GB
            if ($sizeInBytes >= 1 << 30) {
                return [
                    'size' => round($sizeInBytes / (1 << 30), 2) . ' GB',
                    'greater' => true
                ];
            }

            $mb = $sizeInBytes / 1048576;

            if ($sizeInBytes >= 1 << 20) {
                return [
                    'size' => round($sizeInBytes / (1 << 20), 2) . ' MB',
                    'greater' => $mb >= self::REQUIRED_FILE_UPLOAD_SIZE
                ];
            }

            if ($sizeInBytes >= 1 << 10) {
                return [
                    'size' => round($sizeInBytes / (1 << 10), 2) . ' KB',
                    'greater' => false
                ];
            }

            return [
                'size' => $sizeInBytes . ' Bytes',
                'greater' => false
            ];
        } catch (\Exception $e) {
            return [
                'size' => '0 Bytes',
                'greater' => true
            ];
        }
    }

    public static function getUploadMaxFilesize()
    {
        return self::getFormattedSizeAndStatus('upload_max_filesize');
    }

    public static function getPostMaxSize()
    {
        return self::getFormattedSizeAndStatus('post_max_size');
    }

    // Helper function to convert human-readable size to bytes
    public static function returnBytes($val)
    {
        $val = trim($val);
        $valNew = substr($val, 0, -1);
        $last = strtolower($val[strlen($val) - 1]);

        switch ($last) {
            case 'g':
                $valNew *= 1024;
            case 'm':
                $valNew *= 1024;
            case 'k':
                $valNew *= 1024;
        }

        return $valNew;
    }
}
