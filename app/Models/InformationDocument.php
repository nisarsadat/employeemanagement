<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InformationDocument extends Model
{
    protected $table = 'information_documents';

    protected $fillable = [
        'information_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function information()
    {
        return $this->belongsTo(Information::class);
    }
}
