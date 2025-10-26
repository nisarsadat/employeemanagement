<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'account_id','payee_type','payee_id','amount_price','paid_at',
        'phone_number','reason_expense','note'
    ];
    protected $casts = ['paid_at' => 'date', 'amount_price' => 'decimal:2'];

    public function account() { return $this->belongsTo(Account::class); }
    public function payee()   { return $this->morphTo(); } // OfficeEmployee or Resource
}

