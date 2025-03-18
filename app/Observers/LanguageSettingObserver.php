<?php

namespace App\Observers;

use App\Models\LanguageSetting;

class LanguageSettingObserver
{

    public function saving(LanguageSetting $model)
    {
        cache()->forget('language_setting');
        cache()->forget('language_setting_' . $model->language_code);

        return $model;
    }

    public function updated(LanguageSetting $model)
    {
        cache()->forget('language_setting');
        cache()->forget('language_setting_' . $model->language_code);

        return $model;
    }

    public function deleted(LanguageSetting $model)
    {
        cache()->forget('language_setting');
        cache()->forget('language_setting_' . $model->language_code);

        return $model;
    }

}
