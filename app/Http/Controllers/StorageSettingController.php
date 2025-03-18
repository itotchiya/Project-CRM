<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\Storage\StoreRequest;
use App\Models\FileStorage;
use App\Models\StorageSetting;
use App\Helper\Files;
use App\Http\Requests\Settings\StorageAwsFileUpload;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class StorageSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.storageSettings';
        $this->activeSettingMenu = 'storage_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_storage_setting') == 'all'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->awsCredentials = StorageSetting::where('filesystem', 'aws_s3')->first();
        $this->digitalOceanCredentials = StorageSetting::where('filesystem', 'digitalocean')->first();
        $this->wasabiCredentials = StorageSetting::where('filesystem', 'wasabi')->first();
        $this->minioCredentials = StorageSetting::where('filesystem', 'minio')->first();
        $this->localCredentials = StorageSetting::where('filesystem', 'local')->first();

        if (!is_null($this->awsCredentials)) {
            $this->awsKeys = json_decode($this->awsCredentials->auth_keys);
        }

        if (!is_null($this->digitalOceanCredentials)) {
            $this->digitaloceanKeys = json_decode($this->digitalOceanCredentials->auth_keys);
        }

        if (!is_null($this->wasabiCredentials)) {
            $this->wasabiKeys = json_decode($this->wasabiCredentials->auth_keys);
        }

        if (!is_null($this->minioCredentials)) {
            $this->minioKeys = json_decode($this->minioCredentials->auth_keys);
        }

        $this->localFilesCount = FileStorage::where('storage_location', 'local')->count();

        return view('storage-settings.index', $this->data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreRequest $request)
    {

        StorageSetting::query()->update(['status' => 'disabled']);

        $storage = StorageSetting::firstorNew(['filesystem' => $request->storage]);

        switch ($request->storage) {
        case 'digitalocean':

            $arrayResponse = [
                'driver' => 's3',
                'key' => $request->digitalocean_key,
                'secret' => $request->digitalocean_secret,
                'region' => $request->digitalocean_region,
                'bucket' => $request->digitalocean_bucket,
            ];
            $storage->auth_keys = json_encode($arrayResponse);
            break;
        case 'wasabi':

            $arrayResponse = [
                'driver' => 's3',
                'key' => $request->wasabi_key,
                'secret' => $request->wasabi_secret,
                'region' => $request->wasabi_region,
                'bucket' => $request->wasabi_bucket,
            ];
            $storage->auth_keys = json_encode($arrayResponse);
            break;

        case 'aws_s3':
            $arrayResponse = [
                'driver' => 's3',
                'key' => $request->aws_key,
                'secret' => $request->aws_secret,
                'region' => $request->aws_region,
                'bucket' => $request->aws_bucket,
            ];
            $storage->auth_keys = json_encode($arrayResponse);
            break;

        case 'minio':
            $arrayResponse = [
                'driver' => 's3',
                'key' => $request->minio_key,
                'secret' => $request->minio_secret,
                'region' => $request->minio_region,
                'bucket' => $request->minio_bucket,
                'endpoint' => $request->minio_endpoint,
            ];
            $storage->auth_keys = json_encode($arrayResponse);
            break;
        }

        $storage->filesystem = $request->storage;
        $storage->status = 'enabled';
        $storage->save();

        cache()->forget('storage-setting');
        session()->forget('storage-setting');
        session(['storage_setting' => $storage]);

        return Reply::success(__('messages.updateSuccess'));
    }

    public function awsTestModal($type)
    {
        $this->type = $type;

        return view('storage-settings.test-s3-upload', compact('type'));
    }

    public function awsTest(StorageAwsFileUpload $request)
    {
        $file = $request->file('file');

        try {
            $filename = Files::uploadLocalOrS3($file, '/');
        } catch (\Exception $e) {
            return Reply::error($e->getMessage());
        }

        $fileUrl = asset_url_local_s3($filename);

        return Reply::successWithData(__('messages.fileUploaded'), ['fileurl' => $fileUrl]);
    }

    public function awsLocalToAwsModal()
    {
        config(['filesystems.default' => 'local']);
        $this->files = FileStorage::where('storage_location', 'local')->orderBy('storage_location')->get();
        $this->localFilesCount = FileStorage::where('storage_location', 'local')->count();

        return view('storage-settings.local-to-aws', $this->data);
    }

    /**
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     * @throws FileNotFoundException
     */
    public function moveFilesLocalToAwsS3()
    {
        $files = FileStorage::where('storage_location', 'local')->get();

        foreach ($files as $file) {
            $filePath = public_path(Files::UPLOAD_FOLDER . '/' . $file->path . '/' . $file->filename);

            if (!File::exists($filePath)) {
                $file->delete();
                continue;
            }

            $contents = File::get($filePath);
            $uploaded = Storage::disk(config('filesystems.default'))->put($file->path . '/' . $file->filename, $contents);

            if ($uploaded) {
                $file->storage_location = config('filesystems.default') === 's3' ? 'aws_s3' : config('filesystems.default');
                $file->save();
                $this->deleteFileFromLocal($filePath);
            }
        }

        return Reply::successWithData(__('messages.filesMoveToCloudSuccessfully'), ['fileurl' => 'done']);
    }

    private function deleteFileFromLocal($filePath)
    {
        if (File::exists($filePath)) {
            try {
                unlink($filePath);
            } catch (\Throwable $th) {
                return true;
            }
        }
    }

}
