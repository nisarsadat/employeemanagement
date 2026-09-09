<?php

namespace App\Exports;

use App\Models\fifthyformatfour\Wakilano;
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

class WakilanoExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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

        $query = Wakilano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('f_name', 'like', "%{$search}%")
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
            $row->f_name ?? '—',
            $row->grand_father_name ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_zone ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->now_specific_place ?? '—',
            $row->now_street_number ?? '—',
            $row->now_home_number ?? '—',
            $row->hawza ?? '—',
            $row->nahya ?? '—',
            $row->Guzar ?? '—',
            $row->coordinates ?? '—',
            $row->place_tafseel ?? '—',
            $row->old_job ?? '—',
            $row->now_job ?? '—',
            $row->home_quantity ?? '—',
            $row->tanzim ?? '—',
            $row->agent_quantity ?? '—',
            $row->phone_number ?? '—',
            $row->history_form ?? '—',
            $row->Province_addr ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'نوم', 'د پلار نوم', 'د نکورا نوم', 'اصلي ولایت',
            'اصلي ولسوالۍ', 'اصلي کلی', 'اوسني ولایت', 'اوسني زون',
            'اوسني ولسوالۍ', 'اوسني کلی', 'اوسني مشخص ځای',
            'اوسني د کوچه نمبر', 'اوسني د کور نمبر', 'حوزه', 'نهیا', 'ګوړ',
            'مختصات', 'ځای تفصیل', 'مخکنيدنده کار', 'اوسني کار',
            'د کورونو شمېره', 'د تشکیلات', 'د اجنتانو شمېره', 'شمېره',
            'د تاریخ فورم', 'د ولایت پته', 'د منبع کوډ', 'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>16,'C'=>14,'D'=>14,'E'=>14,'F'=>14,'G'=>14,'H'=>14,'I'=>14,'J'=>14,'K'=>14,'L'=>16,'M'=>12,'N'=>12,'O'=>14,'P'=>14,'Q'=>14,'R'=>16,'S'=>16,'T'=>14,'U'=>14,'V'=>12,'W'=>14,'X'=>12,'Y'=>14,'Z'=>14,'AA'=>16,'AB'=>14,'AC'=>20];
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
