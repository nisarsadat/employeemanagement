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
        ?string $dateField = 'event_date'
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = trim((string) $searchField) ?: null;
        $this->year = $year;
        $this->month = $month;
        $this->stat = in_array($stat, ['all', 'today', 'syasi', 'jenayee', 'unpaid'], true)
            ? $stat
            : 'all';

        $this->dateField = in_array($dateField, ['event_date', 'created_at', 'updated_at'], true)
            ? $dateField
            : 'event_date';
    }

    private function searchableColumns(): array
    {
        return [
            'id',
            'talks',
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
            'docs_note',
            'note',
            'trust_percentage',
            'Province_addr',
            'talafat_jani',
            'message_to_finance',
            'money_amount_words',
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

        return Event::query()
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

                    $sub->orWhereHas('akhistonkyEmployee', function (Builder $aq) {
                        $aq->where('code_number', 'like', '%' . $this->search . '%')
                           ->orWhere('name', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->stat === 'today', function (Builder $q) use ($today) {
                $q->whereNotNull($this->dateField)
                    ->whereDate($this->dateField, $today);
            })
            ->when($this->stat === 'syasi', function (Builder $q) {
                $q->where('event_type', 'سیاسی');
            })
            ->when($this->stat === 'jenayee', function (Builder $q) {
                $q->where('event_type', 'جنایی');
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
            $row->resource?->code_number ?? '—',
            $row->officeEmployee?->code_number ?? '—',
            $row->talks ?? '—',
            $row->finance_paid_amount ?? 0,
            $this->pashtoFinanceStatus($row->finance_status),
            $row->coordinates ?? '—',
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
            'آی ډي',
            'د منبع کوډ',
            'د کارکوونکي کوډ',
            'همغږي',
            'ورکړل شوې پیسې',
            'د پیسو د ورکړې وضعیت',
            'نقشه',
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
            'B'  => 16,
            'C'  => 16,
            'D'  => 18,
            'E'  => 18,
            'F'  => 22,
            'G'  => 14,
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