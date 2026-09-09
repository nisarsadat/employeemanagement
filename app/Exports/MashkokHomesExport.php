<?php
namespace App\Exports;
use App\Models\fifthyformatone\MashkokHomes;
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

class MashkokHomesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
        $query = MashkokHomes::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) { $q->where($searchField, 'like', "%{$searchValue}%"); })
            ->when($search, function ($q) use ($search) { $q->where(function ($qq) use ($search) { $qq->where('province', 'like', "%{$search}%")->orWhere('home_type', 'like', "%{$search}%"); }); });
        if (!empty($this->filters['resource_id'])) { $query->where('resource_id', $this->filters['resource_id']); }
        return $query->orderByDesc('id');
    }
    public function map($row): array
    {
        return [++$this->rowNumber, $row->province ?? '—', $row->zone ?? '—', $row->district ?? '—', $row->village ?? '—', $row->specific_place ?? '—', $row->street_number ?? '—', $row->home_number ?? '—', $row->coordinates ?? '—', $row->home_type ?? '—', $row->mashkok_type ?? '—', $row->shak_symbols ?? '—', $row->tafseel ?? '—', $row->resource?->code_number ?? $row->resource_id ?? '—', $row->resource?->phone_number ?? '—', $row->note ?? '—'];
    }
    public function headings(): array { return ['شمېره','ولایت','زون','ولسوالۍ','کلی','مشخص ځای','د سرک نومبر','د کور نومبر','مختصات','د کور ډول','مشکوک ډول','د شک علامتونه','تفصیل','د منبع کوډ','د منبع شمېره','یادښت']; }
    public function columnWidths(): array { return ['A'=>10,'B'=>16,'C'=>16,'D'=>16,'E'=>16,'F'=>18,'G'=>14,'H'=>14,'I'=>18,'J'=>16,'K'=>16,'L'=>18,'M'=>20,'N'=>16,'O'=>16,'P'=>30]; }
    public function registerEvents(): array { return [AfterSheet::class => function (AfterSheet $event) { $sheet = $event->sheet->getDelegate(); $hr = 'A1:'.$sheet->getHighestColumn().$sheet->getHighestRow(); $hd = 'A1:'.$sheet->getHighestColumn().'1'; $sheet->setRightToLeft(true); $sheet->freezePane('A2'); $sheet->getStyle($hd)->getFont()->setBold(true); $sheet->getStyle($hd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getStyle($hd)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF'); $sheet->getStyle($hr)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER); $sheet->getRowDimension(1)->setRowHeight(24); $sheet->setAutoFilter($hd); }]; }
}
