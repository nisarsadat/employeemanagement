<?php

namespace App\Exports;

use App\Models\fifthyformatstow\Bank;
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

class BankExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'bank_name', 'bank_type', 'bank_owner',
            'responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone',
            'which_offices_sallary', 'province', 'zone', 'district', 'village',
            'specific_place', 'guard_quantity', 'note',
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
        if ($searchValue === '') $searchValue = null;

        $cardFilter = trim((string) ($this->filters['card_filter'] ?? 'all'));

        $query = Bank::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search, $searchField) {
                $resolved = $this->resolveSearchField($searchField);
                if ($resolved) {
                    $q->where($resolved, 'like', "%{$search}%");
                } else {
                    $q->where(function ($qq) use ($search) {
                        foreach ($this->searchableColumns() as $i => $col) {
                            if ($i === 0) {
                                $qq->where($col, 'like', "%{$search}%");
                            } else {
                                $qq->orWhere($col, 'like', "%{$search}%");
                            }
                        }
                        $qq->orWhereHas('resource', function ($sub) use ($search) {
                            $sub->where('code_number', 'like', "%{$search}%");
                        });
                    });
                }
            });

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }
        if (!empty($this->filters['fifty_six_format_id'])) {
            $query->where('id', $this->filters['fifty_six_format_id']);
        }

        if ($cardFilter === 'withCoordinates') {
            $query->whereNotNull('coordinates')->whereRaw("TRIM(coordinates) != ''");
        } elseif ($cardFilter === 'withPhone') {
            $query->whereNotNull('responsible_phone')->whereRaw("TRIM(responsible_phone) != ''");
        } elseif ($cardFilter === 'withGuards') {
            $query->whereNotNull('guard_quantity')->whereRaw("TRIM(guard_quantity) != ''")->where('guard_quantity', '>', 0);
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->bank_name ?? '—',
            $row->bank_type ?? '—',
            $row->bank_owner ?? '—',
            $row->responsible_name ?? '—',
            $row->responsible_f_name ?? '—',
            $row->responsible_position ?? '—',
            $row->responsible_phone ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specific_place ?? '—',
            $row->which_offices_sallary ?? '—',
            $row->guard_quantity ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'د بانک نوم',
            'د بانک ډول',
            'د بانک مالک',
            'د مسؤل نوم',
            'د مسؤل د پلار نوم',
            'د مسؤل وظیفه',
            'د مسؤل شمېره',
            'ولایت',
            'آمریت',
            'ولسوالۍ',
            'کلی',
            'مشخص ځای',
            'د کومو ادارو معاشات تادیه کوی',
            'د ځانګړو شمېر',
            'د منبع کوډ',
            'د منبع شمېره',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 22, 'C' => 18, 'D' => 18,
            'E' => 18, 'F' => 18, 'G' => 18, 'H' => 16,
            'I' => 16, 'J' => 16, 'K' => 16, 'L' => 16,
            'M' => 18, 'N' => 22, 'O' => 14, 'P' => 16,
            'Q' => 16, 'R' => 30,
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
