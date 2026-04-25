<?php

namespace App\Http\Controllers\Api;

use App\Exports\FinancesExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\FinanceResource;
use App\Models\Account;
use App\Models\Event;
use App\Models\Finance;
use App\Models\Information;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Morilog\Jalali\Jalalian;

class FinanceController extends Controller
{
    private function searchableColumns(): array
    {
        return [
            'employee_code',
            'employee_name',
            'money_amount_number',
            'money_amount_words',
            'resource_nick_name',
            'source_code',
            'date',
            'employee_phone',
            'expense_result',
            'payer',
            'notes',
            'account_name',
            'money_distributer',
            'receiver',
            'account_type',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function applyFinanceSearch(
        Builder $query,
        string $search = '',
        ?string $searchField = null,
        string $searchValue = ''
    ): Builder {
        $searchField = $this->resolveSearchField($searchField);
        $value = trim($searchValue) !== '' ? trim($searchValue) : trim($search);

        if ($searchField) {
            if ($value === '') {
                return $query;
            }

            switch ($searchField) {
                case 'employee_code':
                    $query->whereHas('officeEmployee', fn ($q) => $q->where('code_number', 'like', "%{$value}%"));
                    break;

                case 'employee_name':
                    $query->whereHas('officeEmployee', fn ($q) => $q->where('name', 'like', "%{$value}%"));
                    break;

                case 'resource_nick_name':
                    $query->whereHas('resource', fn ($q) => $q->where('nick_name', 'like', "%{$value}%"));
                    break;

                case 'source_code':
                    $query->whereHas('resource', fn ($q) => $q->where('code_number', 'like', "%{$value}%"));
                    break;

                case 'employee_phone':
                    $query->whereHas('officeEmployee', fn ($q) => $q->where('phone_number', 'like', "%{$value}%"));
                    break;

                case 'account_name':
                    $query->whereHas('account', fn ($q) => $q->where('name', 'like', "%{$value}%"));
                    break;

                default:
                    $query->where($searchField, 'like', "%{$value}%");
                    break;
            }

            return $query;
        }

        if (trim($search) === '') {
            return $query;
        }

        $query->where(function ($q) use ($search) {
            $q->where('money_amount_number', 'like', "%{$search}%")
                ->orWhere('money_amount_words', 'like', "%{$search}%")
                ->orWhere('date', 'like', "%{$search}%")
                ->orWhere('expense_result', 'like', "%{$search}%")
                ->orWhere('payer', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhere('money_distributer', 'like', "%{$search}%")
                ->orWhere('receiver', 'like', "%{$search}%")
                ->orWhere('account_type', 'like', "%{$search}%")
                ->orWhereHas('officeEmployee', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code_number', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                })
                ->orWhereHas('resource', function ($qq) use ($search) {
                    $qq->where('nick_name', 'like', "%{$search}%")
                        ->orWhere('code_number', 'like', "%{$search}%");
                })
                ->orWhereHas('account', function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%");
                });
        });

        return $query;
    }

    public function filterOptions(Request $request)
    {
        $field = $this->resolveSearchField($request->query('field'));
        $q = trim((string) $request->query('q', ''));

        if (!$field) {
            return response()->json([
                'data' => [
                    'options' => [],
                ],
            ]);
        }

        $options = match ($field) {
           

        'employee_code' => DB::table('finances')
            ->join('office_employees', 'finances.office_employee_id', '=', 'office_employees.id')
            ->select('office_employees.code_number as value')
            ->whereNotNull('office_employees.code_number')
            ->where('office_employees.code_number', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('office_employees.code_number', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('office_employees.code_number')
            ->pluck('value'),

        'employee_name' => DB::table('finances')
            ->join('office_employees', 'finances.office_employee_id', '=', 'office_employees.id')
            ->select('office_employees.name as value')
            ->whereNotNull('office_employees.name')
            ->where('office_employees.name', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('office_employees.name', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('office_employees.name')
            ->pluck('value'),

        'employee_phone' => DB::table('finances')
            ->join('office_employees', 'finances.office_employee_id', '=', 'office_employees.id')
            ->select('office_employees.phone_number as value')
            ->whereNotNull('office_employees.phone_number')
            ->where('office_employees.phone_number', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('office_employees.phone_number', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('office_employees.phone_number')
            ->pluck('value'),

        'resource_nick_name' => DB::table('finances')
            ->join('resources', 'finances.resource_id', '=', 'resources.id')
            ->select('resources.nick_name as value')
            ->whereNotNull('resources.nick_name')
            ->where('resources.nick_name', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('resources.nick_name', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('resources.nick_name')
            ->pluck('value'),

        'source_code' => DB::table('finances')
            ->join('resources', 'finances.resource_id', '=', 'resources.id')
            ->select('resources.code_number as value')
            ->whereNotNull('resources.code_number')
            ->where('resources.code_number', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('resources.code_number', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('resources.code_number')
            ->pluck('value'),

        'account_name' => DB::table('finances')
            ->join('accounts', 'finances.account_id', '=', 'accounts.id')
            ->select('accounts.name as value')
            ->whereNotNull('accounts.name')
            ->where('accounts.name', '!=', '')
            ->when($q !== '', fn ($query) => $query->where('accounts.name', 'like', "%{$q}%"))
            ->distinct()
            ->orderBy('accounts.name')
            ->pluck('value'),

            'account_name' => DB::table('accounts')
                ->select('name as value')
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
                ->distinct()
                ->orderBy('name')
                    ->pluck('value'),

            'employee_phone' => DB::table('office_employees')
                ->select('phone_number as value')
                ->whereNotNull('phone_number')
                ->where('phone_number', '!=', '')
                ->when($q !== '', fn ($query) => $query->where('phone_number', 'like', "%{$q}%"))
                ->distinct()
                ->orderBy('phone_number')
                    ->pluck('value'),

            default => Finance::query()
                ->select($field . ' as value')
                ->whereNotNull($field)
                ->where($field, '!=', '')
                ->when($q !== '', fn ($query) => $query->where($field, 'like', "%{$q}%"))
                ->distinct()
                ->orderBy($field)
                    ->pluck('value'),
        };

        return response()->json([
            'data' => [
                'options' => collect($options)
                    ->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
                    ->map(fn ($value) => [
                        'title' => (string) $value,
                        'value' => (string) $value,
                    ])
                    ->values(),
            ],
        ]);
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
            1 => 'حمل',
            2 => 'ثور',
            3 => 'جوزا',
            4 => 'سرطان',
            5 => 'اسد',
            6 => 'سنبله',
            7 => 'میزان',
            8 => 'عقرب',
            9 => 'قوس',
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
            'perPage'            => ['nullable', 'integer', 'min:1', 'max:2000'],
            'page'               => ['nullable', 'integer', 'min:1'],
            'search'             => ['nullable', 'string'],
            'search_field'       => ['nullable', 'string'],
            'search_value'       => ['nullable', 'string'],
            'account_id'         => ['nullable', 'integer', 'exists:accounts,id'],
            'office_employee_id' => ['nullable', 'integer', 'exists:office_employees,id'],
            'year'               => ['nullable', 'integer', 'min:1', 'max:2500'],
            'month'              => ['nullable', 'integer', 'min:1', 'max:12'],
            'date_field'         => ['nullable', 'string'],
        ]);

        $perPage = (int) ($request->get('perPage', 10));
        $page    = (int) ($request->get('page', 1));
        $search  = trim((string) $request->get('search', ''));
        $searchField = $this->resolveSearchField($request->get('search_field'));
        $searchValue = trim((string) $request->get('search_value', ''));

        $year  = $request->filled('year') ? (int) $request->get('year') : null;
        $month = $request->filled('month') ? (int) $request->get('month') : null;

        $accountId        = $request->get('account_id');
        $officeEmployeeId = $request->get('office_employee_id');

        $dateField = $request->get('date_field', 'date');
        if (!in_array($dateField, ['date', 'created_at', 'updated_at'], true)) {
            $dateField = 'date';
        }

        $baseQuery = Finance::query();

        $this->applyFinanceSearch($baseQuery, $search, $searchField, $searchValue);

        if (is_numeric($accountId) && (int) $accountId > 0) {
            $baseQuery->where('account_id', (int) $accountId);
        }

        if (is_numeric($officeEmployeeId) && (int) $officeEmployeeId > 0) {
            $baseQuery->where('office_employee_id', (int) $officeEmployeeId);
        }

        $this->applyShamsiDateFilter($baseQuery, $year, $month, $dateField);

        $hasAnyFilter =
            ($search !== '') ||
            ($searchField !== null) ||
            ($searchValue !== '') ||
            ($year !== null) ||
            ($month !== null) ||
            (is_numeric($accountId) && (int) $accountId > 0) ||
            (is_numeric($officeEmployeeId) && (int) $officeEmployeeId > 0);

        $filteredOutcome = (clone $baseQuery)->sum('money_amount_number');

        $totalBalanceAllAccounts = (string) (Account::query()->sum('price') ?? 0);

        $totalIncomeAll = '0.00';
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'amount')) {
            $totalIncomeAll = (string) (DB::table('incomes')->sum('amount') ?? 0);
        }

        $totalOutcomeAll = (string) (Finance::query()->sum('money_amount_number') ?? 0);

        $filteredBalance = null;
        if (is_numeric($accountId) && (int) $accountId > 0) {
            $filteredBalance = (string) (Account::query()->where('id', (int) $accountId)->sum('price') ?? 0);
        }

        $filteredIncome = null;
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'amount')) {
            $incomeQ = DB::table('incomes');

            if (Schema::hasColumn('incomes', 'account_id') && is_numeric($accountId) && (int) $accountId > 0) {
                $incomeQ->where('account_id', (int) $accountId);
            }

            if (Schema::hasColumn('incomes', 'date')) {
                if ($year) {
                    [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month ?: 1, 1);

                    if ($month) {
                        $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
                        [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);
                    } else {
                        [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);
                    }

                    $incomeQ->whereBetween('date', [
                        Carbon::create($startGy, $startGm, $startGd)->startOfDay(),
                        Carbon::create($endGy, $endGm, $endGd)->endOfDay(),
                    ]);
                }
            }

            $filteredIncome = (string) ($incomeQ->sum('amount') ?? 0);
        }

        $summary = [
            'total_balance_all_accounts' => $totalBalanceAllAccounts,
            'total_income_all'           => $totalIncomeAll,
            'total_outcome_all'          => $totalOutcomeAll,
            'filtered_outcome'           => (string) ($filteredOutcome ?? 0),
            'filtered_income'            => $filteredIncome,
            'filtered_balance'           => $filteredBalance,
            'has_filters'                => $hasAnyFilter,
        ];

        $q = Finance::query()
            ->with([
                'resource:id,name,code_number,phone_number,nick_name',
                'officeEmployee:id,name,code_number,phone_number',
                'account:id,name,price',
                'event:id,finance_status,finance_paid_at',
                'information:id,finance_status,finance_paid_at',
            ])
            ->orderByDesc('id');

        $this->applyFinanceSearch($q, $search, $searchField, $searchValue);

        if (is_numeric($accountId) && (int) $accountId > 0) {
            $q->where('account_id', (int) $accountId);
        }

        if (is_numeric($officeEmployeeId) && (int) $officeEmployeeId > 0) {
            $q->where('office_employee_id', (int) $officeEmployeeId);
        }

        $this->applyShamsiDateFilter($q, $year, $month, $dateField);

        $collection = FinanceResource::collection(
            $q->paginate($perPage, ['*'], 'page', $page)
        );

        return $collection->additional([
            'summary' => $summary,
        ]);
    }

    public function show(Finance $finance)
    {
        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);

        $data['date'] = $this->normalizeToGregorianDate($data['date'] ?? null) ?: now()->toDateString();

        $finance = DB::transaction(function () use ($data) {
            $finance = Finance::create($data);

            $this->applyAccountDelta(
                (int) $data['account_id'],
                -1 * (float) $data['money_amount_number']
            );

            $this->markLinkedPaid($data);

            return $finance;
        });

        $finance->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function update(Request $request, Finance $finance)
    {
        $data = $this->validatePayload($request);

        $data['date'] = $this->normalizeToGregorianDate($data['date'] ?? null)
            ?: ($finance->date?->toDateString() ?? now()->toDateString());

        DB::transaction(function () use ($finance, $data) {
            $oldAccountId = (int) $finance->account_id;
            $oldAmount    = (float) $finance->money_amount_number;

            $finance->update($data);

            $newAccountId = (int) $finance->account_id;
            $newAmount    = (float) $finance->money_amount_number;

            if ($oldAccountId === $newAccountId) {
                $delta = $oldAmount - $newAmount;
                if ($delta != 0.0) {
                    $this->applyAccountDelta($newAccountId, $delta);
                }
            } else {
                $this->applyAccountDelta($oldAccountId, +1 * $oldAmount);
                $this->applyAccountDelta($newAccountId, -1 * $newAmount);
            }

            $this->markLinkedPaid($data);
        });

        $finance->refresh()->load([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ]);

        return new FinanceResource($finance);
    }

    public function destroy(Finance $finance)
    {
        DB::transaction(function () use ($finance) {
            $this->applyAccountDelta(
                (int) $finance->account_id,
                +1 * (float) $finance->money_amount_number
            );

            $finance->delete();
        });

        return response()->json(['message' => 'Deleted'], 200);
    }

    public function exportExcel(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $searchField = $this->resolveSearchField($request->get('search_field'));
        $searchValue = trim((string) $request->get('search_value', ''));

        $year  = $request->filled('year') ? (int) $request->get('year') : null;
        $month = $request->filled('month') ? (int) $request->get('month') : null;

        $accountId        = $request->filled('account_id') ? (int) $request->get('account_id') : null;
        $officeEmployeeId = $request->filled('office_employee_id') ? (int) $request->get('office_employee_id') : null;

        $dateField = $request->get('date_field', 'date');
        if (!in_array($dateField, ['date', 'created_at', 'updated_at'], true)) {
            $dateField = 'date';
        }

        $fileName = 'finances_' . now()->format('Y_m_d_H_i_s') . '.xlsx';

        return Excel::download(
            new FinancesExport(
                $search,
                $searchField,
                $searchValue,
                $year,
                $month,
                $accountId,
                $officeEmployeeId,
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

        $dates = Finance::query()
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
        $years = Finance::query()
            ->whereNotNull('date')
            ->selectRaw('YEAR(date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return response()->json($years);
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'office_employee_id'  => ['nullable', 'integer', 'exists:office_employees,id'],
            'resource_id'         => ['nullable', 'integer', 'exists:resources,id'],
            'account_id'          => ['required', 'integer', 'exists:accounts,id'],
            'event_id'            => ['nullable', 'integer', 'exists:events,id'],
            'information_id'      => ['nullable', 'integer', 'exists:information,id'],
            'money_amount_number' => ['required', 'numeric', 'gt:0'],
            'money_amount_words'  => ['nullable', 'string', 'max:500'],
            'date'                => ['nullable', 'string', 'max:20'],
            'employee_phone'      => ['nullable', 'string', 'max:50'],
            'expense_result'      => ['nullable', 'string', 'max:255'],
            'money_distributer'   => ['nullable', 'string', 'max:255'],
            'payer'               => ['nullable', 'string', 'max:255'],
            'receiver'            => ['nullable', 'string', 'max:255'],
            'account_type'        => ['nullable', 'string', 'max:120'],
            'notes'               => ['nullable', 'string'],
        ]);
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
        $acc = Account::lockForUpdate()->findOrFail($accountId);
        $acc->price = (float) $acc->price + $delta;
        $acc->save();
    }

    private function markLinkedPaid(array $data): void
    {
        $now = now();

        if (!empty($data['event_id'])) {
            Event::where('id', $data['event_id'])->update([
                'finance_status'  => 'paid',
                'finance_paid_at' => $now,
            ]);
        }

        if (!empty($data['information_id'])) {
            Information::where('id', $data['information_id'])->update([
                'finance_status'  => 'paid',
                'finance_paid_at' => $now,
            ]);
        }
    }
    private function resolveDateField(?string $dateField): string
{
    $field = trim((string) $dateField);

    return in_array($field, ['date', 'created_at', 'updated_at'], true)
        ? $field
        : 'date';
}

private function formatFinancePrintDate($value): string
{
    if (empty($value)) {
        return '';
    }

    try {
        $carbon = Carbon::parse($value);

        [$jy, $jm, $jd] = $this->gregorianToJalali(
            (int) $carbon->format('Y'),
            (int) $carbon->format('m'),
            (int) $carbon->format('d')
        );

        return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    } catch (\Throwable $e) {
        $stringValue = trim((string) $value);

        if (preg_match('/(\d{4}[\/-]\d{2}[\/-]\d{2})/', $stringValue, $matches)) {
            return str_replace('-', '/', $matches[1]);
        }

        return trim(preg_replace('/\s*\d{2}:\d{2}:\d{2}$/', '', $stringValue));
    }
}
public function printView(Request $request)
{
    $search = trim((string) $request->get('search', ''));

    // Same style as index()
    $searchField = $this->resolveSearchField($request->get('search_field'));
    $searchValue = trim((string) $request->get('search_value', ''));

    $year  = $request->filled('year') ? (int) $request->get('year') : null;
    $month = $request->filled('month') ? (int) $request->get('month') : null;

    // Same style as index()
    $accountId        = $request->get('account_id');
    $officeEmployeeId = $request->get('office_employee_id');

    // Same style as index()
    $dateField = $request->get('date_field', 'date');
    if (!in_array($dateField, ['date', 'created_at', 'updated_at'], true)) {
        $dateField = 'date';
    }

    $query = Finance::query()
        ->with([
            'resource:id,name,code_number,phone_number,nick_name',
            'officeEmployee:id,name,code_number,phone_number',
            'account:id,name,price',
            'event:id,finance_status,finance_paid_at',
            'information:id,finance_status,finance_paid_at',
        ])
        ->orderByDesc('id');

    // ✅ Same search filter as index()
    $this->applyFinanceSearch($query, $search, $searchField, $searchValue);

    // ✅ Same account filter as index()
    if (is_numeric($accountId) && (int) $accountId > 0) {
        $query->where('account_id', (int) $accountId);
    }

    // ✅ Same employee filter as index()
    if (is_numeric($officeEmployeeId) && (int) $officeEmployeeId > 0) {
        $query->where('office_employee_id', (int) $officeEmployeeId);
    }

    // ✅ Same date filter as index()
    $this->applyShamsiDateFilter($query, $year, $month, $dateField);

    $rows = $query
        ->get()
        ->map(function ($finance, $index) {
            $finance->index = $index + 1;

            $finance->employee_code =
                $finance->officeEmployee?->code_number
                ?? $finance->office_employee_id
                ?? '-';

            $finance->employee_name =
                $finance->officeEmployee?->name
                ?? '-';

            $finance->employee_phone =
                $finance->officeEmployee?->phone_number
                ?? $finance->employee_phone
                ?? '-';

            $finance->resource_nick_name =
                $finance->resource?->nick_name
                ?? '-';

            $finance->source_code =
                $finance->resource?->code_number
                ?? '-';

            $finance->source_nick_name =
                $finance->resource?->nick_name
                ?? '-';

            $finance->account_name =
                $finance->account?->name
                ?? '-';

            $finance->account =
                $finance->account?->name
                ?? '-';

            // ✅ clean Shamsi date, no 00:00:00
            $finance->date = $this->formatFinancePrintDate($finance->date);
            $finance->created_at = $this->formatFinancePrintDate($finance->created_at);
            $finance->updated_at = $this->formatFinancePrintDate($finance->updated_at);

            if (!empty($finance->information_id)) {
                $finance->expense_result = 'اطلاع ' . $finance->information_id;
            } elseif (!empty($finance->event_id)) {
                $finance->expense_result = 'واقعه ' . $finance->event_id;
            } else {
                $finance->expense_result = $finance->expense_result ?: '-';
            }

            $finance->signature = $finance->signature ?? '-';

            return $finance;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ حساب مالی');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
}