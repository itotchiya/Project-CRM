<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\UploadInstallRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Zip;

class UpdateAppController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.updates';
        $this->pageIcon = 'ti-reload';
        $this->activeSettingMenu = 'update_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!user()->hasRole('admin'));

            return $next($request);
        });
    }

    public function index()
    {
        try {
            $results = DB::select('select version()');
            $this->mysql_version = $results[0]->{'version()'};
            $this->databaseType = 'MySQL Version';

            if (str_contains($this->mysql_version, 'Maria')) {
                $this->databaseType = 'Maria Version';
            }
        } catch (\Exception $e) {
            $this->mysql_version = null;
            $this->databaseType = 'MySQL Version';
        }

        $this->reviewed = file_exists(storage_path('reviewed'));

        return view('update-settings.index', $this->data);
    }

    public function store(UploadInstallRequest $request)
    {

        config(['filesystems.default' => 'storage']);
        $path = storage_path('app') . '/Modules/' . $request->file->getClientOriginalName();

        if (file_exists($path)) {
            File::delete($path);
        }

        $request->file->storeAs('/', $request->file->getClientOriginalName());
    }

    public function deleteFile(Request $request)
    {
        $filePath = $request->filePath;
        File::delete($filePath);

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function install(Request $request)
    {
        File::put(public_path() . '/install-version.txt', 'complete');

        $filePath = $request->filePath;
        $zip = Zip::open($filePath);

        // extract whole archive
        $zip->extract(base_path());

        Artisan::call('optimize:clear');
        Session::flush();
    }

}
