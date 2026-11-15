<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OutgoingIncoming extends Model
{
    protected $fillable = [
        'docs_type',
        'office_outgoing_date',
        'office_outgoing_page',
        'office_incoming_date',
        'office_incoming_page',
        'reyasat_outgoing_date',
        'reyasat_outgoing_page',
        'reyasat_incomming_date',
        'reyasat_incomming_page',
        'loy_reyasat_outgoing_date',
        'loy_reyasat_outgoing_page',
        'answer_date',
        'answer_page',
        'peshnehad',
        'outgoing_incoming_date',
        'who_sighn',
        'marja',
        'tafseel',
        'page',
        'dusya',
        'fihrist_page',
        'naqal_count',
        'awraq',
        'zamima',
        'notes',
    ];

    protected $casts = [
        'office_outgoing_date' => 'date',
        'office_incoming_date' => 'date',
        'reyasat_outgoing_date' => 'date',
        'loy_reyasat_outgoing_date' => 'date',
        'answer_date' => 'date',
        'docs_sign_date' => 'date',
        'outgoing_incoming_date' => 'date',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(SundryDocument::class, 'outgoing_incoming_id');
    }

    public function scopeSearch(Builder $q, ?string $search): Builder
    {
        $search = trim((string) $search);
        if ($search === '') return $q;

        return $q->where(function (Builder $qq) use ($search) {
            $qq->where('docs_type', 'like', "%{$search}%")
               ->orWhere('marja', 'like', "%{$search}%")
               ->orWhere('who_sighn', 'like', "%{$search}%")
               ->orWhere('tafseel', 'like', "%{$search}%")
               ->orWhere('notes', 'like', "%{$search}%")
               ->orWhere('dusya', 'like', "%{$search}%")
               ->orWhere('page', 'like', "%{$search}%");
        });
    }
}
