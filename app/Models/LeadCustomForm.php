<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\LeadCustomForm
 *
 * @property int $id
 * @property string $field_display_name
 * @property string $field_name
 * @property int $field_order
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $added_by
 * @property int|null $last_updated_by
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm query()
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereAddedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereFieldOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereLastUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereUpdatedAt($value)
 * @property int $required
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereRequired($value)
 * @property int|null $company_id
 * @property int|null $custom_fields_id
 * @property-read \App\Models\Company|null $company
 * @property-read \App\Models\CustomField|null $customField
 * @property-read mixed $extras
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LeadCustomForm whereCustomFieldsId($value)
 * @mixin \Eloquent
 */
class LeadCustomForm extends BaseModel
{

    use CustomFieldsTrait;
    use HasCompany;

    protected $guarded = ['id'];

    const FORM_FIELDS = [
        [
            'status' => 'active',
            'field_display_name' => 'Name',
            'field_name' => 'name',
            'field_order' => 1,
            'required' => 1,
        ],
        [
            'status' => 'active',
            'field_display_name' => 'Email',
            'field_name' => 'email',
            'field_order' => 2,
            'required' => 0,
        ],
        [
            'field_display_name' => 'Company Name',
            'status' => 'active',
            'field_name' => 'company_name',
            'field_order' => 3,
            'required' => 0,
        ],
        [

            'field_display_name' => 'Website',
            'field_name' => 'website',
            'status' => 'active',
            'field_order' => 4,
            'required' => 0,

        ],
        [

            'field_display_name' => 'Address',
            'field_name' => 'address',
            'status' => 'active',
            'field_order' => 5,
            'required' => 0,

        ],
        [

            'field_display_name' => 'Mobile',
            'field_name' => 'mobile',
            'field_order' => 6,
            'status' => 'active',
            'required' => 0,

        ],
        [

            'field_display_name' => 'Message',
            'field_name' => 'message',
            'status' => 'active',
            'field_order' => 7,
            'required' => 0,

        ],
        [

            'field_display_name' => 'City',
            'status' => 'active',
            'field_name' => 'city',
            'field_order' => 1,
            'required' => 0,

        ],
        [

            'field_display_name' => 'State',
            'status' => 'active',
            'field_name' => 'state',
            'field_order' => 2,
            'required' => 0,

        ],
        [

            'field_display_name' => 'Country',
            'field_name' => 'country',
            'status' => 'active',
            'field_order' => 3,
            'required' => 0,

        ],
        [

            'field_display_name' => 'Postal Code',
            'field_name' => 'postal_code',
            'status' => 'active',
            'field_order' => 4,
            'required' => 0,

        ],
        [

            'field_display_name' => 'Source',
            'field_name' => 'source',
            'status' => 'active',
            'field_order' => 8,
            'required' => 0,

        ],
        [

            'field_display_name' => 'Product',
            'field_name' => 'product',
            'status' => 'active',
            'field_order' => 9,
            'required' => 0,

        ],
    ];

    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_fields_id');
    }

}
