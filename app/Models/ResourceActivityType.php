<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ResourceActivityType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
