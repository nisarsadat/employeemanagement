<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class ExpenseOil extends Model
{
    protected $table = 'expense_oils';
    protected $fillable = ['oil_account_id', 'date', 'name', 'destination', 'oil_quantity', 'note']; // <-- add date

    protected $casts = [
        'date' => 'date', // <-- cast to Carbon
    ];

    public function account() { return $this->belongsTo(OilAccount::class, 'oil_account_id'); }
}
