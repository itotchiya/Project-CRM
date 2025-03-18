<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\CustomLinkSetting
 *
 * @property int $id
 * @property int|null $company_id
 * @property string $link_title
 * @property string $url
 * @property string|null $can_be_viewed_by
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Company|null $company
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereCanBeViewedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereCompanyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereLinkTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomLinkSetting whereUrl($value)
 * @mixin \Eloquent
 */
class CustomLinkSetting extends BaseModel
{

    use HasFactory, HasCompany;
}
