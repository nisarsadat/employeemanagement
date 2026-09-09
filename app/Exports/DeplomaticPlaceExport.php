<?php

namespace App\Exports;

use App\Models\fifthyformatfour\DeplomaticPlace;
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

class DeplomaticPlaceExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
{
    use Exportable;

    protected array $filters = [];
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    private function searchableColumns(): array
    {
        return [
            'deplomatic_place_name','activity_type','activity_place',
            'enter_male_quantity','outer_male_quantity','enter_female_quantity','outer_female_quantity',
            'responsible_name','responsible_f_name','responsible_possition','responsible_country','responsible_phone',
            'province','zone','district','village','specefic_place','street_number','home_number',
            'supplier_country','guard_quantity','coordinates','note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);
        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    public function query()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $search = $search !== '' ? $search : null;
        $searchField = $this->resolveSearchField($this->filters['search_field'] ?? null);
        $searchValue = $this->filters['search_value'] ?? null;
        $searchValue = is_string($searchValue) ? trim($searchValue) : $searchValue;
        if ($searchValue === '') $searchValue = null;

        $cardFilter = trim((string) ($this->filters['card_filter'] ?? 'all'));

        $query = DeplomaticPlace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('deplomatic_place_name', 'like', "%{$search}%")
                       ->orWhere('activity_type', 'like', "%{$search}%")
                       ->orWhere('responsible_name', 'like', "%{$search}%")
                       ->orWhere('responsible_phone', 'like', "%{$search}%")
                       ->orWhere('province', 'like', "%{$search}%")
                       ->orWhere('district', 'like', "%{$search}%")
                       ->orWhereHas('resource', function ($sub) use ($search) {
                           $sub->where('code_number', 'like', "%{$search}%");
                       });
                });
            });

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }

        if ($cardFilter === 'enterMale') {
            $query->whereNotNull('enter_male_quantity')->where('enter_male_quantity', '>', 0);
        } elseif ($cardFilter === 'enterFemale') {
            $query->whereNotNull('enter_female_quantity')->where('enter_female_quantity', '>', 0);
        } elseif ($cardFilter === 'outerMale') {
            $query->whereNotNull('outer_male_quantity')->where('outer_male_quantity', '>', 0);
        } elseif ($cardFilter === 'outerFemale') {
            $query->whereNotNull('outer_female_quantity')->where('outer_female_quantity', '>', 0);
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->deplomatic_place_name ?? '—',
            $row->activity_type ?? '—',
            $row->activity_place ?? '—',
            $row->supplier_country ?? '—',
            $row->enter_male_quantity ?? '—',
            $row->outer_male_quantity ?? '—',
            $row->enter_female_quantity ?? '—',
            $row->outer_female_quantity ?? '—',
            $row->responsible_name ?? '—',
            $row->responsible_f_name ?? '—',
            $row->responsible_possition ?? '—',
            $row->responsible_country ?? '—',
            $row->responsible_phone ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specefic_place ?? '—',
            $row->street_number ?? '—',
            $row->home_number ?? '—',
            $row->coordinates ?? '—',
            $row->guard_quantity ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'د دیپلوماټیک ځای نوم',
            'د فعالیت ډول',
            'د فعالیت ځای',
            'د حمایه کوونکی هېواد',
            'د ورودي نارینه',
            'د وروځي نارینه',
            'د ورودي ښځینه',
            'د وروځي ښځینه',
            'مسؤل',
            'د مسؤل د پلار نوم',
            'د مسؤل موقف',
            'د مسؤل هېواد',
            'د مسؤل شمېره',
            'ولایت',
            'زون',
            'ولسوالۍ',
            'کلی',
            'مشخص ځای',
            'د سرک نومبر',
            'د کور نومبر',
            'مختصات',
            'د محافظانو شماره',
            'د منبع کوډ',
            'د منبع شمېره',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 25, 'C' => 20, 'D' => 20,
            'E' => 20, 'F' => 16, 'G' => 16, 'H' => 16,
            'I' => 16, 'J' => 18, 'K' => 18, 'L' => 18,
            'M' => 18, 'N' => 18, 'O' => 16, 'P' => 16,
            'Q' => 16, 'R' => 16, 'S' => 18, 'T' => 14,
            'U' => 14, 'V' => 18, 'W' => 16, 'X' => 16,
            'Y' => 16, 'Z' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $fullRange = 'A1:' . $highestColumn . $highestRow;
                $headerRange = 'A1:' . $highestColumn . '1';

                $sheet->setRightToLeft(true);
                $sheet->freezePane('A2');

                $sheet->getStyle($headerRange)->getFont()->setBold(true);
                $sheet->getStyle($headerRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle($headerRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFEFEFEF');

                $sheet->getStyle($fullRange)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->setAutoFilter($headerRange);
            },
        ];
    }
}
