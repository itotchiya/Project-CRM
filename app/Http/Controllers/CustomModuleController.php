<?php

namespace App\Http\Controllers;

use App\Events\ModuleStatusChanged;
use App\Helper\Reply;
use App\Models\ModuleSetting;
use Froiden\Envato\Functions\EnvatoUpdate;
use Froiden\Envato\Traits\ModuleVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Macellan\Zip\Zip;
use Nwidart\Modules\Facades\Module;

class CustomModuleController extends AccountBaseController
{

    use ModuleVerify;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.moduleSettings';
        $this->activeSettingMenu = 'module_settings';
        $this->middleware(function ($request, $next) {
            abort_403(!user()->hasRole('admin'));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $this->type = 'custom';
        $this->updateFilePath = config('froiden_envato.tmp_path');
        /** @phpstan-ignore-next-line */
        $this->allModules = Module::toCollection()->filter(function ($module, $key) {
            return $key !== 'UniversalBundle';
        });

        /** @phpstan-ignore-next-line */
        $this->universalBundle = Module::find('UniversalBundle');

        $this->view = 'custom-modules.ajax.custom';
        $this->activeTab = 'custom';
        $this->plugins = collect(EnvatoUpdate::plugins());

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle, 'activeTab' => $this->activeTab]);
        }

        return view('module-settings.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->pageTitle = 'app.menu.moduleSettingsInstall';
        $this->type = 'custom';
        $this->updateFilePath = config('froiden_envato.tmp_path');

        return view('custom-modules.install', $this->data);
    }

    /**
     * @param Request $request
     * @return array
     * @throws \Exception
     */
    public function store(Request $request)
    {
        if (!extension_loaded('zip')) {
            return Reply::error('<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.');
        }

        File::put(public_path() . '/install-version.txt', 'complete');

        $filePath = $request->filePath;

        $zip = Zip::open($filePath);

        $zipName = $this->getZipName($filePath);

        // Extract the files to storage folder first for checking the right plugin
        // Filename Like codecanyon-0gOuGKoY-zoom-meeting-module-for-worksuite.zip
        if (str_contains($zipName, 'codecanyon-')) {
            $zipName = $this->unzipCodecanyon($zip);
        }
        else {
            $zip->extract(storage_path('app') . '/Modules');
        }

        $moduleName = str_replace('.zip', '', $zipName);


        $validateModule = $this->validateModule($moduleName);

        if ($validateModule ['status'] == true) {
            // Move files to Modules if modules belongs to this product
            File::moveDirectory(storage_path('app') . '/Modules/' . $moduleName, base_path() . '/Modules/' . $moduleName, true);

            cache()->forget('laravel-modules');

            // Delete Modules Directory after moving files
            File::deleteDirectory(storage_path('app') . '/Modules/');

            $this->updateVersion($moduleName);

            // if module is universal bundle module then activate the module
            if ($moduleName == 'UniversalBundle') {
                /** @phpstan-ignore-next-line */
                $module = Module::findOrFail($moduleName);
                $module->enable();
                Artisan::call('module:migrate', array($moduleName, '--force' => true));
                event(new ModuleStatusChanged($module, 'active'));
            }

            $this->flushData();

            return Reply::success('Installed successfully.');
        }

        return Reply::error($validateModule ['message']);
    }

    public function validateModule($moduleName)
    {
        $appName = str_replace('-new', '', config('froiden_envato.envato_product_name'));
        $wrongMessage = 'The zip that you are trying to install is not compatible with ' . $appName . ' version';

        // Check if PHP-ZIP extension is missing
        if (!extension_loaded('zip')) {
            return [
                'status' => false,
                'message' => '<b>PHP-ZIP</b> extension is missing on your server. Please install the extension.'
            ];
        }

        $configPath = storage_path('app') . '/Modules/' . $moduleName . '/Config/config.php';

        // Check if module configuration file exists
        if (!file_exists($configPath)) {
            return [
                'status' => false,
                'message' => $wrongMessage
            ];
        }

        $config = require_once $configPath;

        // Check if parent_envato_id is defined and matches the application's envato_id
        if (!isset($config['parent_envato_id']) || $config['parent_envato_id'] !== config('froiden_envato.envato_item_id')) {
            return [
                'status' => false,
                'message' => 'You are installing the wrong module for this product'
            ];
        }

        // Parent envato id is different from module envato id
        if ($config['parent_envato_id'] !== config('froiden_envato.envato_item_id')) {
            return [
                'status' => false,
                'message' => 'You are installing wrong module for this product'
            ];
        }

        // Check if parent_min_version is defined
        if (!isset($config['parent_min_version'])) {
            $errorMessage = App::environment('codecanyon') ? 'Please download and install the latest version of the module.' : 'Minimum version of <b>' . $appName . ' main application</b> is not defined in the Module.';

            return [
                'status' => false,
                'message' => $errorMessage
            ];
        }

        // Check if the application version is lower than the required minimum version
        if ($config['parent_min_version'] >= File::get('version.txt')) {
            return [
                'status' => false,
                'message' => 'Minimum version of <b>' . $appName . ' main application</b> should be greater than or equal to <b>' . $config['parent_min_version'] . '</b>. Your application version is <b>' . File::get('version.txt') . '</b>'
            ];
        }


        // Check if parent_product_name is defined and matches the application's product name
        if (!isset($config['parent_product_name']) || $config['parent_product_name'] !== config('froiden_envato.envato_product_name')) {
            return [
                'status' => false,
                'message' => $wrongMessage
            ];
        }

        return [
            'status' => true,
            'message' => 'Unzipped successfully'
        ];


    }

    private function flushData()
    {
        Artisan::call('optimize:clear');
        Artisan::call('view:clear');
        $user = auth()->id();
        // clear cache
        cache()->flush();
        // clear session
        session()->flush();
        auth()->logout();
        // login user
        auth()->loginUsingId($user);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        return $this->verifyModulePurchase($id);
    }

    public function update(Request $request, $moduleName)
    {
        /** @phpstan-ignore-next-line */
        $module = Module::findOrFail($moduleName);

        $status = $request->status;

        ModuleSetting::where('module_name', $moduleName)->delete();

        ($status == 'active') ? $module->enable() : $module->disable();

        event(new ModuleStatusChanged($moduleName, $status));

        // We are registering the module to run the commands
        $module->register();


        if ($status == 'active') {
            $this->runModuleMigrateCommand($moduleName);

            // We will call the module function php artisan asset:activate, zoom:active , etc
            $this->runActivateCommand(strtolower($moduleName));

        }

        $this->flushData();

        if (strtolower($moduleName) == 'languagepack' && $status == 'active') {
            session(['languagepack_module_activated' => true]);
        }

        return Reply::redirect(route('custom-modules.index') . '?tab=custom', 'Status Changed. Reloading');
    }

    public function verifyingModulePurchase(Request $request)
    {
        $request->validate([
            'purchase_code' => 'required|max:80',
        ]);

        $module = $request->module;
        $purchaseCode = $request->purchase_code;

        return $this->modulePurchaseVerified($module, $purchaseCode);
    }

    /**
     * @throws \Exception
     */
    private function unzipCodecanyon($zip)
    {
        $codeCanyonPath = storage_path('app') . '/Modules/Codecanyon';
        $zip->extract($codeCanyonPath);
        $files = File::allfiles($codeCanyonPath);

        foreach ($files as $file) {

            if (str_contains($file->getRelativePathname(), '.zip')) {
                $filePath = $file->getRelativePathname();
                $zip = Zip::open($codeCanyonPath . '/' . $filePath);
                $zip->extract(storage_path('app') . '/Modules');

                return $this->getZipName($filePath);
            }
        }

        return false;
    }

    private function getZipName($filePath)
    {
        $array = explode('/', str_replace('\\', '/', $filePath));

        return end($array);
    }

    /**
     * @param $moduleName
     * This will update the version of on server
     */
    private function updateVersion($moduleName)
    {
        try {
            $config = require base_path() . '/Modules/' . $moduleName . '/Config/config.php';
            $setting = (new $config['setting'])::first();

            // When module migrations are not run

            if ($setting?->purchase_code) {
                $this->modulePurchaseVerified(strtolower($moduleName), $setting->purchase_code);
            }

        } catch (\Exception $e) {
            logger($e->getMessage());
        }
    }

    private function runModuleMigrateCommand($moduleName)
    {
        Artisan::call('module:migrate', [$moduleName, '--force' => true]);
    }

    private function runActivateCommand($moduleName)
    {
        $command = $moduleName . ':activate';

        $artisanCommands = \Artisan::all();

        if (array_has($artisanCommands, $command)) {
            Artisan::call($command);
        }
    }

}
