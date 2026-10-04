<?php

namespace App\Exports;

use App\Models\fifthyformatone\DamageRasanPlaces;
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

class DamageRasanPlacesExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected array $filters = [];
    protected int $rowNumber = 0;

    public function __construct(array $filters = []) { $this->filters = $filters; }

    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;

        $query = DamageRasanPlaces::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('province', 'like', "%{$search}%")
                       ->orWhere('damage_place', 'like', "%{$search}%")
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
            $row->tahdid_type ?? '—', $row->percentage ?? '—', $row->damage_place ?? '—',
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
            'شمېره','د تهدید ډول','سږتونه','د آسیب ځای',
            'ولایت','آمریت','ولسوالۍ','کلی','مشخص ځای','مشخص ډټ',
            'مختصات','د منبع کوډ','د منبع شمېره','آدرس','یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'=>10,'B'=>18,'C'=>14,'D'=>18,'E'=>16,'F'=>16,'G'=>16,'H'=>16,
            'I'=>18,'J'=>18,'K'=>18,'L'=>16,'M'=>16,'N'=>20,'O'=>30,
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
