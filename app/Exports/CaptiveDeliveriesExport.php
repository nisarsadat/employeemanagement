<?php

namespace App\Exports;

use App\Models\CaptiveDelivery;
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

class CaptiveDeliveriesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected ?string $search;
    protected ?string $searchField;
    protected string $stat;
    protected string $dateField;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?string $stat = 'all',
        ?string $dateField = 'captive_date'
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = trim((string) $searchField) ?: null;
        $this->stat = in_array($stat, ['all', 'spy', 'saeen', 'sharFasad', 'today'], true)
            ? $stat
            : 'all';

        $this->dateField = in_array($dateField, ['captive_date', 'created_at', 'updated_at'], true)
            ? $dateField
            : 'captive_date';
    }

    private function searchableColumns(): array
    {
        return [
            'name',
            'father_name',
            'grand_father_name',
            'nickname',
            'tazkira',
            'age',
            'halat_madani',
            'criminal_history',
            'old_job',
            'new_job',
            'language',
            'scientific_level',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'captive_reason',
            'maznonyat_type',
            'taslimi_warkawonky_person',
            'taslimi_akhistonky_person',
            'taslimi_akhistonky_organ',
            'captive_place',
            'coordinates',
            'captive_marja',
            'captive_date',
            'captive_hour',
            'phone_number',
            'captive_halat',
            'suspect_tools',
            'notes',
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

        return CaptiveDelivery::query()
            ->select([
                'id',
                'name',
                'father_name',
                'grand_father_name',
                'nickname',
                'tazkira',
                'age',
                'halat_madani',
                'criminal_history',
                'old_job',
                'new_job',
                'language',
                'scientific_level',
                'main_province',
                'main_district',
                'main_village',
                'now_province',
                'now_district',
                'now_village',
                'captive_reason',
                'maznonyat_type',
                'taslimi_warkawonky_person',
                'taslimi_akhistonky_person',
                'taslimi_akhistonky_organ',
                'captive_place',
                'coordinates',
                'captive_marja',
                'captive_date',
                'captive_hour',
                'phone_number',
                'captive_halat',
                'suspect_tools',
                'notes',
                'created_at',
                'updated_at',
            ])
            ->when($this->search, function (Builder $q) use ($searchField) {
                if ($searchField) {
                    $q->where($searchField, 'like', '%' . $this->search . '%');
                    return;
                }

                $q->where(function (Builder $sub) {
                    foreach ($this->searchableColumns() as $index => $column) {
                        if ($index === 0) {
                            $sub->where($column, 'like', '%' . $this->search . '%');
                        } else {
                            $sub->orWhere($column, 'like', '%' . $this->search . '%');
                        }
                    }
                });
            })
            ->when($this->stat === 'spy', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('maznonyat_type', 'جاسوسی')
                        ->orWhere('maznonyat_type', 'جاسوسي');
                });
            })
            ->when($this->stat === 'saeen', function (Builder $q) {
                $q->where('maznonyat_type', 'ساعین بالفساد');
            })
            ->when($this->stat === 'sharFasad', function (Builder $q) {
                $q->where('maznonyat_type', 'شر او فساد');
            })
            ->when($this->stat === 'today', function (Builder $q) use ($today) {
                $q->whereDate($this->dateField, $today);
            })
            ->latest('id');
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name ?? '—',
            $row->father_name ?? '—',
            $row->grand_father_name ?? '—',
            $row->nickname ?? '—',
            $row->tazkira ?? '—',
            $row->age ?? '—',
            $row->halat_madani ?? '—',
            $row->criminal_history ?? '—',
            $row->old_job ?? '—',
            $row->new_job ?? '—',
            $row->language ?? '—',
            $row->scientific_level ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->captive_reason ?? '—',
            $row->maznonyat_type ?? '—',
            $row->taslimi_warkawonky_person ?? '—',
            $row->taslimi_akhistonky_person ?? '—',
            $row->taslimi_akhistonky_organ ?? '—',
            $row->captive_place ?? '—',
            $row->coordinates ?? '—',
            $row->captive_marja ?? '—',
            $this->formatShamsiDate($row->captive_date),
            $row->captive_hour ?? '—',
            $row->phone_number ?? '—',
            $row->captive_halat ?? '—',
            $row->suspect_tools ?? '—',
            $row->notes ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'آی ډي',
            'نوم',
            'د پلار نوم',
            'د نيکه نوم',
            'مستعار نوم',
            'تذکره',
            'عمر',
            'مدني حالت',
            'جرمي سابقه',
            'پخوانۍ دنده',
            'اوسنۍ دنده',
            'ژبه',
            'علمي سطحه',
            'اصلي ولايت',
            'اصلي ولسوالۍ',
            'اصلي کلی/ګذر',
            'اوسنی ولايت',
            'اوسنۍ ولسوالۍ',
            'اوسنی کلی/ګذر',
            'د اسارت علت',
            'د مظنونيت ډول',
            'تسلیمي ورکوونکی شخص',
            'تسلیمي اخېستونکی شخص',
            'تسلیمي اخېستونکی ارګان',
            'د نيولو ځای',
            'مختصات',
            'د نيولو مرجع',
            'د نيولو نېټه',
            'د نيولو ساعت',
            'د تماس شمېره',
            'د نيولو حالت',
            'اخیستل شوي وسایل او شواهد',
            'ملاحظات',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 18,
            'C' => 18,
            'D' => 18,
            'E' => 18,
            'F' => 18,
            'G' => 10,
            'H' => 14,
            'I' => 18,
            'J' => 18,
            'K' => 18,
            'L' => 14,
            'M' => 16,
            'N' => 16,
            'O' => 16,
            'P' => 18,
            'Q' => 16,
            'R' => 16,
            'S' => 18,
            'T' => 18,
            'U' => 18,
            'V' => 20,
            'W' => 20,
            'X' => 20,
            'Y' => 20,
            'Z' => 18,
            'AA' => 18,
            'AB' => 18,
            'AC' => 14,
            'AD' => 16,
            'AE' => 18,
            'AF' => 28,
            'AG' => 30,
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
}