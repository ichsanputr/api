<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 
        'reference', 
        'payment_name', 
        'checkout_url', 
        'status', 
        'expired_time', 
        'day'
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}