<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OilAccount extends Model
{
    protected $fillable = ['name', 'quantity', 'details'];

    public function incomes() {
        return $this->hasMany(IncomeOil::class);
    }

    public function expenses() {
        return $this->hasMany(ExpenseOil::class);
    }
}
