<?php

namespace App\Exports;

use App\Models\fifthyformatstow\School;
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

class SchoolExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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

        $query = School::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('school_name', 'like', "%{$search}%")
                       ->orWhere('principle_name', 'like', "%{$search}%")
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
            $row->school_name ?? '—',
            $row->school_type ?? '—',
            $row->principle_name ?? '—',
            $row->principle_f_name ?? '—',
            $row->principle_phone ?? '—',
            $row->male_teachers_quantity ?? '—',
            $row->female_teachers_quantity ?? '—',
            $row->male_students_quantity ?? '—',
            $row->female_students_quantity ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specific_place ?? '—',
            $row->street_number ?? '—',
            $row->coordinates ?? '—',
            $row->Province_addr ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'د مکتب نوم', 'د مکتب ډول', 'د مoznالم نوم', 'د مoznالم پلار نوم',
            'د مoznالم شمېره', 'د پسران استاذه شمېره', 'د ښځان استاذه شمېره',
            'د پسران زده‌کړونکو شمېره', 'د ښځان زده‌کړونکو شمېره',
            'ولایت', 'زون', 'ولسوالۍ', 'کلی', 'مشخص ځای',
            'د کوچه نمبر', 'مختصات', 'د ولایت پته', 'د منبع کوډ', 'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>16,'C'=>14,'D'=>16,'E'=>14,'F'=>14,'G'=>12,'H'=>12,'I'=>12,'J'=>12,'K'=>14,'L'=>14,'M'=>14,'N'=>14,'O'=>16,'P'=>12,'Q'=>16,'R'=>16,'S'=>14,'T'=>20];
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
