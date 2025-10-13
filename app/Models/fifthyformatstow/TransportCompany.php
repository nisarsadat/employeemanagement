<?php

namespace App\Models\fifthyformatstow;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'transport_company_name',
        'type',
        'responsible_name',
        'responsible_f_name',
        'responsible_position',
        'responsible_phone',
        'province',
        'zone',
        'district',
        'village',
        'specific_place',
        'coordinate',
        'ways',
        'note',
        'resource_id', // <— ensure this is present
    ];

    // ✅ Define the relation explicitly
    public function resource()
    {
        // Add ->withTrashed() ONLY if your Resource model uses SoftDeletes
        return $this->belongsTo(Resource::class, 'resource_id', 'id');
        // ->withTrashed();
    }
}
