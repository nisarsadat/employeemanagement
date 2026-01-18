<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Model;

class SaeenFasadDocument extends Model
{
    protected $fillable = [
        'saeen_fasad_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function saeenFasad()
    {
        return $this->belongsTo(SaeenFasad::class);
    }
}
