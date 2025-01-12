<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FillmeSentences extends Model
{
    protected $table = 'fillme_sentences';

    protected $fillable = ['uuid', 'sentence', 'fill', 'length', 'reported', 'category', 'languange', 'user_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }
}
