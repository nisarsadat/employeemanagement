<?php

namespace App\Exports;

use App\Models\Information;
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
use Illuminate\Support\Facades\DB;
class InformationsExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'office_employee_info_id',
            'office_employee_info_display',
            'finance_paid_amount',
            'finance_status',
            'name',
            'nick_name',
            'father_name',
            'grand_father_name',
            'madani_halat',
            'age',
            'job',
            'main_province',
            'main_districts',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'specefic_place',
            'street_number',
            'home_number',
            'coordinates',
            'syasi_info_type',
            'jenayee_info_type',
            'letry_format_name',
            'information_saved_date',
            'information_saved_hour',
            'information_degree',
            'information_category',
            'resource_lasrasy',
            'tafseel',
            'docs_note',
            'tahlil',
            'manager_comment',
            'lead_drirection',
            'hamahangi_date',
            'maktop_number',
            'organ',
            'result',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    private function resolveDateField(?string $dateField): string
    {
        $allowed = ['information_saved_date', 'hamahangi_date', 'created_at', 'updated_at'];

        $field = trim((string) $dateField);
        if ($field === '' || !in_array($field, $allowed, true)) {
            return 'information_saved_date';
        }

        return $field;
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

        $dateField = $this->resolveDateField($this->filters['date_field'] ?? 'information_saved_date');
        $stat = trim((string) ($this->filters['stat'] ?? 'all'));
        $type = trim((string) ($this->filters['type'] ?? ''));

        $query = Information::query()
            ->select([
                'id',
                'resource_id',
                'office_employee_id',
                'office_employee_info_id',
                'matched_information_id',
                'name',
                'nick_name',
                'father_name',
                'grand_father_name',
                'Province_addr',
                'madani_halat',
                'age',
                'job',
                'estijalyat_degree',
                'main_province',
                'main_districts',
                'main_village',
                'hamahangi_date',
                'maktop_number',
                'organ',
                'now_province',
                'now_district',
                'now_village',
                'syasi_info_type',
                'jenayee_info_type',
                'information_saved_date',
                'information_saved_hour',
                'information_degree',
                'information_category',
                'trust_percentage',
                'resource_lasrasy',
                'specefic_place',
                'home_number',
                'street_number',
                'tafseel',
                'coordinates',
                'tahlil',
                'manager_comment',
                'lead_drirection',
                'talks',
                'note',
                'docs_note',
                'result',
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
                'officeEmployeeInfo:id,code_number,name',
                'matchedInformation:id',
                'childMatchedInformations:id,matched_information_id',
            ])
            ->withSum('finances as finance_paid_amount', 'money_amount_number');

        $this->applySearchFilter($query, $search, $searchField, $searchValue);
        $this->applyMonthYearFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query);
        $this->applyDateRangeFilter($query, $dateField);
        $this->applyTypeFilter($query, $type);
        $this->applyStatFilter($query, $stat, $dateField);

        return $query->orderBy('id', 'asc');
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
            } elseif ($resolvedField === 'office_employee_info_display') {
                $query->whereHas('officeEmployeeInfo', function ($q) use ($searchValue) {
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
            } elseif ($resolvedField === 'office_employee_info_display') {
                $query->whereHas('officeEmployeeInfo', function ($q) use ($search) {
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
                        $q->where('code_number', 'like', "%{$search}%");
                    });
                } elseif ($column === 'office_employee_display') {
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $qq->{$method}('officeEmployee', function ($q) use ($search) {
                        $q->where('code_number', 'like', "%{$search}%");
                    });
                } elseif ($column === 'office_employee_info_display') {
                    $method = $index === 0 ? 'whereHas' : 'orWhereHas';
                    $qq->{$method}('officeEmployeeInfo', function ($q) use ($search) {
                        $q->where('code_number', 'like', "%{$search}%");
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

    private function applyRelatedFilters($query)
    {
        if (!empty($this->filters['information_id'])) {
            $relatedId = (int) $this->filters['information_id'];

            $selected = Information::query()
                ->select(['id', 'matched_information_id'])
                ->find($relatedId);

            $ids = collect([$relatedId]);

            if ($selected && $selected->matched_information_id) {
                $ids->push((int) $selected->matched_information_id);
            }

            $ids = $ids->filter()->unique()->values();

            $query->where(function ($q) use ($ids) {
                $q->whereIn('id', $ids)
                  ->orWhereIn('matched_information_id', $ids);
            });
        }

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }

        if (!empty($this->filters['office_employee_id'])) {
            $value = $this->filters['office_employee_id'];
            $query->where(function ($q) use ($value) {
                $q->where('office_employee_id', $value)
                  ->orWhere('office_employee_info_id', $value);
            });
        }

        if (!empty($this->filters['event_id'])) {
            $query->whereHas('events', fn ($q) => $q->where('id', $this->filters['event_id']));
        }

        if (!empty($this->filters['fifty_six_format_id'])) {
            $query->whereHas('fiftySixFormats', fn ($q) => $q->where('id', $this->filters['fifty_six_format_id']));
        }

        return $query;
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

    private function applyTypeFilter($query, ?string $type)
    {
        $type = trim((string) $type);

        if ($type === '') {
            return $query;
        }

        $query->where(function ($q) use ($type) {
            $q->where('syasi_info_type', $type)
              ->orWhere('jenayee_info_type', $type);
        });

        return $query;
    }

    private function applyStatFilter($query, ?string $stat, string $dateField)
    {
        $stat = trim((string) $stat);

        if ($stat === '' || $stat === 'all' || $stat === 'resources') {
            return $query;
        }

        if ($stat === 'today') {
            $query->whereDate($dateField, now()->toDateString());
            return $query;
        }

        if ($stat === 'saeen') {
            $query->where(function ($q) {
                $q->where('syasi_info_type', 'ساعین بالفساد')
                  ->orWhere('jenayee_info_type', 'ساعین بالفساد');
            });
            return $query;
        }

        if ($stat === 'shar') {
            $query->where(function ($q) {
                $q->where('syasi_info_type', 'شر او فساد')
                  ->orWhere('jenayee_info_type', 'شر او فساد');
            });
            return $query;
        }

        if ($stat === 'spy') {
            $query->where(function ($q) {
                $q->whereIn('syasi_info_type', ['جاسوسی', ' جاسوسي', 'جواسیس'])
                  ->orWhereIn('jenayee_info_type', ['جاسوسی', ' جاسوسي', 'جواسیس']);
            });
            return $query;
        }

        if ($stat === 'cat_alef') {
            $query->where('information_category', 'الف');
            return $query;
        }

        if ($stat === 'unpaid') {
        $query->where(function ($q) {
            $q->whereNull('finance_status')
            ->orWhere('finance_status', '')
            ->orWhereRaw('LOWER(TRIM(finance_status)) IN (?, ?, ?, ?)', [
                'unpaid',
                'pending',
                'ناپرداخت',
                'not_paid',
            ]);
        });
    }

        if ($stat === 'done') {
            $query->whereNotNull('result')
                  ->where('result', '!=', '');
            return $query;
        }

        if ($stat === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('result')
                  ->orWhere('result', '');
            });
        }

        return $query;
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->id,
            $this->formatMatchedLinks($row),
            $row->resource?->code_number ?? '—',
            $row->officeEmployee?->code_number ?? '—',
            $row->finance_paid_amount ?? 0,
            $this->pashtoFinanceStatus($row->finance_status),
            $this->resultPendingMessage($row),
            $row->name ?? '—',
            $row->nick_name ?? '—',
            $row->father_name ?? '—',
            $row->grand_father_name ?? '—',
            $row->madani_halat ?? '—',
            $row->age ?? '—',
            $row->job ?? '—',
            $row->main_province ?? '—',
            $row->main_districts ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->specefic_place ?? '—',
            $row->street_number ?? '—',
            $row->home_number ?? '—',
            $row->coordinates ?? '—',
            $row->syasi_info_type ?? '—',
            $row->jenayee_info_type ?? '—',
            $this->formatShamsiDate($row->information_saved_date),
            $row->information_saved_hour ?? '—',
            $row->information_degree ?? '—',
            $row->information_category ?? '—',
            $row->officeEmployeeInfo?->code_number ?? '—',
            $row->resource_lasrasy ?? '—',
            $row->tafseel ?? '—',
            $row->docs_note ?? '—',
            $row->tahlil ?? '—',
            $row->manager_comment ?? '—',
            $row->lead_drirection ?? '—',
            $this->formatShamsiDate($row->hamahangi_date),
            $row->maktop_number ?? '—',
            $row->organ ?? '—',
            $row->result ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'مسلسل شمیره',
            'تعقیبي اړیکې',
            'د منبع کوډ',
            'اړوند کارکوونکی',
            'ورکړل شوې پیسې',
            'د پیسو د ورکړې وضعیت',
            'د پایلې ځنډ',
            'نوم',
            'مستعار نوم',
            'د پلار نوم',
            'د نیکه نوم',
            'مدني حالت',
            'عمر',
            'دنده',
            'اصلي ولایت',
            'اصلي ولسوالۍ',
            'اصلي کلی',
            'اوسنی ولایت',
            'اوسنۍ ولسوالۍ',
            'اوسنی کلی',
            'مشخص ځای',
            'د کوڅې شمېره',
            'د کور شمېره',
            'مختصات',
            'سیاسي معلومات',
            'جنایي معلومات',
            'د اطلاع نېټه',
            'د اطلاع ساعت',
            'د اطلاع د اعتبار درجه',
            'د اطلاع کتګوري',
            'اطلاع اخېستونکی کارکوونکی',
            'د منبع لاسرسی',
            'تفصیل',
            'د اسنادو اړوند یادښت',
            'د اطلاع د تعقیب د آمریت نظر',
            'د مدیر نظر',
            'د رهبرۍ لارښوونه',
            'د همغږۍ نېټه',
            'د مکتوب شمېره',
            'ارګان',
            'پایله',
            'ملاحظات',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'  => 10,
            'B'  => 10,
            'C'  => 18,
            'D'  => 16,
            'E'  => 16,
            'F'  => 18,
            'G'  => 22,
            'H'  => 18,
            'I'  => 18,
            'J'  => 18,
            'K'  => 18,
            'L'  => 18,
            'M'  => 16,
            'N'  => 12,
            'O'  => 18,
            'P'  => 18,
            'Q'  => 18,
            'R'  => 18,
            'S'  => 18,
            'T'  => 18,
            'U'  => 18,
            'V'  => 22,
            'W'  => 14,
            'X'  => 14,
            'Y'  => 20,
            'Z'  => 16,
            'AA' => 16,
            'AB' => 16,
            'AC' => 16,
            'AD' => 18,
            'AE' => 35,
            'AF' => 30,
            'AG' => 25,
            'AH' => 25,
            'AI' => 25,
            'AJ' => 18,
            'AK' => 18,
            'AL' => 18,
            'AM' => 30,
            'AN' => 30,
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

    private function resultPendingMessage($row): string
    {
        if (!empty($row->result)) {
            return 'بشپړ شوی';
        }

        if (empty($row->information_saved_date)) {
            return 'په انتظار';
        }

        try {
            $savedAt = Carbon::parse($row->information_saved_date)->startOfDay();
            $today = now()->startOfDay();
            $days = $savedAt->diffInDays($today);

            if ($days < 7) {
                return 'په انتظار';
            }

            if ($days < 14) {
                return '۱ اوونۍ';
            }

            if ($days < 30) {
                return '۲ اوونۍ';
            }

            return 'له ۱ میاشتې ډېر';
        } catch (\Throwable $e) {
            return 'په انتظار';
        }
    }

    private function formatMatchedLinks($row): string
    {
        $parts = [];

        if (!empty($row->matched_information_id)) {
            $parts[] = 'اصلي #' . $row->matched_information_id;
        }

        $childIds = collect($row->childMatchedInformations ?? [])
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        foreach ($childIds as $id) {
            $parts[] = '#' . $id;
        }

        return !empty($parts) ? implode(' | ', $parts) : '—';
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