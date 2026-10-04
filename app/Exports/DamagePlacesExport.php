<?php

namespace App\Exports;

use App\Models\fifthyformatone\DamagePlaces;
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

class DamagePlacesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected array $filters = [];
    protected int $rowNumber = 0;

    public function __construct(array $filters = []) { $this->filters = $filters; }

    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;

        $query = DamagePlaces::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('province', 'like', "%{$search}%")
                       ->orWhere('tahdid_type', 'like', "%{$search}%")
                       ->orWhere('district', 'like', "%{$search}%")
                       ->orWhereHas('resource', fn($s) => $s->where('code_number', 'like', "%{$search}%"));
                });
            });

        if (!empty($this->filters['resource_id'])) $query->where('resource_id', $this->filters['resource_id']);
        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->tahdid_type ?? '—', $row->percentage ?? '—', $row->tahdid_place ?? '—',
            $row->amnyat_khala ?? '—', $row->cameesa_tafseel ?? '—',
            $row->guard_quantity ?? '—',
            $row->masool_name ?? '—', $row->masool_f_name ?? '—',
            $row->masool_position ?? '—', $row->masool_phone ?? '—',
            $row->province ?? '—', $row->zone ?? '—', $row->district ?? '—',
            $row->village ?? '—', $row->specific_place ?? '—', $row->specific_dot ?? '—',
            $row->coordinates ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->Province_addr ?? '—', $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره','د تهدید ډول','سږتونه','د تهدید ځای','امنیتي خاله','تفصیل د کیسه',
            '保安数','د مسؤل نوم','د مسؤل د پلار نوم','د مسؤل وظیفه','د مسؤل شمېره',
            'ولایت','آمریت','ولسوالۍ','کلی','مشخص ځای','مشخص ډټ',
            'مختصات','د منبع کوډ','د منبع شمېره','آدرس','یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'=>10,'B'=>18,'C'=>14,'D'=>18,'E'=>18,'F'=>18,'G'=>12,'H'=>18,'I'=>18,
            'J'=>18,'K'=>16,'L'=>16,'M'=>16,'N'=>16,'O'=>16,'P'=>18,'Q'=>18,'R'=>18,
            'S'=>16,'T'=>16,'U'=>20,'V'=>30,
        ];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $e) {
            $s = $e->sheet->getDelegate();
            $h = $s->getHighestRow(); $c = $s->getHighestColumn();
            $s->setRightToLeft(true); $s->freezePane('A2');
            $s->getStyle('A1:'.$c.'1')->getFont()->setBold(true);
            $s->getStyle('A1:'.$c.'1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $s->getStyle('A1:'.$c.'1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFEFEF');
            $s->getStyle('A1:'.$c.$h)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $s->setAutoFilter('A1:'.$c.'1');
        }];
    }
}
