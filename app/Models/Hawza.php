<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hawza extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'name',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}
