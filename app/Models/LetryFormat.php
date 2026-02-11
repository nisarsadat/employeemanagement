<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetryFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'letry_format_name',
    ];
}
