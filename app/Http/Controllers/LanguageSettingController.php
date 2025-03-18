<?php

namespace App\Http\Controllers;

use App\Models\Flag;
use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\LanguageSetting;
use App\Models\TranslateSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use App\Http\Requests\Admin\Language\StoreRequest;
use App\Http\Requests\Admin\Language\UpdateRequest;
use Barryvdh\TranslationManager\Models\Translation;
use App\Http\Requests\Admin\Language\AutoTranslateRequest;

class LanguageSettingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.languageSettings';
        $this->activeSettingMenu = 'language_settings';
        $this->langPath = base_path() . '/resources/lang';
        $this->middleware(function ($request, $next) {
            abort_403(!(user()->permission('manage_language_setting') == 'all'));
            return $next($request);
        });
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $this->languages = LanguageSetting::all();
        return view('language-settings.index', $this->data);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    // phpcs:ignore
    public function update(Request $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);

        if ($request->has('status')) {
            $setting->status = $request->status;
        }

        $setting->save();


        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array
     */
    // phpcs:ignore
    public function updateData(UpdateRequest $request, $id)
    {
        $setting = LanguageSetting::findOrFail($request->id);

        $oldLangExists = File::exists($this->langPath.'/'.$setting->language_code);

        if($oldLangExists){
            // check and create lang folder
            $langExists = File::exists($this->langPath . '/' . $request->language_code);

            if (!$langExists) {
                // update lang folder name
                File::move($this->langPath . '/' . $setting->language_code, $this->langPath . '/' . $request->language_code);

                Translation::where('locale', $setting->language_code)->get()->map(function ($translation) {
                    $translation->delete();
                });
            }
        }

        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->flag_code = strtolower($request->flag);
        $setting->status = $request->status;
        $setting->is_rtl = $request->is_rtl;
        $setting->save();


        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreRequest $request)
    {
        // check and create lang folder
        $langExists = File::exists($this->langPath . '/' . $request->language_code);

        if (!$langExists) {
            File::makeDirectory($this->langPath . '/' . $request->language_code);
        }

        $setting = new LanguageSetting();
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->flag_code = $request->flag;
        $setting->status = $request->status;
        $setting->is_rtl = $request->is_rtl;
        $setting->save();

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $this->flags = Flag::get();

        return view('language-settings.create-language-settings-modal', $this->data);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function autoTranslate(Request $request)
    {
        $this->translateSetting = TranslateSetting::first();
        return view('language-settings.auto-translate-modal', $this->data);
    }

    public function autoTranslateUpdate(AutoTranslateRequest $request)
    {
        $translateSetting = TranslateSetting::first();
        $translateSetting->update($request->validated());

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Request $request, $id)
    {
        $this->languageSetting = LanguageSetting::findOrFail($id);
        $this->flags = Flag::get();

        return view('language-settings.edit-language-settings-modal', $this->data);
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $language = LanguageSetting::findOrFail($id);
        $setting = company();

        if ($language->language_code == $setting->locale) {
            $setting->locale = 'en';
            $setting->last_updated_by = $this->user->id;
            $setting->save();
            session()->forget('user');
        }

        $language->destroy($id);

        $langExists = File::exists($this->langPath . '/' . $language->language_code);

        if ($langExists) {
            File::deleteDirectory($this->langPath . '/' . $language->language_code);
        }

        if (Schema::hasTable('ltm_translations')) {
            DB::statement('DELETE FROM ltm_translations where locale = "'.$language->language_code.'"');
        }

        return Reply::success(__('messages.deleteSuccess'));
    }

    public function fixTranslation()
    {
        Artisan::call('translations:reset');
        Artisan::call('translations:import');
        return Reply::success(__('modules.languageSettings.fixTranslationSuccess'));
    }

    public function createEnLocale()
    {
        // copy eng folder from resources/lang to resources/lang/en
        File::copyDirectory($this->langPath . '/eng', $this->langPath . '/en');

        // copy eng.json file from resources/lang to resources/lang/en.json
        File::copy($this->langPath . '/eng.json', $this->langPath . '/en.json');

        return Reply::success(__('messages.recordSaved'));
    }

}
