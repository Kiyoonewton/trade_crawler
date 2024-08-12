<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class WinOrDrawMarket extends Model
{
    use HasFactory;

    protected $collection ='winOrDrawMarket';

    protected $fillable = ['matchday','user_id'];

    public function season()
    {
        return $this->belongsTo(Season::class, 'user_id','_id');
    }
}
