<?php

namespace App\Exports;

use App\Models\ExpenseOil;
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
use Morilog\Jalali\Jalalian;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExpenseOilsExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected ?string $search;
    protected ?string $searchField;
    protected ?int $year;
    protected ?int $month;
    protected ?int $oilAccountId;
    protected ?int $employeeId;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?int $year = null,
        ?int $month = null,
        ?int $oilAccountId = null,
        ?int $employeeId = null
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = trim((string) $searchField) ?: null;
        $this->year = $year;
        $this->month = $month;
        $this->oilAccountId = $oilAccountId;
        $this->employeeId = $employeeId;
    }

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

    private function applyExpenseOilSearch(Builder $query, ?string $search, ?string $searchField = null): Builder
    {
        $search = trim((string) $search);

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

        $salA = [
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
            if ($gd <= $salA[$gm]) {
                break;
            }
            $gd -= $salA[$gm];
        }

        return [$gy, $gm, $gd];
    }

    private function applyShamsiDateFilter(Builder $query, ?int $year, ?int $month, string $dateField = 'date'): Builder
    {
        if ($year && $month) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month, 1);
            $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($year) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, 1, 1);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        return $query;
    }

    private function formatShamsiDate($date): string
    {
        if (blank($date)) {
            return '—';
        }

        try {
            return Jalalian::fromCarbon(Carbon::parse($date))->format('Y/m/d');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    }

    public function query()
    {
        $q = ExpenseOil::query()
            ->with([
                'account:id,name,quantity',
                'employee:id,name,code_number,phone_number',
            ])
            ->latest('id');

        $this->applyExpenseOilSearch($q, $this->search, $this->searchField);

        if ($this->oilAccountId) {
            $q->where('oil_account_id', $this->oilAccountId);
        }

        if ($this->employeeId) {
            $q->where('employee_id', $this->employeeId);
        }

        $this->applyShamsiDateFilter($q, $this->year, $this->month, 'date');

        return $q;
    }

    public function map($row): array
    {
        return [
            $row->employee?->code_number ?? '—',
            $row->employee?->name ?? '—',
            $row->name ?? '—',
            $row->father_name ?? '—',
            $this->formatShamsiDate($row->date),
            $row->liter_in_number ?? '—',
            $row->liter_in_words ?? '—',
            $row->account?->name ?? '—',
            $row->tafseel ?? '—',
            $row->payer ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'د کارکوونکي کوډ',
            'د کارکوونکي نوم',
            'نوم',
            'د پلار نوم',
            'نېټه',
            'لېټر په عدد',
            'لېټر په حروف',
            'ډول',
            'تفصیل',
            'ورکوونکی',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 24,
            'C' => 20,
            'D' => 20,
            'E' => 18,
            'F' => 18,
            'G' => 22,
            'H' => 20,
            'I' => 28,
            'J' => 20,
            'K' => 28,
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
                $sheet->setAutoFilter($headerRange);

                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');

                $sheet->getStyle($fullRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $sheet->getRowDimension(1)->setRowHeight(24);
            },
        ];
    }
}