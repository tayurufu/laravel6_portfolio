<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $guarded = ['create_at', 'update_at',];

    protected $hidden = [
        self::CREATED_AT, self::UPDATED_AT,
    ];
}
