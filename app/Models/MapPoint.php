<?php

// app/Models/MapPoint.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'father_name',
        'code_number',
        'coordinates',
        'phone',
        'note',
        'picture_path',
    ];

    protected $appends = ['picture_url'];

    public function getPictureUrlAttribute(): ?string
    {
        return $this->picture_path
            ? asset('storage/' . $this->picture_path)
            : null;
    }
}

