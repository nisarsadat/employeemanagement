<?php

namespace App\Models\fifthyFormats;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryExit extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'entry_way','way_type','province','zone','district','village',
        'specific_place','cordinate','amnayaty_poste','amnayaty_ishala',
        'Province_addr',
        'where_went','where_come',
        'notes','resource_id',
    ];

    public function resource()
    {
        return $this->belongsTo(Resource::class , 'resource_id' , 'id');
    }
}
