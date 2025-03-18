<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Qrcodes extends BaseModel
{

    use HasFactory;

    protected $table = 'qrcode';
    protected $fillable = ['qr_enable'];

}
