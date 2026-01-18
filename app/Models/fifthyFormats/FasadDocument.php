<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FasadDocument extends Model
{
    use HasFactory;

    protected $table = 'fasad_documents';

    protected $fillable = [
        'fasad_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function fasad()
    {
        return $this->belongsTo(Fasad::class);
    }
}
