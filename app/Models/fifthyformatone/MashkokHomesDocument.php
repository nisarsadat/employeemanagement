<?php

namespace App\Models\fifthyformatone;

use Illuminate\Database\Eloquent\Model;

class MashkokHomesDocument extends Model
{
    protected $fillable = [
        'mashkok_home_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function mashkokHome()
    {
        return $this->belongsTo(MashkokHomes::class, 'mashkok_home_id');
    }
}
