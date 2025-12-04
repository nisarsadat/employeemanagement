<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaptiveDeliveryDocument extends Model
{
    use HasFactory;

    protected $table = 'captive_delivery_documents';

    protected $fillable = [
        'captive_delivery_id',
        'file_name',
        'file_path',
        'mime_type',
        'size_bytes',
    ];

    public function captiveDelivery()
    {
        return $this->belongsTo(CaptiveDelivery::class, 'captive_delivery_id', 'id');
    }
}
