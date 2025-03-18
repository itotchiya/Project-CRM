<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSettingForAgents extends BaseModel
{
    use HasFactory, HasCompany;

    protected $table = 'ticket_settings_for_agents';

    protected $fillable = [
        'ticket_scope',
        'group_id',
        'updated_by',
    ];

    protected $casts = [
        'group_id' => 'array',
    ];
}
