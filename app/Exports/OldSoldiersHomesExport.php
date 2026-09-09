<?php

namespace App\Exports;

use App\Models\fifthyformatone\OldSoldiersHomes;
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

class OldSoldiersHomesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
        $query = OldSoldiersHomes::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) { $q->where($searchField, 'like', "%{$searchValue}%"); })
            ->when($search, function ($q) use ($search) { $q->where(function ($qq) use ($search) { $qq->where('name', 'like', "%{$search}%")->orWhere('father_name', 'like', "%{$search}%")->orWhere('phone_number', 'like', "%{$search}%"); }); });
        if (!empty($this->filters['resource_id'])) { $query->where('resource_id', $this->filters['resource_id']); }
        return $query->orderByDesc('id');
    }
    public function map($row): array
    {
        return [++$this->rowNumber, $row->name ?? '—', $row->father_name ?? '—', $row->main_province ?? '—', $row->province ?? '—', $row->zone ?? '—', $row->district ?? '—', $row->village ?? '—', $row->specific_place ?? '—', $row->street_number ?? '—', $row->home_number ?? '—', $row->phone_number ?? '—', $row->coordinates ?? '—', $row->old_job ?? '—', $row->now_activity ?? '—', $row->resource?->code_number ?? $row->resource_id ?? '—', $row->resource?->phone_number ?? '—', $row->note ?? '—'];
    }
    public function headings(): array { return ['شمېره','نوم','د پلار نوم','اصلي ولایت','ولایت','زون','ولسوالۍ','کلی','مشخص ځای','د سرک نومبر','د کور نومبر','د اړیکه شمېره','مختصات','مختصره دنده','اوسنی دنده','د منبع کوډ','د منبع شمېره','یادښت']; }
    public function columnWidths(): array { return ['A'=>10,'B'=>18,'C'=>16,'D'=>16,'E'=>16,'F'=>16,'G'=>16,'H'=>16,'I'=>18,'J'=>14,'K'=>14,'L'=>16,'M'=>18,'N'=>16,'O'=>16,'P'=>16,'Q'=>16,'R'=>30]; }
    public function registerEvents(): array { return [AfterSheet::class => function (AfterSheet $event) { $sheet = $event->sheet->getDelegate(); $hr = 'A1:'.$sheet->getHighestColumn().$sheet->getHighestRow(); $hd = 'A1:'.$sheet->getHighestColumn().'1'; $sheet->setRightToLeft(true); $sheet->freezePane('A2'); $sheet->getStyle($hd)->getFont()->setBold(true); $sheet->getStyle($hd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getStyle($hd)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF'); $sheet->getStyle($hr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getRowDimension(1)->setRowHeight(24); $sheet->setAutoFilter($hd); }]; }
}
