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

    protected ?string $search;
    protected ?string $searchField;
    protected ?int $year;
    protected ?int $month;
    protected string $stat;
    protected string $dateField;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?int $year = null,
        ?int $month = null,
        ?string $stat = 'all',
        ?string $dateField = 'resource_saved_date'
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = trim((string) $searchField) ?: null;
        $this->year = $year;
        $this->month = $month;
        $this->stat = in_array($stat, ['all', 'today', 'active', 'inactive'], true) ? $stat : 'all';
        $this->dateField = in_array($dateField, ['resource_saved_date', 'created_at', 'updated_at'], true)
            ? $dateField
            : 'resource_saved_date';
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
        $searchField = $this->resolveSearchField($this->searchField);

        return Resource::query()
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
            ->when($this->year !== null, function (Builder $q) {
                $q->whereNotNull($this->dateField)
                    ->whereYear($this->dateField, $this->year);
            })
            ->when($this->month !== null && $this->month >= 1 && $this->month <= 12, function (Builder $q) {
                $q->whereNotNull($this->dateField)
                    ->whereMonth($this->dateField, $this->month);
            })
            ->when($this->search, function (Builder $q) use ($searchField) {
                if ($searchField) {
                    $q->where($searchField, 'like', '%' . $this->search . '%');
                } else {
                    $q->where(function (Builder $sub) {
                        foreach ($this->searchableColumns() as $index => $column) {
                            if ($index === 0) {
                                $sub->where($column, 'like', '%' . $this->search . '%');
                            } else {
                                $sub->orWhere($column, 'like', '%' . $this->search . '%');
                            }
                        }
                    });
                }
            })
            ->when($this->stat === 'today', function (Builder $q) use ($today) {
                $q->whereNotNull($this->dateField)
                    ->whereDate($this->dateField, $today);
            })
            ->when($this->stat === 'active', function (Builder $q) {
                $q->where('activity', 'فعال');
            })
            ->when($this->stat === 'inactive', function (Builder $q) {
                $q->where('activity', 'غیر فعال');
            })
            ->latest('id');
    }

    public function map($row): array
    {
        return [
            $row->id,
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
            'آی ډي',
            'ورکړل شوې پیسې',
            'د پېښو شمېر',
            'د معلوماتو شمېر',
            'د خیرات راپورونو شمېر',
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
            'زون',
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
            'B'  => 18,
            'C'  => 14,
            'D'  => 14,
            'E'  => 18,
            'F'  => 20,
            'G'  => 16,
            'H'  => 22,
            'I'  => 16,
            'J'  => 16,
            'K'  => 20,
            'L'  => 20,
            'M'  => 20,
            'N'  => 18,
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
            'Y'  => 18,
            'Z'  => 18,
            'AA' => 18,
            'AB' => 12,
            'AC' => 12,
            'AD' => 20,
            'AE' => 20,
            'AF' => 12,
            'AG' => 14,
            'AH' => 14,
            'AI' => 18,
            'AJ' => 20,
            'AK' => 18,
            'AL' => 18,
            'AM' => 16,
            'AN' => 16,
            'AO' => 16,
            'AP' => 18,
            'AQ' => 14,
            'AR' => 16,
            'AS' => 16,
            'AT' => 14,
            'AU' => 30,
            'AV' => 20,
            'AW' => 20,
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
}