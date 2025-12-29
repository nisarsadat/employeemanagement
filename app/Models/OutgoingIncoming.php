<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutgoingIncoming extends Model
{
    protected $fillable = [
        'outgoing_to_reyasat',
        'out_going_to_others',
        'incomming',

        'ahkam_reyasat',
        'ahkam_rad_shoda_reyasat',
        'reyasat_na_direction',

        'loy_reyasat_ahkam',
        'loy_reyasat_ahakm_rad',
        'loy_reyasat_direction',

        'number',
        'sign_date',
        'outgoing_incoming_date',

        'marja',
        'who_sign',

        'numer_of_jold',
        'list_number',

        'naql_amount',
        'pages',

        'zamima',
        'notes',
    ];

    public function sundryDocuments(): HasMany
    {
        return $this->hasMany(
            SundryDocument::class,
            'outgoing_incoming_id'
        );
    }
}
