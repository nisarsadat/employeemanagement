<?php

namespace App\Exports;

use App\Models\fifthyFormats\Sarafyano;
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

class SarafyanoExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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

        $query = Sarafyano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('sarafi_name', 'like', "%{$search}%")
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
            $row->name ?? '—',
            $row->sarafi_name ?? '—',
            $row->sarafi_type ?? '—',
            $row->father_name ?? '—',
            $row->position ?? '—',
            $row->phone_number ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specific_place ?? '—',
            $row->market_name ?? '—',
            $row->shop_number ?? '—',
            $row->cordinate ?? '—',
            $row->provinces_hawala ?? '—',
            $row->countries_hawala ?? '—',
            $row->employee_quantity ?? '—',
            $row->Province_addr ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->notes ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'نوم', 'د سارفي نوم', 'د سارفي ډول', 'د پلار نوم', 'منصب', 'شمېره',
            'ولایت', 'زون', 'ولسوالۍ', 'کلی', 'مشخص ځای', 'د بازار نوم',
            'د دکان شمېره', 'مختصات', 'د ولایت حوالې', 'د هېواد حوالې', 'د کارمندان شمېره',
            'د ولایت پته', 'د منبع کوډ', 'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>16,'C'=>16,'D'=>14,'E'=>14,'F'=>14,'G'=>14,'H'=>14,'I'=>14,'J'=>14,'K'=>14,'L'=>16,'M'=>16,'N'=>12,'O'=>16,'P'=>16,'Q'=>16,'R'=>12,'S'=>16,'T'=>14,'U'=>20];
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
