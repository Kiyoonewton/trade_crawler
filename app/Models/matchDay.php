<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Support\Str;

class MatchDay extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'matches';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['uuid', 'queryUrl', 'doc'];

    protected $casts = [
        'doc' => 'array',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
