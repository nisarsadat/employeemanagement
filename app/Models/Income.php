<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['account_id','amount','received_at','note'];
    protected $casts = ['received_at' => 'date', 'amount' => 'decimal:2'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeSearch($q, $search)
    {
        if (!$search) return $q;

        return $q->where(function ($q) use ($search) {
            $q->where('note', 'like', "%{$search}%")
              ->orWhere('amount', 'like', "%{$search}%");
        });
    }
}
