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

class InformationsExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected ?string $search;
    protected ?string $searchField;
    protected ?int $year;
    protected ?int $month;
    protected string $stat;
    protected string $dateField;
    protected ?int $relatedId;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?int $year = null,
        ?int $month = null,
        ?string $stat = 'all',
        ?string $dateField = 'information_saved_date',
        ?int $relatedId = null
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = trim((string) $searchField) ?: null;
        $this->year = $year;
        $this->month = $month;
        $this->stat = in_array($stat, [
            'all',
            'resources',
            'saeen',
            'shar',
            'spy',
            'today',
            'cat_alef',
            'unpaid',
            'done',
            'pending',
        ], true) ? $stat : 'all';

        $this->dateField = in_array($dateField, [
            'information_saved_date',
            'hamahangi_date',
            'created_at',
            'updated_at',
        ], true) ? $dateField : 'information_saved_date';

        $this->relatedId = $relatedId;
    }

    private function searchableColumns(): array
    {
        return [
            'id',
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
            'finance_status',
            'message_to_finance',
            'money_amount_words',
            'trust_percentage',
            'Province_addr',
            'estijalyat_degree',
            'talks',
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

        return Information::query()
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
            ->withSum('finances as finance_paid_amount', 'money_amount_number')
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

                    $sub->orWhereHas('resource', function (Builder $rq) {
                        $rq->where('code_number', 'like', '%' . $this->search . '%')
                           ->orWhere('name', 'like', '%' . $this->search . '%');
                    });

                    $sub->orWhereHas('officeEmployee', function (Builder $eq) {
                        $eq->where('code_number', 'like', '%' . $this->search . '%')
                           ->orWhere('name', 'like', '%' . $this->search . '%');
                    });

                    $sub->orWhereHas('officeEmployeeInfo', function (Builder $iq) {
                        $iq->where('code_number', 'like', '%' . $this->search . '%')
                           ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->relatedId, function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('matched_information_id', $this->relatedId)
                        ->orWhere('id', $this->relatedId);
                });
            })
            ->when($this->stat === 'today', function (Builder $q) use ($today) {
                $q->whereNotNull($this->dateField)
                    ->whereDate($this->dateField, $today);
            })
            ->when($this->stat === 'done', function (Builder $q) {
                $q->whereNotNull('result')
                  ->where('result', '!=', '');
            })
            ->when($this->stat === 'pending', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->whereNull('result')
                        ->orWhere('result', '');
                });
            })
            ->when($this->stat === 'saeen', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('syasi_info_type', 'like', '%ساعین فساد%')
                        ->orWhere('jenayee_info_type', 'like', '%ساعین فساد%');
                });
            })
            ->when($this->stat === 'shar', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('syasi_info_type', 'like', '%شر او فساد%')
                        ->orWhere('jenayee_info_type', 'like', '%شر او فساد%');
                });
            })
            ->when($this->stat === 'spy', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->where('syasi_info_type', 'like', '%جاسوسی%')
                        ->orWhere('jenayee_info_type', 'like', '%جاسوسی%');
                });
            })
            ->when($this->stat === 'cat_alef', function (Builder $q) {
                $q->where('information_category', 'الف');
            })
            ->when($this->stat === 'unpaid', function (Builder $q) {
                $q->where(function (Builder $sub) {
                    $sub->whereNull('finance_status')
                        ->orWhere('finance_status', '')
                        ->orWhereIn('finance_status', ['unpaid', 'pending', 'ناپرداخت', 'not_paid']);
                });
            })
            ->latest('id');
    }

    public function map($row): array
    {
        return [
            $row->id,
            $this->formatMatchedLinks($row),
            $row->resource?->code_number ?? '—',
            $row->officeEmployee?->code_number ?? '—',
            $row->finance_paid_amount ?? 0,
            $this->pashtoFinanceStatus($row->finance_status),
            $row->coordinates ?? '—',
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
            'آی ډي',
            'تعقیبي اړیکې',
            'د منبع کوډ',
            'اړوند کارکوونکی',
            'ورکړل شوې پیسې',
            'د پیسو د ورکړې وضعیت',
            'نقشه',
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
            'B'  => 18,
            'C'  => 16,
            'D'  => 16,
            'E'  => 18,
            'F'  => 22,
            'G'  => 14,
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

    private function formatShamsiDate($value): ?string
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