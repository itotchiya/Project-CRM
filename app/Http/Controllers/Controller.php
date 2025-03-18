<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Carbon\Carbon;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\App;

class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, AppBoot;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @param mixed $name
     * @param mixed $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param mixed $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __construct()
    {

        $this->middleware(function ($request, $next) {

            $this->checkMigrateStatus();


            // To keep the session we need to move it to middleware
            $this->gdpr = gdpr_setting();
            $this->company = company();

            $this->global = global_setting();

            $this->socialAuthSettings = social_auth_setting();

            $this->companyName = company() ? $this->company->company_name : $this->global->global_app_name;

            $this->appName = company() ? $this->company->app_name : $this->global->global_app_name;
            $this->locale = session('locale') ? session('locale') : (company() ? $this->company->locale : $this->global->locale);

            $this->taskBoardColumnLength = $this->company ? $this->company->taskboard_length : 10;

            config(['app.name' => $this->companyName]);
            config(['app.url' => url('/')]);

            App::setLocale($this->locale);
            Carbon::setLocale($this->locale);

            setlocale(LC_TIME, $this->locale . '_' . mb_strtoupper($this->locale));

            if (config('app.env') == 'codecanyon') {
                config(['app.debug' => $this->global->app_debug]);
            }

            if (user()) {
                config(['froiden_envato.allow_users_id' => true]);
            }

            return $next($request);
        });
    }

    public function checkMigrateStatus()
    {
        return check_migrate_status();
    }

    public function returnAjax($view)
    {
        $html = view($view, $this->data)->render();

        return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
    }

}
