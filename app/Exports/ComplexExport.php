<?php

namespace App\Exports;

use App\Models\fifthyformatfour\Complex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ComplexExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected array $filters = [];
    protected int $rowNumber = 0;

    public function __construct(array $filters = []) { $this->filters = $filters; }

    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;

        $query = Complex::query()->with(['resource:id,name,code_number,phone_number'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('complex_name', 'like', "%{$search}%")
                       ->orWhere('province', 'like', "%{$search}%")
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
            $row->complex_name ?? '—', $row->activity_type ?? '—', $row->activity_place ?? '—',
            $row->supplier ?? '—',
            $row->responsible_name ?? '—', $row->responsible_f_name ?? '—',
            $row->responsible_job ?? '—', $row->responsible_phone ?? '—', $row->responsible_country ?? '—',
            $row->enter_male_quantity ?? '—', $row->outer_male_quantity ?? '—',
            $row->enter_femal_quantity ?? '—', $row->outer_femal_quantity ?? '—',
            $row->guard_quantity ?? '—',
            $row->province ?? '—', $row->zone ?? '—', $row->district ?? '—',
            $row->village ?? '—', $row->specefic_place ?? '—',
            $row->street_number ?? '—', $row->home_number ?? '—',
            $row->coordinates ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره','د کمپلکس نوم','د فعالیت ډول','د فعالیت ځای','تامین کوونکی',
            'د مسؤل نوم','د مسؤل د پلار نوم','د مسؤل وظیفه','د مسؤل شمېره','د مسؤل هیواد',
            'اخلي نارینه','بیرونۍ نارینه','اخلي ښځینه','بیرونۍ ښځینه','保安数',
            'ولایت','زون','ولسوالۍ','کلی','مشخص ځای','سړک نمبر','کور نمبر',
            'مختصات','د منبع کوډ','د منبع شمېره','یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A'=>10,'B'=>22,'C'=>18,'D'=>18,'E'=>18,'F'=>18,'G'=>18,'H'=>18,'I'=>16,'J'=>16,
            'K'=>14,'L'=>14,'M'=>14,'N'=>14,'O'=>12,'P'=>16,'Q'=>16,'R'=>16,'S'=>16,'T'=>18,
            'U'=>14,'V'=>14,'W'=>18,'X'=>16,'Y'=>16,'Z'=>30,
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
