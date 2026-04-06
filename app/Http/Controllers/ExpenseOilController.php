<?php

namespace App\Http\Controllers;

use App\Exports\ExpenseOilsExport;
use App\Http\Requests\StoreExpenseOilRequest;
use App\Http\Requests\UpdateExpenseOilRequest;
use App\Http\Resources\ExpenseOilResource;
use App\Models\ExpenseOil;
use App\Models\IncomeOil;
use App\Models\OilAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;

class ExpenseOilController extends Controller
{
    private function searchableColumns(): array
    {
        return [
            'employee_code',
            'employee_name',
            'employee_phone',
            'name',
            'father_name',
            'payer',
            'date',
            'liter_in_number',
            'liter_in_words',
            'tafseel',
            'note',
            'account_name',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function applyExpenseOilSearch(Builder $query, string $search, ?string $searchField = null): Builder
    {
        if ($search === '') {
            return $query;
        }

        $searchField = $this->resolveSearchField($searchField);

        if ($searchField) {
            switch ($searchField) {
                case 'employee_code':
                    $query->whereHas('employee', fn ($q) => $q->where('code_number', 'like', "%{$search}%"));
                    break;

                case 'employee_name':
                    $query->whereHas('employee', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    break;

                case 'employee_phone':
                    $query->whereHas('employee', fn ($q) => $q->where('phone_number', 'like', "%{$search}%"));
                    break;

                case 'account_name':
                    $query->whereHas('account', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    break;

                default:
                    $query->where($searchField, 'like', "%{$search}%");
                    break;
            }

            return $query;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('father_name', 'like', "%{$search}%")
                ->orWhere('payer', 'like', "%{$search}%")
                ->orWhere('date', 'like', "%{$search}%")
                ->orWhere('liter_in_number', 'like', "%{$search}%")
                ->orWhere('liter_in_words', 'like', "%{$search}%")
                ->orWhere('tafseel', 'like', "%{$search}%")
                ->orWhere('note', 'like', "%{$search}%")
                ->orWhereHas('employee', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                })
                ->orWhereHas('account', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%");
                });
        });

        return $query;
    }

    private function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
        $days = (365 * $gy)
            + intdiv(($gy2 + 3), 4)
            - intdiv(($gy2 + 99), 100)
            + intdiv(($gy2 + 399), 400)
            - 80
            + $gd
            + $g_d_m[$gm - 1];

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv(($days - 1), 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv(($days - 186), 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    private function jalaliToGregorian(int $jy, int $jm, int $jd): array
    {
        if ($jy > 979) {
            $gy = 1600;
            $jy -= 979;
        } else {
            $gy = 621;
        }

        $days = (365 * $jy)
            + intdiv($jy, 33) * 8
            + intdiv((($jy % 33) + 3), 4)
            + 78
            + $jd
            + (($jm < 7) ? (($jm - 1) * 31) : ((($jm - 7) * 30) + 186));

        $gy += 400 * intdiv($days, 146097);
        $days %= 146097;

        if ($days > 36524) {
            $gy += 100 * intdiv(--$days, 36524);
            $days %= 36524;

            if ($days >= 365) {
                $days++;
            }
        }

        $gy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $gy += intdiv(($days - 1), 365);
            $days = ($days - 1) % 365;
        }

        $gd = $days + 1;

        $sal_a = [
            0,
            31,
            (($gy % 4 === 0 && $gy % 100 !== 0) || ($gy % 400 === 0)) ? 29 : 28,
            31,
            30,
            31,
            30,
            31,
            31,
            30,
            31,
            30,
            31,
        ];

        for ($gm = 1; $gm <= 12; $gm++) {
            if ($gd <= $sal_a[$gm]) {
                break;
            }
            $gd -= $sal_a[$gm];
        }

        return [$gy, $gm, $gd];
    }

    private function shamsiMonthName(int $month): string
    {
        $months = [
            1  => 'حمل',
            2  => 'ثور',
            3  => 'جوزا',
            4  => 'سرطان',
            5  => 'اسد',
            6  => 'سنبله',
            7  => 'میزان',
            8  => 'عقرب',
            9  => 'قوس',
            10 => 'جدی',
            11 => 'دلو',
            12 => 'حوت',
        ];

        return $months[$month] ?? (string) $month;
    }

    private function applyShamsiDateFilter($query, ?int $year, ?int $month, string $dateField = 'date')
    {
        if ($year && $month) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month, 1);
            $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate   = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($year) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, 1, 1);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate   = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        return $query;
    }

