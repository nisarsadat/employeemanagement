<?php

namespace App\Exports;

use App\Models\fifthyFormats\Fasad;
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

class FasadExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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

        $query = Fasad::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%")
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
            $row->jabha ?? '—',
            $row->supplier_name ?? '—',
            $row->responsibility ?? '—',
            $row->activity_zone ?? '—',
            $row->friends_number ?? '—',
            $row->phone_number ?? '—',
            $row->bond_distance ?? '—',
            $row->last_band_reason ?? '—',
            $row->band_place ?? '—',
            $row->now_job ?? '—',
            $row->tafseel ?? '—',
            $row->coordinates ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->notes ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره', 'نوم', 'اخترنوم', 'د پلار نوم', 'اصلي ولایت', 'اصلي ولسوالۍ', 'اصلي کلي',
            'اوسنی ولایت', 'اوسنی آمریت', 'اوسنی ولسوالۍ', 'اوسنی کلي', 'مشخص ځای', 'د سرک نومبر',
            'د کور نومبر', 'جنځه', 'د حمایه کوونکی نوم', 'مسؤلیت', 'د فعالیت آمریت', 'د رفیقو شمېره',
            'د اړیکه شمېره', 'د بند لوړتیا', 'د بند شمېره', 'د بند ځای', 'اوسنی دنده',
            'تفصیل', 'مختصات', 'د منبع کوډ', 'د منبع شمېره', 'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return ['A'=>10,'B'=>18,'C'=>16,'D'=>16,'E'=>16,'F'=>16,'G'=>16,'H'=>16,'I'=>16,'J'=>16,'K'=>16,'L'=>18,'M'=>14,'N'=>14,'O'=>16,'P'=>18,'Q'=>16,'R'=>16,'S'=>14,'T'=>16,'U'=>16,'V'=>16,'W'=>16,'X'=>16,'Y'=>20,'Z'=>18,'AA'=>16,'AB'=>16,'AC'=>30];
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
