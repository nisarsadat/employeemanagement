<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JawasisDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'jawasis_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function jawasis()
    {
        return $this->belongsTo(Jawasis::class);
    }
}
