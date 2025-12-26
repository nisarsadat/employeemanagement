<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'price', 'details'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    // ✅ Outcome (formerly Expense)
    public function finances()
    {
        return $this->hasMany(Finance::class);
    }

    public function scopeSearch($q, $search)
    {
        if (!$search) return $q;

        return $q->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('details', 'like', "%{$search}%");
        });
    }
}
