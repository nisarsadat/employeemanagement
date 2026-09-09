<?php

namespace App\Exports;

use App\Models\fifthyFormats\EntryExit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EntryExitExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected array $filters = [];
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;
        $searchField = $this->filters['search_field'] ?? null;
        $searchValue = $this->filters['search_value'] ?? null;
        $searchValue = is_string($searchValue) ? trim($searchValue) : $searchValue;
        if ($searchValue === '') $searchValue = null;

        $query = EntryExit::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('entry_way', 'like', "%{$search}%")
                       ->orWhere('way_type', 'like', "%{$search}%")
                       ->orWhere('province', 'like', "%{$search}%")
                       ->orWhereHas('resource', function ($sub) use ($search) {
                           $sub->where('code_number', 'like', "%{$search}%");
                       });
                });
            });

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->entry_way ?? '—',
            $row->way_type ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specific_place ?? '—',
            $row->cordinate ?? '—',
            $row->amnayaty_poste ?? '—',
            $row->amnayaty_ishala ?? '—',
            $row->where_went ?? '—',
            $row->where_come ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->notes ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'د دخل/ځورځ تړلی ځای', 'د تړلو ډول', 'ولایت', 'زون', 'ولسوالۍ', 'کلی',
            'مشخص ځای', 'مختصات', 'امنیتي پوسته', 'امنیتي ایستل', 'چېرته وړل', 'چېرته راتل',
            'د منبع کوډ', 'د منبع شمېره', 'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>22,'C'=>18,'D'=>16,'E'=>16,'F'=>16,'G'=>16,'H'=>18,'I'=>18,'J'=>18,'K'=>18,'L'=>18,'M'=>18,'N'=>16,'O'=>16,'P'=>30];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $fullRange = 'A1:'.$highestColumn.$highestRow;
            $headerRange = 'A1:'.$highestColumn.'1';
            $sheet->setRightToLeft(true);
            $sheet->freezePane('A2');
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
            $sheet->getStyle($fullRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension(1)->setRowHeight(24);
            $sheet->setAutoFilter($headerRange);
        }];
    }
}
