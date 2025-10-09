<?php
// app/Rules/UniqueCodeNumberAcrossStaff.php
namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueCodeNumberAcrossStaff implements ValidationRule
{
    public function __construct(
        private string $currentTable,   // 'office_employees' | 'zone_employees' | 'resedints'
        private ?int $currentId = null, // id to ignore on update
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $tables = ['office_employees', 'zone_employees', 'resedints'];

        foreach ($tables as $table) {
            $query = DB::table($table)->where('code_number', $value);

            if ($table === $this->currentTable && $this->currentId) {
                $query->where('id', '!=', $this->currentId);
            }

            if ($query->exists()) {
                $fail("The :attribute has already been taken.");
                return;
            }
        }
    }
}
