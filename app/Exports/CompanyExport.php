<?php

namespace App\Exports;

use App\Models\fifthyformatfour\Company;
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

class CompanyExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'com_name', 'com_type', 'product_type', 'com_level', 'main_owner',
            'responsible_name', 'responsible_f_name', 'responsible_position', 'responsible_phone',
            'com_province', 'com_zone', 'com_district', 'com_village', 'com_specific_place', 'com_coordinates',
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
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;
        $searchField = $this->resolveSearchField($this->filters['search_field'] ?? null);
        $searchValue = $this->filters['search_value'] ?? null;
        $searchValue = is_string($searchValue) ? trim($searchValue) : $searchValue;
        if ($searchValue === '') $searchValue = null;

        $cardFilter = trim((string) ($this->filters['card_filter'] ?? 'all'));

        $query = Company::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('com_name', 'like', "%{$search}%")
                       ->orWhere('product_type', 'like', "%{$search}%")
                       ->orWhere('responsible_name', 'like', "%{$search}%")
                       ->orWhere('responsible_phone', 'like', "%{$search}%")
                       ->orWhere('com_province', 'like', "%{$search}%")
                       ->orWhere('com_district', 'like', "%{$search}%")
                       ->orWhereHas('resource', function ($sub) use ($search) {
                           $sub->where('code_number', 'like', "%{$search}%");
                       });
                });
            });

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }

        if ($cardFilter === 'withCoordinates') {
            $query->whereNotNull('com_coordinates')->whereRaw("TRIM(com_coordinates) != ''");
        } elseif ($cardFilter === 'withPhone') {
            $query->whereNotNull('responsible_phone')->whereRaw("TRIM(responsible_phone) != ''");
        } elseif ($cardFilter === 'withGuards') {
            $query->whereNotNull('guard_quantity')->where('guard_quantity', '>', 0);
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->com_name ?? '—',
            $row->com_type ?? '—',
            $row->product_type ?? '—',
            $row->com_level ?? '—',
            $row->main_owner ?? '—',
            $row->responsible_name ?? '—',
            $row->responsible_f_name ?? '—',
            $row->responsible_position ?? '—',
            $row->responsible_phone ?? '—',
            $row->com_province ?? '—',
            $row->com_zone ?? '—',
            $row->com_district ?? '—',
            $row->com_village ?? '—',
            $row->com_specific_place ?? '—',
            $row->com_coordinates ?? '—',
            $row->male_employee_quantity_inner ?? '—',
            $row->female_employee_quantity_inner ?? '—',
            $row->male_employee_quantity_out ?? '—',
            $row->female_employee_quantity_out ?? '—',
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
            'د شرکت نوم',
            'د شرکت ډول',
            'د پیداړې ډول',
            'د شرکت کچه',
            'اصلي مالک',
            'د مسؤل نوم',
            'د مسؤل د پلار نوم',
            'د مسؤل وظیفه',
            'د مسؤل شمېره',
            'ولایت',
            'آمریت',
            'ولسوالۍ',
            'کلی',
            'مشخص ځای',
            'مختصات',
            'د نارینه کارمندان (داخلي)',
            'د ښځینه کارمندان (داخلي)',
            'د نارینه کارمندان (بیرونۍ)',
            'د ښځینه کارمندان (بیرونۍ)',
            '保安数',
            'د منبع کوډ',
            'د منبع شمېره',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 22, 'C' => 18, 'D' => 18,
            'E' => 14, 'F' => 18, 'G' => 18, 'H' => 18,
            'I' => 18, 'J' => 16, 'K' => 16, 'L' => 16,
            'M' => 16, 'N' => 16, 'O' => 18, 'P' => 18,
            'Q' => 18, 'R' => 18, 'S' => 18, 'T' => 18,
            'U' => 14, 'V' => 16, 'W' => 16, 'X' => 30,
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
