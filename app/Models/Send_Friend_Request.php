<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Send_Friend_Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'reciver_id',
    ];

    public $timestamps = false;
}
