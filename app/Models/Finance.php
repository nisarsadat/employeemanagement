<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Finance extends Model
{
    use HasFactory;

    protected $fillable = [
        'office_employee_id',
        'resource_id',
        'account_id',

        // ✅ NEW (fix)
        'event_id',
        'information_id',

        'money_amount_number',
        'money_amount_words',
        'date',
        'employee_phone',
        'expense_result',
        'money_distributer',
        'payer',
        'receiver',
        'account_type',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'money_amount_number' => 'decimal:2',
    ];

    public function officeEmployee()
    {
        return $this->belongsTo(OfficeEmployee::class, 'office_employee_id');
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class, 'resource_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function information()
    {
        return $this->belongsTo(Information::class, 'information_id');
    }

    public function scopeSearch($q, $search)
    {
        if (!$search) return $q;

        return $q->where(function ($q) use ($search) {
            $q->where('money_amount_words', 'like', "%{$search}%")
              ->orWhere('employee_phone', 'like', "%{$search}%")
              ->orWhere('expense_result', 'like', "%{$search}%")
              ->orWhere('money_distributer', 'like', "%{$search}%")
              ->orWhere('payer', 'like', "%{$search}%")
              ->orWhere('receiver', 'like', "%{$search}%")
              ->orWhere('account_type', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%");
        });
    }
}
