<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FillmeResults extends Model
{
    protected $table = 'fillme_results';
    protected $fillable = ['uuid', 'sentence_id', 'user_id', 'time', 'languange'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}
