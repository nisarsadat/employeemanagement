<?php

namespace App\Exports;

use App\Models\Finance;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class FinancesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected ?string $search;
    protected ?string $searchField;
    protected ?int $year;
    protected ?int $month;
    protected ?int $accountId;
    protected ?int $officeEmployeeId;
    protected string $dateField;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?int $year = null,
        ?int $month = null,
        ?int $accountId = null,
        ?int $officeEmployeeId = null,
        ?string $dateField = 'date'
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = trim((string) $searchField) ?: null;
        $this->year = $year;
        $this->month = $month;
        $this->accountId = $accountId;
        $this->officeEmployeeId = $officeEmployeeId;
        $this->dateField = in_array($dateField, ['date', 'created_at', 'updated_at'], true)
            ? $dateField
            : 'date';
    }

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

    private function applyFinanceSearch(Builder $query, ?string $search, ?string $searchField = null): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $searchField = $this->resolveSearchField($searchField);

        if ($searchField) {
            switch ($searchField) {
                case 'employee_code':
                    $query->whereHas('officeEmployee', fn ($q) => $q->where('code_number', 'like', "%{$search}%"));
                    break;

                case 'employee_name':
                    $query->whereHas('officeEmployee', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                    break;

                case 'resource_nick_name':
                    $query->whereHas('resource', fn ($q) => $q->where('nick_name', 'like', "%{$search}%"));
                    break;

                case 'source_code':
                    $query->whereHas('resource', fn ($q) => $q->where('code_number', 'like', "%{$search}%"));
                    break;

                case 'employee_phone':
                    $query->whereHas('officeEmployee', fn ($q) => $q->where('phone_number', 'like', "%{$search}%"));
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

    private function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

        $jy = ($gy <= 1600) ? 0 : 979;
        $gy -= ($gy <= 1600) ? 621 : 1600;

        $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

        $days = (365 * $gy)
            + intdiv($gy2 + 3, 4)
            - intdiv($gy2 + 99, 100)
            + intdiv($gy2 + 399, 400)
            - 80
            + $gd
            + $g_d_m[$gm - 1];

        $jy += 33 * intdiv($days, 12053);
        $days %= 12053;

        $jy += 4 * intdiv($days, 1461);
        $days %= 1461;

        if ($days > 365) {
            $jy += intdiv($days - 1, 365);
            $days = ($days - 1) % 365;
        }

        if ($days < 186) {
            $jm = 1 + intdiv($days, 31);
            $jd = 1 + ($days % 31);
        } else {
            $jm = 7 + intdiv($days - 186, 30);
            $jd = 1 + (($days - 186) % 30);
        }

        return [$jy, $jm, $jd];
    }

    private function formatShamsiDate($value): string
    {
        if (empty($value)) {
            return '—';
        }

        try {
            $date = $value instanceof Carbon ? $value : Carbon::parse($value);

            [$jy, $jm, $jd] = $this->gregorianToJalali(
                (int) $date->format('Y'),
                (int) $date->format('m'),
                (int) $date->format('d')
            );

            return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    public function query()
    {
        $q = Finance::query()
            ->select([
                'id',
                'office_employee_id',
                'resource_id',
                'account_id',
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
                'created_at',
                'updated_at',
            ])
            ->with([
                'resource:id,code_number,nick_name',
                'officeEmployee:id,name,code_number,phone_number',
                'account:id,name',
            ])
            ->orderByDesc('id');

        $this->applyFinanceSearch($q, $this->search, $this->searchField);

        if ($this->accountId) {
            $q->where('account_id', $this->accountId);
        }

        if ($this->officeEmployeeId) {
            $q->where('office_employee_id', $this->officeEmployeeId);
        }

        $this->applyShamsiDateFilter($q, $this->year, $this->month, $this->dateField);

        return $q;
    }

    public function map($row): array
    {
        return [
            $row->officeEmployee?->code_number ?? '—',
            $row->officeEmployee?->name ?? '—',
            $row->money_amount_number ?? '—',
            $row->money_amount_words ?? '—',
            $row->resource?->nick_name ?? '—',
            $row->resource?->code_number ?? '—',
            $this->formatShamsiDate($row->date),
            $row->officeEmployee?->phone_number ?? ($row->employee_phone ?? '—'),
            $row->expense_result ?? '—',
            $row->payer ?? '—',
            $row->notes ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'د کارمند کوډ',
            'د کارمند نوم',
            'د پیسو اندازه',
            'د پیسو اندازه په تورو',
            'د منبع مستعار نوم',
            'د منبع کوډ',
            'نېټه',
            'د کارمند د تماس شمېره',
            'د مصرف دلیل',
            'ورکوونکی',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 24,
            'C' => 18,
            'D' => 28,
            'E' => 22,
            'F' => 18,
            'G' => 18,
            'H' => 20,
            'I' => 28,
            'J' => 20,
            'K' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $fullRange = 'A1:' . $highestColumn . $highestRow;
                $headerRange = 'A1:' . $highestColumn . '1';

                $sheet->setRightToLeft(true);
                $sheet->freezePane('A2');

                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');

                $sheet->getStyle($fullRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->setAutoFilter($headerRange);
            },
        ];
    }
}