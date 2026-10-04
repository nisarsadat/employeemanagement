<?php

namespace App\Exports;

use App\Models\fifthyformatsthree\Mujahedino;
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

class MujahedinoExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;
    protected array $filters = [];
    protected int $rowNumber = 0;
    public function __construct(array $filters = []) { $this->filters = $filters; }
    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;
        $searchField = $this->filters['search_field'] ?? null;
        $searchValue = $this->filters['search_value'] ?? null;
        $searchValue = is_string($searchValue) ? trim($searchValue) : $searchValue;
        if ($searchValue === '') $searchValue = null;
        $query = Mujahedino::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) { $q->where($searchField, 'like', "%{$searchValue}%"); })
            ->when($search, function ($q) use ($search) { $q->where(function ($qq) use ($search) { $qq->where('name', 'like', "%{$search}%")->orWhere('father_name', 'like', "%{$search}%")->orWhere('phone_number', 'like', "%{$search}%"); }); });
        if (!empty($this->filters['resource_id'])) { $query->where('resource_id', $this->filters['resource_id']); }
        return $query->orderByDesc('id');
    }
    public function map($row): array
    {
        return [++$this->rowNumber, $row->nickname ?? '—', $row->name ?? '—', $row->father_name ?? '—', $row->main_province ?? '—', $row->main_district ?? '—', $row->main_village ?? '—', $row->now_province ?? '—', $row->now_zone ?? '—', $row->now_district ?? '—', $row->now_village ?? '—', $row->now_specific_place ?? '—', $row->now_street_number ?? '—', $row->now_home_number ?? '—', $row->duty_type ?? '—', $row->now_job ?? '—', $row->organ ?? '—', $row->friends_quantity ?? '—', $row->phone_number ?? '—', $row->coordinates ?? '—', $row->resource?->code_number ?? $row->resource_id ?? '—', $row->resource?->phone_number ?? '—', $row->note ?? '—'];
    }
    public function headings(): array { return ['شمېره','اخترنوم','نوم','د پلار نوم','اصلي ولایت','اصلي ولسوالۍ','اصلي کلي','اوسنی ولایت','اوسنی آمریت','اوسنی ولسوالۍ','اوسنی کلي','اوسنی مشخص ځای','اوسنی د سرک نومبر','اوسنی د کور نومبر','د ویلو ډول','اوسنی دنده','ارگان','د رفیقو شمېره','د اړیکه شمېره','مختصات','د منبع کوډ','د منبع شمېره','یادښت']; }
    public function columnWidths(): array { return ['A'=>10,'B'=>16,'C'=>18,'D'=>16,'E'=>16,'F'=>16,'G'=>16,'H'=>16,'I'=>16,'J'=>16,'K'=>16,'L'=>18,'M'=>14,'N'=>14,'O'=>16,'P'=>16,'Q'=>16,'R'=>14,'S'=>16,'T'=>18,'U'=>16,'V'=>16,'W'=>30]; }
    public function registerEvents(): array { return [AfterSheet::class => function (AfterSheet $event) { $sheet = $event->sheet->getDelegate(); $hr = 'A1:'.$sheet->getHighestColumn().$sheet->getHighestRow(); $hd = 'A1:'.$sheet->getHighestColumn().'1'; $sheet->setRightToLeft(true); $sheet->freezePane('A2'); $sheet->getStyle($hd)->getFont()->setBold(true); $sheet->getStyle($hd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getStyle($hd)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF'); $sheet->getStyle($hr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getRowDimension(1)->setRowHeight(24); $sheet->setAutoFilter($hd); }]; }
}
