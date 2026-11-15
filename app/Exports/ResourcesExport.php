<?php

namespace App\Exports;

use App\Models\Resource;
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

class ResourcesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'letry_format_name',
            'resedint_code',
            'code_number',
            'name',
            'father_name',
            'grand_father_name',
            'nick_name',
            'tazkira',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'now_specific_place',
            'now_street_number',
            'now_home_number',
            'old_job',
            'now_job',
            'job_place',
            'zone',
            'hawza',
            'specefic_place',
            'coordinates',
            'activity',
            'halat_madani',
            'age',
            'elmi_level',
            'faculty',
            'specific_skills',
            'resource_saved_date',
            'resource_activity_type',
            'resource_cover',
            'language',
            'nation',
            'blood_group',
            'train',
            'project',
            'phone_number',
            'phone_number_1',
            'phone_number_2',
            'Province_addr',
            'trust_percentage',
            'note',
            'office_employee_code',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);

        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    public function query()
    {
        $today = now()->toDateString();

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

        $stat = trim((string) ($this->filters['stat'] ?? 'all'));
        if (!in_array($stat, ['all', 'today', 'active', 'inactive'], true)) {
            $stat = 'all';
        }

        $dateField = $this->filters['date_field'] ?? 'resource_saved_date';
        if (!in_array($dateField, ['resource_saved_date', 'created_at', 'updated_at'], true)) {
            $dateField = 'resource_saved_date';
        }

        $query = Resource::query()
            ->select([
                'id',
                'office_employee_id',
                'letry_format_name',
                'resedint_code',
                'code_number',
                'name',
                'father_name',
                'grand_father_name',
                'nick_name',
                'tazkira',
                'main_province',
                'main_district',
                'main_village',
                'now_province',
                'now_district',
                'now_village',
                'now_specific_place',
                'now_street_number',
                'now_home_number',
                'old_job',
                'now_job',
                'job_place',
                'zone',
                'hawza',
                'specefic_place',
                'coordinates',
                'activity',
                'halat_madani',
                'age',
                'elmi_level',
                'faculty',
                'specific_skills',
                'resource_saved_date',
                'resource_activity_type',
                'resource_cover',
                'language',
                'nation',
                'phone_number',
                'phone_number_1',
                'phone_number_2',
                'Province_addr',
                'blood_group',
                'train',
                'project',
                'trust_percentage',
                'note',
                'created_at',
                'updated_at',
            ])
            ->with([
                'officeEmployee:id,name,code_number',
            ])
            ->withSum('finances as payed_money', 'money_amount_number')
            ->withCount([
                'events',
                'informations',
                'khayratReports',
            ])
            ->when($stat === 'today', function (Builder $q) use ($today, $dateField) {
                $q->whereNotNull($dateField)
                  ->whereDate($dateField, $today);
            })
            ->when($stat === 'active', function (Builder $q) {
                $q->where('activity', 'فعال');
            })
            ->when($stat === 'inactive', function (Builder $q) {
                $q->where('activity', 'غیر فعال');
            });

        $this->applyCommonFilters($query, $search, $searchField, $searchValue);
        $this->applyShamsiDateFilter($query, $year, $month, $dateField);
        $this->applyRelatedFilters($query);
        $this->applyDateRangeFilter($query, $dateField);

        return $query->orderBy('id', 'asc');
    }

    private function applyCommonFilters($query, ?string $search, ?string $searchField = null, $searchValue = null)
    {
        $query
            ->when(
                $searchValue !== null && $searchValue !== '' && $searchField,
                function ($q) use ($searchField, $searchValue) {
                    if ($searchField === 'office_employee_code') {
                        $q->whereHas('officeEmployee', function ($qq) use ($searchValue) {
                            $qq->where('code_number', $searchValue);
                        });
                    } else {
                        $q->where($searchField, $searchValue);
                    }
                }
            )
            ->when($search, function ($q) use ($search, $searchField) {
                $resolvedField = $this->resolveSearchField($searchField);

                if ($resolvedField === 'office_employee_code') {
                    $q->whereHas('officeEmployee', function ($qq) use ($search) {
                        $qq->where('code_number', 'like', "%{$search}%");
                    });
                } elseif ($resolvedField) {
                    $q->where($resolvedField, 'like', "%{$search}%");
                } else {
                    $q->where(function ($qq) use ($search) {
                        foreach ($this->searchableColumns() as $index => $column) {
                            if ($column === 'office_employee_code') {
                                continue;
                            }

                            if ($index === 0) {
                                $qq->where($column, 'like', "%{$search}%");
                            } else {
                                $qq->orWhere($column, 'like', "%{$search}%");
                            }
                        }

                        $qq->orWhereHas('officeEmployee', function ($sub) use ($search) {
                            $sub->where('code_number', 'like', "%{$search}%");
                        });
                    });
                }
            });

        return $query;
    }

    private function applyRelatedFilters($query): void
    {
        if (!empty($this->filters['resource_id'])) {
            $query->where('id', $this->filters['resource_id']);
        }

        if (!empty($this->filters['office_employee_id'])) {
            $query->where('office_employee_id', $this->filters['office_employee_id']);
        }

        if (!empty($this->filters['information_id'])) {
            $query->whereHas('informations', fn ($q) => $q->where('id', $this->filters['information_id']));
        }

        if (!empty($this->filters['event_id'])) {
            $query->whereHas('events', fn ($q) => $q->where('id', $this->filters['event_id']));
        }

        if (!empty($this->filters['fifty_six_format_id'])) {
            $query->whereHas('fiftySixFormats', fn ($q) => $q->where('id', $this->filters['fifty_six_format_id']));
        }
    }

    private function applyDateRangeFilter($query, string $dateField): void
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
    }

    private function applyShamsiDateFilter($query, ?int $year, ?int $month, string $dateField)
    {
        if ($year && $month) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, $month, 1);
            $lastDay = $month <= 6 ? 31 : ($month <= 11 ? 30 : 29);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, $month, $lastDay);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)
                  ->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        if ($year) {
            [$startGy, $startGm, $startGd] = $this->jalaliToGregorian($year, 1, 1);
            [$endGy, $endGm, $endGd] = $this->jalaliToGregorian($year, 12, 29);

            $startDate = Carbon::create($startGy, $startGm, $startGd)->startOfDay();
            $endDate = Carbon::create($endGy, $endGm, $endGd)->endOfDay();

            $query->whereNotNull($dateField)
                  ->whereBetween($dateField, [$startDate, $endDate]);

            return $query;
        }

        return $query;
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->payed_money ?? 0,
            $row->events_count ?? 0,
            $row->informations_count ?? 0,
            $row->khayrat_reports_count ?? 0,
            $row->letry_format_name ?? '—',
            $row->officeEmployee?->code_number ?? '—',
            $row->officeEmployee?->name ?? '—',
            $row->resedint_code ?? '—',
            $row->code_number ?? '—',
            $row->name ?? '—',
            $row->father_name ?? '—',
            $row->grand_father_name ?? '—',
            $row->nick_name ?? '—',
            $row->tazkira ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->now_specific_place ?? '—',
            $row->now_street_number ?? '—',
            $row->now_home_number ?? '—',
            $row->old_job ?? '—',
            $row->now_job ?? '—',
            $row->job_place ?? '—',
            $row->zone ?? '—',
            $row->hawza ?? '—',
            $row->specefic_place ?? '—',
            $row->coordinates ?? '—',
            $this->pashtoActivity($row->activity),
            $row->halat_madani ?? '—',
            $row->age ?? '—',
            $row->elmi_level ?? '—',
            $row->faculty ?? '—',
            $row->specific_skills ?? '—',
            $this->formatShamsiDate($row->resource_saved_date),
            $row->resource_activity_type ?? '—',
            $row->resource_cover ?? '—',
            $row->language ?? '—',
            $row->nation ?? '—',
            $row->phone_number ?? '—',
            $row->phone_number_1 ?? '—',
            $row->phone_number_2 ?? '—',
            $row->Province_addr ?? '—',
            $row->blood_group ?? '—',
            $row->train ?? '—',
            $row->project ?? '—',
            $row->trust_percentage ?? '—',
            $row->note ?? '—',
            $this->formatShamsiDateTime($row->created_at),
            $this->formatShamsiDateTime($row->updated_at),
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'ورکړل شوې پیسې',
            'د پېښو شمېر',
            'د معلوماتو شمېر',
            'د غیر حاضری راپورونو شمېر',
            'د ۵۶ فورمټ نوم',
            'د کارکوونکي کوډ',
            'د کارکوونکي نوم',
            'رېزېډنټ کوډ',
            'کوډ نمبر',
            'نوم',
            'د پلار نوم',
            'د نیکه نوم',
            'مستعار نوم',
            'تذکره',
            'اصلي ولایت',
            'اصلي ولسوالۍ',
            'اصلي کلی',
            'اوسنی ولایت',
            'اوسنۍ ولسوالۍ',
            'اوسنی کلی',
            'اوسنی مشخص ځای',
            'د سړک شمېره',
            'د کور شمېره',
            'پخوانۍ دنده',
            'اوسنۍ دنده',
            'د کار ځای',
            'آمریت',
            'حوزه',
            'مشخص ځای',
            'مختصات',
            'فعالیت',
            'مدني حالت',
            'عمر',
            'د زده کړې درجه',
            'پوهنځی',
            'ځانګړي مهارتونه',
            'د ثبت نېټه',
            'د فعالیت ډول',
            'د منبع پوښښ',
            'ژبه',
            'قوم',
            'د تلیفون شمېره',
            'د تلیفون شمېره ۱',
            'د تلیفون شمېره ۲',
            'پته (ولایت)',
            'د وینې ګروپ',
            'روزنه',
            'پروژه',
            'د باور سلنه',
            'یادښت',
            'د جوړېدو نېټه',
            'د تازه کېدو نېټه',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'  => 10,
            'B'  => 10,
            'C'  => 18,
            'D'  => 14,
            'E'  => 14,
            'F'  => 18,
            'G'  => 20,
            'H'  => 16,
            'I'  => 22,
            'J'  => 16,
            'K'  => 16,
            'L'  => 20,
            'M'  => 20,
            'N'  => 20,
            'O'  => 18,
            'P'  => 18,
            'Q'  => 18,
            'R'  => 18,
            'S'  => 18,
            'T'  => 18,
            'U'  => 18,
            'V'  => 18,
            'W'  => 22,
            'X'  => 14,
            'Y'  => 14,
            'Z'  => 18,
            'AA' => 18,
            'AB' => 18,
            'AC' => 12,
            'AD' => 12,
            'AE' => 20,
            'AF' => 20,
            'AG' => 12,
            'AH' => 14,
            'AI' => 14,
            'AJ' => 18,
            'AK' => 20,
            'AL' => 18,
            'AM' => 18,
            'AN' => 16,
            'AO' => 16,
            'AP' => 16,
            'AQ' => 18,
            'AR' => 14,
            'AS' => 16,
            'AT' => 16,
            'AU' => 14,
            'AV' => 30,
            'AW' => 20,
            'AX' => 20,
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

    private function pashtoActivity(?string $activity): string
    {
        $activity = trim((string) $activity);

        return match ($activity) {
            'active', 'فعال' => 'فعال',
            'inactive', 'غیر فعال' => 'غیر فعال',
            '' => '—',
            default => $activity,
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

    private function formatShamsiDateTime($value): string
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

            return sprintf(
                '%04d/%02d/%02d %s',
                $jy,
                $jm,
                $jd,
                $date->format('H:i:s')
            );
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