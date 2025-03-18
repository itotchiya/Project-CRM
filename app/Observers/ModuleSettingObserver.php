<?php

namespace App\Observers;

use App\Models\ModuleSetting;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;

class ModuleSettingObserver
{

    //phpcs:ignore
    public function updated(ModuleSetting $model)
    {

        User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])->get()
            ->each(function ($user) {
                cache()->forget('user_modules_' . $user->id);
            });
    }

}
