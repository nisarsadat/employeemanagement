<?php

namespace App\Exports;

use App\Models\fifthyformatfour\Shora;
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

class ShoraExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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

        $query = Shora::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('shora_name', 'like', "%{$search}%")
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
            $row->shora_name ?? '—',
            $row->shora_goal ?? '—',
            $row->people_quantity ?? '—',
            $row->week_meeting_quantity ?? '—',
            $row->regester ?? '—',
            $row->shora_meeting_place ?? '—',
            $row->responsible_name ?? '—',
            $row->responsible_f_name ?? '—',
            $row->responsible_job ?? '—',
            $row->responsible_phone ?? '—',
            $row->supplier_name ?? '—',
            $row->supplier_job ?? '—',
            $row->supplier_phone ?? '—',
            $row->emarat_tarfdary ?? '—',
            $row->shora_province ?? '—',
            $row->shora_zone ?? '—',
            $row->shora_district ?? '—',
            $row->shora_village ?? '—',
            $row->Province_addr ?? '—',
            $row->coordinates ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'د شورا نوم', 'د شورا هدف', 'د خلکو شمېره',
            'اونۍ مeling شمېره', 'ثبت', 'د مeling ځای',
            'مسول نوم', 'د مسول پلار نوم', 'مسول کار', 'د مسول شمېره',
            'د تامین کوونکي نوم', 'د تامین کوونکي کار', 'د تامین کوونکي شمېره',
            'د امارت ترفیع', 'د شورا ولایت', 'د شورا زون', 'د شورا ولسوالۍ',
            'د شورا کلی', 'د ولایت پته', 'مختصات', 'د منبع کوډ', 'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>16,'C'=>16,'D'=>12,'E'=>12,'F'=>12,'G'=>16,'H'=>16,'I'=>14,'J'=>14,'K'=>14,'L'=>16,'M'=>14,'N'=>14,'O'=>16,'P'=>14,'Q'=>14,'R'=>14,'S'=>14,'T'=>16,'U'=>16,'V'=>14,'W'=>20];
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
