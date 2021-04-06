<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class xmpUrl extends Model
{
    protected $fillable = [
        'prefix', 'url', 'name'
    ];
}
