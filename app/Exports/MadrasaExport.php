<?php
namespace App\Exports;
use App\Models\fifthyformatsthree\Madrasa;
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

class MadrasaExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
        $query = Madrasa::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) { $q->where($searchField, 'like', "%{$searchValue}%"); })
            ->when($search, function ($q) use ($search) { $q->where(function ($qq) use ($search) { $qq->where('name', 'like', "%{$search}%")->orWhere('responsible_name', 'like', "%{$search}%")->orWhere('province', 'like', "%{$search}%"); }); });
        if (!empty($this->filters['resource_id'])) { $query->where('resource_id', $this->filters['resource_id']); }
        return $query->orderByDesc('id');
    }
    public function map($row): array
    {
        return [++$this->rowNumber, $row->name ?? '—', $row->madrasa_type ?? '—', $row->responsible_name ?? '—', $row->responsible_f_name ?? '—', $row->responsible_position ?? '—', $row->responsible_phone ?? '—', $row->responsible_main_province ?? '—', $row->tarafdari_type_responsible ?? '—', $row->mazhab ?? '—', $row->male_teacher_quantity ?? '—', $row->female_teacher_quantity ?? '—', $row->male_students_quantity ?? '—', $row->female_students_quantity ?? '—', $row->province ?? '—', $row->zone ?? '—', $row->district ?? '—', $row->village ?? '—', $row->specific_place ?? '—', $row->street_number ?? '—', $row->coordinates ?? '—', $row->resource?->code_number ?? $row->resource_id ?? '—', $row->resource?->phone_number ?? '—', $row->note ?? '—'];
    }
    public function headings(): array { return ['شمېره','نوم','د مدرسه ډول','مسؤل نوم','د مسؤل د پلار نوم','د مسؤل موقف','د مسؤل شمېره','د مسؤل اصلي ولایت','د مسؤل تعلق','مذهب','د نارینه ښوونکو شمېره','د ښځینه ښوونکو شمېره','د نارینه زده‌کوونکو شمېره','د ښځینه زده‌کوونکو شمېره','ولایت','زون','ولسوالۍ','کلی','مشخص ځای','د سرک نومبر','مختصات','د منبع کوډ','د منبع شمېره','یادښت']; }
    public function columnWidths(): array { return ['A'=>10,'B'=>20,'C'=>16,'D'=>18,'E'=>18,'F'=>18,'G'=>18,'H'=>16,'I'=>16,'J'=>16,'K'=>16,'L'=>16,'M'=>16,'N'=>16,'O'=>16,'P'=>16,'Q'=>16,'R'=>16,'S'=>18,'T'=>14,'U'=>18,'V'=>16,'W'=>16,'X'=>30]; }
    public function registerEvents(): array { return [AfterSheet::class => function (AfterSheet $event) { $sheet = $event->sheet->getDelegate(); $hr = 'A1:'.$sheet->getHighestColumn().$sheet->getHighestRow(); $hd = 'A1:'.$sheet->getHighestColumn().'1'; $sheet->setRightToLeft(true); $sheet->freezePane('A2'); $sheet->getStyle($hd)->getFont()->setBold(true); $sheet->getStyle($hd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getStyle($hd)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF'); $sheet->getStyle($hr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getRowDimension(1)->setRowHeight(24); $sheet->setAutoFilter($hd); }]; }
}
