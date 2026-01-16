<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseOil extends Model
{
    protected $table = 'expense_oils';

    protected $fillable = [
        'oil_account_id',
        'employee_id',
        'name',
        'father_name',
        'date',
        'liter_in_number',
        'liter_in_words',
        'account_name',
        'tafseel',
        'payer',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'liter_in_number' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(OilAccount::class, 'oil_account_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(OfficeEmployee::class, 'employee_id');
    }

    // Optional (if you use search like other modules)
    public function scopeSearch($query, $search)
    {
        if (!$search) return $query;

        $s = trim($search);

        return $query->where(function ($q) use ($s) {
            $q->where('name', 'like', "%{$s}%")
              ->orWhere('father_name', 'like', "%{$s}%")
              ->orWhere('payer', 'like', "%{$s}%")
              ->orWhere('account_name', 'like', "%{$s}%")
              ->orWhere('tafseel', 'like', "%{$s}%")
              ->orWhere('note', 'like', "%{$s}%");
        });
    }
}
