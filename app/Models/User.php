<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class User extends Model
{
    protected $fillable = ['uuid', 'username', 'password', 'pic_url', 'email'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}
