<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'price', 'account_type_id', 'details'];

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }
    public function incomes()  { return $this->hasMany(Income::class); }
    public function expenses() { return $this->hasMany(Expense::class); }

}

