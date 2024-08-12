<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class Season extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'seasons';
    protected $fillable = ['seasonId'];

    public function winOrDrawMarket()
    {
        return $this->hasMany(WinOrDrawMarket::class, 'season_id', '_id');
    }

    public function overOrUnderMarket()
    {
        return $this->hasMany(OverOrUnderMarket::class, 'season_id', '_id');
    }
}
