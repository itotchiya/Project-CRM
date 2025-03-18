<?php

namespace App\Traits;

use App\Models\CustomField;

trait CustomFieldsRequestTrait
{

    public function customFieldRules($rules = [])
    {
        $fields = request()->custom_fields_data;

        if ($fields) {

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);

                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes') {
                    $rules['custom_fields_data.' . $key] = 'required';

                    if ($customField->type == 'file' && request()->hasFile('custom_fields_data.' . $key)) {
                        $rules['custom_fields_data.' . $key] = 'required|file|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,text/plain,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,video/mp4,video/x-msvideo,video/x-flv,video/x-ms-wmv,video/3gpp,video/webm,audio/mpeg,application/zip,application/x-rar-compressed,application/x-7z-compressed,model/stl,application/sla,model/x.stl-ascii,model/x.stl-binary';
                    }
                }
            }
        }

        return $rules;
    }

    public function customFieldsAttributes($attributes = [])
    {
        $fields = request()->custom_fields_data;

        if ($fields) {

            foreach ($fields as $key => $value) {
                $idarray = explode('_', $key);
                $id = end($idarray);
                $customField = CustomField::findOrFail($id);

                if ($customField->required == 'yes') {
                    $attributes['custom_fields_data.' . $key] = str($customField->label);
                }
            }
        }

        return $attributes;
    }

}
