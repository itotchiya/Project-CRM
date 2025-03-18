<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeBoardUser extends BaseModel
{

    use HasFactory;

    public function employees(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'user_id', 'id')->where('type', 'employee');
    }

    public function clients(): BelongsTo
    {
        return $this->belongsTo(Notice::class, 'user_id', 'id')->where('type', 'client');
    }

}