    public function index(Request $request)
    {
        $request->validate([
            'perPage'        => ['nullable', 'integer', 'min:1', 'max:2000'],
            'page'           => ['nullable', 'integer', 'min:1'],
            'search'         => ['nullable', 'string'],
            'search_field'   => ['nullable', 'string'],
            'oil_account_id' => ['nullable', 'integer', 'exists:oil_accounts,id'],
            'employee_id'    => ['nullable', 'integer', 'exists:office_employees,id'],
            'year'           => ['nullable', 'integer', 'min:1', 'max:2500'],
            'month'          => ['nullable', 'integer', 'min:1', 'max:12'],
            'date_field'     => ['nullable', 'string'],
        ]);

        $perPage = (int) ($request->get('perPage', 10));
        $page    = (int) ($request->get('page', 1));
        $search  = trim((string) $request->get('search', ''));
        $searchField = $this->resolveSearchField($request->get('search_field'));

        $year  = $request->filled('year') ? (int) $request->get('year') : null;
        $month = $request->filled('month') ? (int) $request->get('month') : null;

        $accountId  = $request->get('oil_account_id');
        $employeeId = $request->get('employee_id');

        $dateField = $request->get('date_field', 'date');
        if (!in_array($dateField, ['date', 'created_at', 'updated_at'], true)) {
            $dateField = 'date';
        }

        $baseQuery = ExpenseOil::query();

        $this->applyExpenseOilSearch($baseQuery, $search, $searchField);

        if (is_numeric($accountId) && (int) $accountId > 0) {
            $baseQuery->where('oil_account_id', (int) $accountId);
        }

        if (is_numeric($employeeId) && (int) $employeeId > 0) {
            $baseQuery->where('employee_id', (int) $employeeId);
        }

        $this->applyShamsiDateFilter($baseQuery, $year, $month, $dateField);

        $hasAnyFilter =
            ($search !== '') ||
            ($year !== null) ||
            ($month !== null) ||
            (is_numeric($accountId) && (int) $accountId > 0) ||
            (is_numeric($employeeId) && (int) $employeeId > 0);

        $filteredExpense = (clone $baseQuery)->sum('liter_in_number');

        $totalQuantityAllAccounts = (string) (OilAccount::query()->sum('quantity') ?? 0);
        $totalExpenseAll = (string) (ExpenseOil::query()->sum('liter_in_number') ?? 0);

        $filteredBalance = null;
        if (is_numeric($accountId) && (int) $accountId > 0) {
            $filteredBalance = (string) (OilAccount::query()->where('id', (int) $accountId)->sum('quantity') ?? 0);
        }

        $filteredIncome = null;
        if (Schema::hasTable('income_oils') && Schema::hasColumn('income_oils', 'oil_quantity')) {
            $incomeQ = IncomeOil::query();

            if (Schema::hasColumn('income_oils', 'oil_account_id') && is_numeric($accountId) && (int) $accountId > 0) {
                $incomeQ->where('oil_account_id', (int) $accountId);
            }

            if (Schema::hasColumn('income_oils', 'date')) {
                $this->applyShamsiDateFilter($incomeQ, $year, $month, 'date');
            }

            $filteredIncome = (string) ($incomeQ->sum('oil_quantity') ?? 0);
        }

        $summary = [
            'total_quantity_all_accounts' => $totalQuantityAllAccounts,
            'total_income_quantity'       => $filteredIncome ?? '0',
            'total_expense_quantity'      => $totalExpenseAll,
            'filtered_expense_quantity'   => (string) ($filteredExpense ?? 0),
            'filtered_balance'            => $filteredBalance,
            'has_filters'                 => $hasAnyFilter,
        ];

        $q = ExpenseOil::query()
            ->with([
                'account:id,name,quantity',
                'employee:id,name,code_number,phone_number',
            ])
            ->orderByDesc('id');

        $this->applyExpenseOilSearch($q, $search, $searchField);

        if (is_numeric($accountId) && (int) $accountId > 0) {
            $q->where('oil_account_id', (int) $accountId);
        }

        if (is_numeric($employeeId) && (int) $employeeId > 0) {
            $q->where('employee_id', (int) $employeeId);
        }

        $this->applyShamsiDateFilter($q, $year, $month, $dateField);

        $collection = ExpenseOilResource::collection(
            $q->paginate($perPage, ['*'], 'page', $page)
        );

        return $collection->additional([
            'summary' => $summary,
        ]);
    }

    public function show(ExpenseOil $expenseOil)
    {
        $expenseOil->load([
            'account:id,name,quantity',
            'employee:id,name,code_number,phone_number',
        ]);

        return new ExpenseOilResource($expenseOil);
    }

    public function store(StoreExpenseOilRequest $request)
    {
        $data = $request->validated();

        $data['date'] = $this->normalizeToGregorianDate($data['date'] ?? null) ?: now()->toDateString();

        $expenseOil = DB::transaction(function () use ($data) {
            $expenseOil = ExpenseOil::create($data);

            $this->applyAccountDelta(
                (int) $data['oil_account_id'],
                -1 * (float) $data['liter_in_number']
            );

            return $expenseOil;
        });

        $expenseOil->load([
            'account:id,name,quantity',
            'employee:id,name,code_number,phone_number',
        ]);

        return new ExpenseOilResource($expenseOil);
    }

