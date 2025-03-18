<?php

namespace App\Traits;

use Carbon\Carbon;
use App\Helper\Files;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\CustomFieldGroup;
use Illuminate\Support\Facades\DB;
use ReflectionClass;

trait CustomFieldsTrait
{

    public $model;
    private $extraData;
    public $custom_fields;
    public $custom_fields_data;

    /** Get company ID for current object
     * @return int Returns current object's company id
     */

    private function getModelName()
    {
        $model = new ReflectionClass($this);
        $this->model = $model;

        return $this->model->getName();
    }

    public function updateCustomField($group)
    {

        // Add Custom Fields for this group
        foreach ($group['fields'] as $field) {
            $insertData = [
                'custom_field_group_id' => 1,
                'label' => $field['label'],
                'name' => $field['name'],
                'type' => $field['type']
            ];

            if (isset($field['required']) && (in_array(strtolower($field['required']), ['yes', 'on', 1]))) {
                $insertData['required'] = 'yes';
            }
            else {
                $insertData['required'] = 'no';
            }

            // Single value should be stored as text (multi value JSON encoded)
            if (isset($field['value'])) {
                if (is_array($field['value'])) {
                    $insertData['values'] = json_encode($field['value']);

                }
                else {
                    $insertData['values'] = $field['value'];
                }
            }

            DB::table('custom_fields')->insert($insertData);
        }
    }

    public function getCustomFieldGroups($fields = false)
    {
        $customFieldGroup = CustomFieldGroup::where('model', $this->getModelName());

        $customFieldGroup = $customFieldGroup->when(method_exists($this, 'company'), function ($query) {
            return $query->where('company_id', $this->company_id ?: company()->id);
        })->first();

        if ($fields && $customFieldGroup) {
            $customFieldGroup->load(['customField'])->append(['fields']);
        }

        return $customFieldGroup;
    }

    public function getCustomFieldGroupsWithFields()
    {
        return $this->getCustomFieldGroups(true);
    }

    public function getCustomFieldsData()
    {

        $modelId = $this->id;

        // Get custom fields for this modal
        /** @var \Illuminate\Database\Eloquent\Collection $data */
        $data = DB::table('custom_fields_data')
            ->rightJoin('custom_fields', function ($query) use ($modelId) {
                $query->on('custom_fields_data.custom_field_id', '=', 'custom_fields.id');
                $query->on('model_id', '=', DB::raw($modelId));
            })
            ->rightJoin('custom_field_groups', 'custom_fields.custom_field_group_id', '=', 'custom_field_groups.id')
            ->select('custom_fields.id', DB::raw('CONCAT("field_", custom_fields.id) as field_id'), 'custom_fields.type', 'custom_fields_data.value')
            ->where('custom_field_groups.model', $this->getModelName())
            ->get();

        $data = collect($data);

        // Convert collection to an associative array
        // of format ['field_{id}' => $value]
        $result = $data->pluck('value', 'field_id');

        return $result;
    }

    public function updateCustomFieldData($fields, $company_id = null)
    {
        foreach ($fields as $key => $value) {

            $idarray = explode('_', $key);
            $id = end($idarray);

            $fieldType = CustomField::findOrFail($id)->type;
            $company = $company_id ? Company::findOrFail($company_id) : company();

            $value = ($fieldType == 'date') ? Carbon::createFromFormat($company->date_format, $value)->format('Y-m-d') : $value;
            $value = ($fieldType == 'file' && !is_string($value) && !is_null($value)) ? Files::uploadLocalOrS3($value, 'custom_fields') : $value;

            // Find is entry exists
            $entry = DB::table('custom_fields_data')
                ->where('model', $this->getModelName())
                ->where('model_id', $this->id)
                ->where('custom_field_id', $id)
                ->first();

            if ($entry) {
                if ($fieldType == 'file' && (!is_null($entry->value) && $entry->value != $value)) {
                    Files::deleteFile($entry->value, 'custom_fields');
                }

                // Update entry
                DB::table('custom_fields_data')
                    ->where('model', $this->getModelName())
                    ->where('model_id', $this->id)
                    ->where('custom_field_id', $id)
                    ->update(['value' => $value]);
            }
            else {
                DB::table('custom_fields_data')
                    ->insert([
                        'model' => $this->getModelName(),
                        'model_id' => $this->id,
                        'custom_field_id' => $id,
                        'value' => (!is_null($value)) ? $value : ''
                    ]);
            }
        }
    }

    public function getExtrasAttribute()
    {
        if ($this->extraData == null) {
            $this->extraData = $this->getCustomFieldGroupsWithFields();
        }

        return $this->extraData;
    }

    public function withCustomFields()
    {
        $this->custom_fields = $this->getCustomFieldGroupsWithFields();
        $this->custom_fields_data = $this->getCustomFieldsData();

        return $this;
    }

}
