<?php

namespace App\Exports;

use App\Models\fifthyFormats\SaeenFasad;
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

class SaeenFasadExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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

        $query = SaeenFasad::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%")
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
            $row->nick_name ?? '—',
            $row->father_name ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_zone ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->specific_place ?? '—',
            $row->street_number ?? '—',
            $row->home_number ?? '—',
            $row->Province_addr ?? '—',
            $row->coordinates ?? '—',
            $row->responsiability ?? '—',
            $row->activity_place ?? '—',
            $row->friends_number ?? '—',
            $row->phone_number ?? '—',
            $row->band_distince ?? '—',
            $row->band_place ?? '—',
            $row->old_job ?? '—',
            $row->now_job ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
            $row->tafseel ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'نوم', 'معروفي', 'د پلار نوم', 'اصلي ولایت', 'اصلي ولسوالۍ', 'اصلي کلی',
            'اوسني ولایت', 'اوسني زون', 'اوسني ولسوالۍ', 'اوسني کلی', 'مشخص ځای',
            'د کوچه نمبر', 'د کور نمبر', 'د ولایت پته', 'مختصات', 'مسولیت', 'د فعالیت ځای',
            'د دوستانه شمېره', 'شمېره', 'د بانډ دواړه', 'د بانډ ځای', 'مخکنيدنده کار',
            'اوسني کار', 'د منبع کوډ', 'د منبع شمېره', 'یادښت', 'تفصیل',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>16,'C'=>14,'D'=>14,'E'=>14,'F'=>14,'G'=>14,'H'=>14,'I'=>14,'J'=>14,'K'=>14,'L'=>16,'M'=>12,'N'=>12,'O'=>16,'P'=>16,'Q'=>14,'R'=>14,'S'=>12,'T'=>14,'U'=>12,'V'=>14,'W'=>14,'X'=>14,'Y'=>14,'Z'=>14,'AA'=>20,'AB'=>16];
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