    public function update(UpdateExpenseOilRequest $request, ExpenseOil $expenseOil)
    {
        $data = $request->validated();

        $data['date'] = $this->normalizeToGregorianDate($data['date'] ?? null)
            ?: ($expenseOil->date?->toDateString() ?? now()->toDateString());

        DB::transaction(function () use ($expenseOil, $data) {
            $oldAccountId = (int) $expenseOil->oil_account_id;
            $oldAmount    = (float) $expenseOil->liter_in_number;

            $expenseOil->update($data);

            $newAccountId = (int) $expenseOil->oil_account_id;
            $newAmount    = (float) $expenseOil->liter_in_number;

            if ($oldAccountId === $newAccountId) {
                $delta = $oldAmount - $newAmount;
                if ($delta != 0.0) {
                    $this->applyAccountDelta($newAccountId, $delta);
                }
            } else {
                $this->applyAccountDelta($oldAccountId, +1 * $oldAmount);
                $this->applyAccountDelta($newAccountId, -1 * $newAmount);
            }
        });

        $expenseOil->refresh()->load([
            'account:id,name,quantity',
            'employee:id,name,code_number,phone_number',
        ]);

        return new ExpenseOilResource($expenseOil);
    }

    public function destroy(ExpenseOil $expenseOil)
    {
        DB::transaction(function () use ($expenseOil) {
            $this->applyAccountDelta(
                (int) $expenseOil->oil_account_id,
                +1 * (float) $expenseOil->liter_in_number
            );

            $expenseOil->delete();
        });

        return response()->json(['message' => 'Deleted successfully.'], 200);
    }

    public function exportExcel(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $searchField = $this->resolveSearchField($request->get('search_field'));

        $year  = $request->filled('year') ? (int) $request->get('year') : null;
        $month = $request->filled('month') ? (int) $request->get('month') : null;

        $accountId  = $request->filled('oil_account_id') ? (int) $request->get('oil_account_id') : null;
        $employeeId = $request->filled('employee_id') ? (int) $request->get('employee_id') : null;

        $dateField = $request->get('date_field', 'date');
        if (!in_array($dateField, ['date', 'created_at', 'updated_at'], true)) {
            $dateField = 'date';
        }

        $fileName = 'expense_oils_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return Excel::download(
            new ExpenseOilsExport(
                $search,
                $searchField,
                $year,
                $month,
                $accountId,
                $employeeId,
                $dateField
            ),
            $fileName
        );
    }

    public function dateOptions(Request $request)
    {
        $dateField = $request->get('date_field', 'date');

        if (!in_array($dateField, ['date', 'created_at', 'updated_at'], true)) {
            $dateField = 'date';
        }

        $shamsiYear = $request->filled('year') ? (int) $request->get('year') : null;

        $dates = ExpenseOil::query()
            ->whereNotNull($dateField)
            ->pluck($dateField);

        $yearsMap = [];
        $monthsMap = [];

        foreach ($dates as $date) {
            if (!$date) {
                continue;
            }

            $carbon = Carbon::parse($date);

            [$jy, $jm, $jd] = $this->gregorianToJalali(
                (int) $carbon->format('Y'),
                (int) $carbon->format('m'),
                (int) $carbon->format('d')
            );

            $yearsMap[$jy] = $jy;

            if ($shamsiYear && $jy === $shamsiYear) {
                $monthsMap[$jm] = [
                    'value' => $jm,
                    'label' => $this->shamsiMonthName($jm),
                ];
            }
        }

        $years = collect($yearsMap)
            ->sortDesc()
            ->map(fn ($year) => [
                'value' => $year,
                'label' => (string) $year,
            ])
            ->values()
            ->all();

        $months = collect($monthsMap)
            ->sortBy('value')
            ->values()
            ->all();

        return response()->json([
            'date_field' => $dateField,
            'years'      => $years,
            'months'     => $months,
        ]);
    }

    public function years()
    {
        $years = ExpenseOil::query()
            ->whereNotNull('date')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return response()->json($years);
    }

    private function normalizeToGregorianDate(?string $date): ?string
    {
        if (!$date) return null;

        $date = trim($date);
        if ($date === '') return null;

        $parts = explode('-', $date);
        $y = isset($parts[0]) ? (int) $parts[0] : 0;

        if ($y >= 1300 && $y <= 1600) {
            return Jalalian::fromFormat('Y-m-d', $date)->toCarbon()->toDateString();
        }

        return Carbon::parse($date)->toDateString();
    }

    private function applyAccountDelta(int $accountId, float $delta): void
    {
        $acc = OilAccount::lockForUpdate()->findOrFail($accountId);
        $acc->quantity = (float) $acc->quantity + $delta;
        $acc->save();
    }
}