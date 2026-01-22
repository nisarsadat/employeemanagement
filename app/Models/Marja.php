<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marja extends Model
{
    protected $table = 'marjas';

    protected $fillable = [
        'name',
    ];
}
