<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadSetting extends BaseModel
{

    use HasFactory, HasCompany;

    protected $table = 'lead_setting';

}
