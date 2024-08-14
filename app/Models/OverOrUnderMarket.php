<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class OverOrUnderMarket extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'over_or_under';

    protected $fillable = ['season_id', 'market'];

    // protected $cast = [
    //     'market' => 'array',
    // ];

    public function season()
    {
        return $this->belongsTo(Season::class, 'season_id');
    }
}
