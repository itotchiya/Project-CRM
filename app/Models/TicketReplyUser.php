<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TicketReplyUser extends Pivot
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'ticket_reply_users';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticketReply()
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }

}
