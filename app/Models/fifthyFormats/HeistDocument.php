<?php

namespace App\Models\fifthyFormats;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeistDocument extends Model
{
    use HasFactory;

    protected $table = 'heist_documents';

    protected $fillable = [
        'heist_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function heist()
    {
        return $this->belongsTo(Heist::class);
    }
}
