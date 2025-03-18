<?php

use App\Models\GlobalSetting;
use App\Models\PaymentGatewayCredentials;
use App\Models\SmtpSetting;
use App\Models\SocialAuthSetting;
use App\Models\StorageSetting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {


        // SOCIAL AUTH SETTING
        $this->changeToTextAndEncrypt(new SmtpSetting());

        // SOCIAL AUTH SETTING
        $this->changeToTextAndEncrypt(new SocialAuthSetting());

        // Payment Gateway Setting
        $this->changeToTextAndEncrypt(new PaymentGatewayCredentials());

        // Payment Gateway Setting
        $this->changeToTextAndEncrypt(new GlobalSetting());


        // STORAGE
        $storages = StorageSetting::all();

        foreach ($storages as $storage) {
            $this->saveEncrypt($storage, ['auth_keys']);
        }

        $globalSetting = GlobalSetting::first();

        if ($globalSetting) {
            DB::table($globalSetting->getTable())->where('google_map_key', '')->update(['google_map_key' => null]);
        }

    }

    private function changeToTextAndEncrypt($model)
    {

        $columns = $this->getColumns($model);

        Schema::table($model->getTable(), function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                $table->text($column)->nullable()->change();
            }
        });

        $dataObject = $model->all();

        foreach ($dataObject as $data) {
            $this->saveEncrypt($data, $columns);
        }
    }

    private function getColumns($model): array
    {
        $casts = $model->getCasts();

        $encryptedFields = array_keys(array_filter($casts, function ($value) {
            return $value === 'encrypted';
        }));

        return $encryptedFields;

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }

    private function saveEncrypt($model, $columns)
    {

        if (!$model) {
            return true;
        }

        $fieldsToUpdate = [];

        foreach ($columns as $fieldItem) {

            $rawValue = $model->getRawOriginal($fieldItem);

            if (!is_null($rawValue) && $rawValue !== '') {
                $fieldsToUpdate[$fieldItem] = $rawValue;
            }

        }

        try {
            Crypt::decryptString(head($fieldsToUpdate));

        } catch (DecryptException $e) {
            $encryptedValues = [];

            if (count($fieldsToUpdate) == 0) {
                return true;
            }

            foreach ($fieldsToUpdate as $fieldName => $fieldValue) {
                $encryptedValues[$fieldName] = Crypt::encryptString($fieldValue);
            }

            DB::table($model->getTable())->where('id', $model->id)->update($encryptedValues);
        }
    }

};
