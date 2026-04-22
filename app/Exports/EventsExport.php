<?php

namespace App\Exports;

use App\Models\Event;
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

class EventsExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected array $filters = [];
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    private function searchableColumns(): array
    {
        return [
            'id',
            'resource_id',
            'resource_display',
            'office_employee_id',
            'office_employee_display',
            'office_employee_akhistonky_id',
            'office_employee_akhistonky_code',
            'talks',
            'finance_paid_amount',
            'finance_status',
            'event_type',
            'event_specific_type',
            'target_person',
            'criminal',
            'event_date',
            'event_hour',
            'event_zone',
            'event_hawza',
            'event_specific_place',
            'coordinates',
            'talafat_mali',
            'injured',
            'killed',
            'tafseel',
            'money_amount_words',
            'note',
            'docs_note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;

        $searchField = $this->resolveSearchField($this->filters['search_field'] ?? null);

        $searchValue = $this->filters['search_value'] ?? null;
        $searchValue = is_string($searchValue) ? trim($searchValue) : $searchValue;
        if ($searchValue === '') {
            $searchValue = null;
        }

        $year = isset($this->filters['year']) && $this->filters['year'] !== ''
            ? (int) $this->filters['year']
            : null;

        $month = isset($this->filters['month']) && $this->filters['month'] !== ''
            ? (int) $this->filters['month']
            : null;

        $dateField = $this->resolveDateField($this->filters['date_field'] ?? 'event_date');
        $cardFilter = trim((string) ($this->filters['card_filter'] ?? ($this->filters['stat'] ?? 'all')));
        $type = trim((string) ($this->filters['type'] ?? ''));

        $query = Event::query()
            ->select([
                'id',
                'resource_id',
                'office_employee_id',
                'office_employee_akhistonky_id',
                'event_type',
                'event_specific_type',
                'target_person',
                'criminal',
                'event_date',
                'event_hour',
                'event_zone',
                'event_hawza',
                'event_specific_place',
                'talafat_jani',
                'talafat_mali',
                'injured',
                'trust_percentage',
                'killed',
                'tafseel',
                'Province_addr',
                'talks',
                'coordinates',
                'note',
                'docs_note',
                'message_to_finance',
                'money_amount_words',
                'finance_status',
                'finance_paid_at',
                'account_id',
                'created_at',
                'updated_at',
            ])
            ->with([
                'resource:id,code_number,name',
                'officeEmployee:id,code_number,name',
                'akhistonkyEmployee:id,code_number,name',
            ])
            ->withSum('finances as finance_paid_amount', 'money_amount_number');

        $this->applySearchFilter($query, $search, $searchField, $searchValue);
        $this->applyCardFilter($query, $cardFilter);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query);
        $this->applyDateRangeFilter($query, $dateField);
        $this->applyTypeFilter($query, $type);

        return $query->orderBy('id', 'asc');
    }

    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['event_date', 'created_at', 'updated_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'event_date';
        }

        return $field;
    }

    private function applySearchFilter($query, ?string $search, ?string $searchField = null, $searchValue = null)
    {
        $resolvedField = $this->resolveSearchField($searchField);
        $search = trim((string) $search);

        if ($searchValue !== null && $searchValue !== '' && $resolvedField) {
            if ($resolvedField === 'resource_display') {
                $query->whereHas('resource', function ($q) use ($searchValue) {
                    $q->where('code_number', $searchValue);
                });
            } elseif ($resolvedField === 'office_employee_display') {
                $query->whereHas('officeEmployee', function ($q) use ($searchValue) {
                    $q->where('code_number', $searchValue);
                });
            } elseif ($resolvedField === 'office_employee_akhistonky_code') {
                $query->whereHas('akhistonkyEmployee', function ($q) use ($searchValue) {
                    $q->where('code_number', $searchValue);
                });
            } elseif ($resolvedField !== 'finance_paid_amount') {
                $query->where($resolvedField, $searchValue);
            }
        }

        if ($search === '') {
            return $query;
        }

        if ($resolvedField) {
            if ($resolvedField === 'resource_display') {
                $query->whereHas('resource', function ($q) use ($search) {
                    $q->where('code_number', 'like', "%{$search}%");
                });
            } elseif ($resolvedField === 'office_employee_display') {
                $query->whereHas('officeEmployee', function ($q) use ($search) {
                    $q->where('code_number', 'like', "%{$search}%");
                });
            } elseif ($resolvedField === 'office_employee_akhistonky_code') {
                $query->whereHas('akhistonkyEmployee', function ($q) use ($search) {
                    $q->where('code_number', 'like', "%{$search}%");
                });
            } elseif ($resolvedField !== 'finance_paid_amount') {
                $query->where($resolvedField, 'like', "%{$search}%");
            }

            return $query;
        }

        $query->where(function ($qq) use ($search) {
            foreach ($this->searchableColumns() as $index => $column) {
                if ($column === 'resource_display') {
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $qq->{$method}('resource', function ($q) use ($search) {
                        $q->where('code_number', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                } elseif ($column === 'office_employee_display') {
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $qq->{$method}('officeEmployee', function ($q) use ($search) {
                        $q->where('code_number', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                } elseif ($column === 'office_employee_akhistonky_code') {
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $qq->{$method}('akhistonkyEmployee', function ($q) use ($search) {
                        $q->where('code_number', 'like', "%{$search}%")
                          ->orWhere('name', 'like', "%{$search}%");
                    });
                } elseif ($column !== 'finance_paid_amount') {
                    if ($index === 0) {
                        $qq->where($column, 'like', "%{$search}%");
                    } else {
                        $qq->orWhere($column, 'like', "%{$search}%");
                    }
                }
            }
        });

        return $query;
    }

    private function applyCardFilter($query, ?string $cardFilter)
    {
        $cardFilter = trim((string) $cardFilter);

        if ($cardFilter === '' || $cardFilter === 'all') {
            return $query;
        }

        if ($cardFilter === 'syasi') {
            $query->where(function ($qq) {
                $qq->where('event_type', 'like', '%سیاسی%')
                  ->orWhere('event_type', 'like', '%سیاسي%')
                  ->orWhere('event_type', 'like', '%syasi%');
            });

            return $query;
        }

        if ($cardFilter === 'jenayee') {
            $query->where(function ($qq) {
                $qq->where('event_type', 'like', '%جینایی%')
                  ->orWhere('event_type', 'like', '%جنایی%')
                  ->orWhere('event_type', 'like', '%جنایي%')
                  ->orWhere('event_type', 'like', '%jinayee%')
                  ->orWhere('event_type', 'like', '%jenayee%');
            });

            return $query;
        }

        if ($cardFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());

            return $query;
        }

        if ($cardFilter === 'unpaid') {
            $query->where(function ($qq) {
                $qq->whereNull('finance_status')
                   ->orWhere('finance_status', '')
                   ->orWhereIn('finance_status', ['unpaid', 'pending', 'ناپرداخت', 'not_paid']);
            });
        }

        return $query;
    }

    private function applyTypeFilter($query, ?string $type)
    {
        $type = trim((string) $type);

        if ($type === '') {
            return $query;
        }

        if ($type === 'syasi') {
            $query->where(function ($q) {
                $q->where('event_type', 'like', '%سیاسی%')
                  ->orWhere('event_type', 'like', '%سیاسي%')
                  ->orWhere('event_type', 'like', '%syasi%');
            });

            return $query;
        }

        if ($type === 'jenayee') {
            $query->where(function ($q) {
                $q->where('event_type', 'like', '%جینایی%')
                  ->orWhere('event_type', 'like', '%جنایی%')
                  ->orWhere('event_type', 'like', '%جنایي%')
                  ->orWhere('event_type', 'like', '%jinayee%')
                  ->orWhere('event_type', 'like', '%jenayee%');
            });

            return $query;
        }

        if ($type === 'elahi') {
            $query->where(function ($q) {
                $q->where('event_type', 'like', '%الهی%')
                  ->orWhere('event_type', 'like', '%الهي%')
                  ->orWhere('event_type', 'like', '%elahi%');
            });

            return $query;
        }

        if ($type === 'traffic') {
            $query->where(function ($q) {
                $q->where('event_type', 'like', '%ترافیکی%')
                  ->orWhere('event_type', 'like', '%ترافیکي%')
                  ->orWhere('event_type', 'like', '%traffic%');
            });

            return $query;
        }

        $query->where('event_specific_type', $type);

        return $query;
    }

    private function applyRelatedFilters($query): void
    {
        if (!empty($this->filters['event_id'])) {
            $query->where('id', $this->filters['event_id']);
        }

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }

        if (!empty($this->filters['office_employee_id'])) {
            $value = $this->filters['office_employee_id'];
            $query->where(function ($q) use ($value) {
                $q->where('office_employee_id', $value)
                  ->orWhere('office_employee_akhistonky_id', $value);
            });
        }

        if (!empty($this->filters['information_id'])) {
            $query->whereHas('informations', fn ($q) => $q->where('id', $this->filters['information_id']));
        }

        if (!empty($this->filters['fifty_six_format_id'])) {
            $query->whereHas('fiftySixFormats', fn ($q) => $q->where('id', $this->filters['fifty_six_format_id']));
        }
    }

    private function applyDateRangeFilter($query, string $dateField)
    {
        $date = $this->filters['date'] ?? null;
        $dateFrom = $this->filters['date_from'] ?? ($this->filters['from_date'] ?? null);
        $dateTo = $this->filters['date_to'] ?? ($this->filters['to_date'] ?? null);

        if (!empty($date)) {
            $query->whereDate($dateField, $date);
        }

        if (!empty($dateFrom)) {
            $query->whereDate($dateField, '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate($dateField, '<=', $dateTo);
        }

        return $query;
    }

    private function applyMonthYearFilter($query, ?int $year, ?int $month, string $dateField)
    {
        if ($year && $month) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month, 1);
            $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($year) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, 1, 1);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($month) {
            $query->whereMonth($dateField, $month);
        }

        return $query;
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->resource?->code_number ?? '—',
            $row->officeEmployee?->code_number ?? '—',
            $row->talks ?? '—',
            $row->finance_paid_amount ?? 0,
            $this->pashtoFinanceStatus($row->finance_status),
            $row->event_type ?? '—',
            $row->event_specific_type ?? '—',
            $row->target_person ?? '—',
            $row->criminal ?? '—',
            $this->formatShamsiDate($row->event_date),
            $row->event_hour ?? '—',
            $row->event_zone ?? '—',
            $row->event_hawza ?? '—',
            $row->event_specific_place ?? '—',
            $row->coordinates ?? '—',
            $row->talafat_mali ?? '—',
            $row->injured ?? '—',
            $row->killed ?? '—',
            $row->akhistonkyEmployee?->code_number ?? '—',
            $row->tafseel ?? '—',
            $row->docs_note ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'مسلسل شمیره',
            'د منبع کوډ',
            'د کارکوونکي کوډ',
            'همغږي',
            'ورکړل شوې پیسې',
            'د پیسو د ورکړې وضعیت',
            'د پېښې ډول',
            'د پېښې مشخص ډول',
            'هدف څوک و / ځای څه و؟',
            'جرم چا کړی؟',
            'د پېښې نېټه',
            'د پېښې ساعت',
            'زون',
            'حوزه',
            'مشخص ځای',
            'مختصات',
            'مالي تلفات',
            'ټپیان',
            'وژل شوي',
            'د پېښې اخېستونکي کارکوونکي کوډ',
            'تفصیل',
            'د اسنادو اړوند یادښت',
            'ملاحظات',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'  => 10,
            'B'  => 10,
            'C'  => 16,
            'D'  => 16,
            'E'  => 18,
            'F'  => 18,
            'G'  => 22,
            'H'  => 20,
            'I'  => 24,
            'J'  => 24,
            'K'  => 24,
            'L'  => 16,
            'M'  => 14,
            'N'  => 12,
            'O'  => 12,
            'P'  => 22,
            'Q'  => 20,
            'R'  => 16,
            'S'  => 14,
            'T'  => 14,
            'U'  => 20,
            'V'  => 35,
            'W'  => 30,
            'X'  => 30,
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

    private function pashtoFinanceStatus(?string $status): string
    {
        $status = trim((string) $status);

        return match ($status) {
            'paid', 'پرداخت', 'paid_done' => 'ورکړل شوې',
            'pending' => 'په انتظار',
            'unpaid', 'ناپرداخت', 'not_paid' => 'نه دي ورکړل شوې',
            '' => '—',
            default => $status,
        };
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
}