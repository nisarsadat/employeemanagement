<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['account_id','amount','received_at','note'];
    protected $casts = ['received_at' => 'date', 'amount' => 'decimal:2'];

    public function account() { return $this->belongsTo(Account::class); }
}
