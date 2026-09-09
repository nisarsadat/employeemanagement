<?php

namespace App\Exports;

use App\Models\fifthyformatsthree\Badmashano;
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

class BadmashanoExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'nickname', 'name', 'father_name',
            'main_province', 'main_district', 'main_village',
            'now_province', 'now_zone', 'now_district', 'now_village',
            'now_specific_place', 'now_street_number', 'now_home_number',
            'old_job', 'job', 'friends_quantity', 'supplier', 'phone_number',
            'history_jorm', 'jorm_type', 'note',
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

        $query = Badmashano::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('nickname', 'like', "%{$search}%")
                       ->orWhere('father_name', 'like', "%{$search}%")
                       ->orWhere('main_province', 'like', "%{$search}%")
                       ->orWhere('now_province', 'like', "%{$search}%")
                       ->orWhere('job', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%")
                       ->orWhere('coordinates', 'like', "%{$search}%")
                       ->orWhereHas('resource', function ($sub) use ($search) {
                           $sub->where('code_number', 'like', "%{$search}%");
                       });
                });
            });

        if (!empty($this->filters['resource_id'])) {
            $query->where('resource_id', $this->filters['resource_id']);
        }
        if (!empty($this->filters['fifty_six_format_id'])) {
            $query->where('id', $this->filters['fifty_six_format_id']);
        }

        if ($cardFilter === 'withCoordinates') {
            $query->whereNotNull('coordinates')->whereRaw("TRIM(coordinates) != ''");
        } elseif ($cardFilter === 'withPhone') {
            $query->whereNotNull('phone_number')->whereRaw("TRIM(phone_number) != ''");
        } elseif ($cardFilter === 'withJob') {
            $query->whereNotNull('job')->whereRaw("TRIM(job) != ''");
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->nickname ?? '—',
            $row->name ?? '—',
            $row->father_name ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_zone ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->now_specific_place ?? '—',
            $row->now_street_number ?? '—',
            $row->now_home_number ?? '—',
            $row->old_job ?? '—',
            $row->job ?? '—',
            $row->friends_quantity ?? '—',
            $row->supplier ?? '—',
            $row->phone_number ?? '—',
            $row->history_jorm ?? '—',
            $row->jorm_type ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'لقب',
            'نوم',
            'د پلار نوم',
            'اصلي ولایت',
            'اصلي ولسوالۍ',
            'اصلي کلی',
            'اوسنی ولایت',
            'اوسنی زون',
            'اوسنۍ ولسوالۍ',
            'اوسنی کلی',
            'اوسنی مشخص ځای',
            'د سړک شمېره',
            'د کور شمېره',
            'پخوانۍ وظیفه',
            'اوسنۍ وظیفه',
            'د ملګري شمېر',
            'tomoyul kwnk',
            'د تلیفون شمېره',
            'د جرم تاریخ',
            'د تخلف ډول',
            'د منبع کوډ',
            'د منبع شمېره',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 16, 'C' => 18, 'D' => 18,
            'E' => 16, 'F' => 16, 'G' => 16, 'H' => 16,
            'I' => 14, 'J' => 16, 'K' => 16, 'L' => 18,
            'M' => 14, 'N' => 14, 'O' => 18, 'P' => 18,
            'Q' => 14, 'R' => 18, 'S' => 16, 'T' => 18,
            'U' => 16, 'V' => 16, 'W' => 16, 'X' => 30,
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
