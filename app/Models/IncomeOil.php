<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeOil extends Model
{
    protected $table = 'income_oils';
    protected $fillable = ['oil_account_id', 'date', 'oil_quantity', 'source', 'note']; // <-- add date

    protected $casts = [
        'date' => 'date', // <-- cast to Carbon
    ];

    public function account() { return $this->belongsTo(OilAccount::class, 'oil_account_id'); }
}

