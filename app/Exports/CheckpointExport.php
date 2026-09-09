<?php

namespace App\Exports;

use App\Models\fifthyformatfour\Checkpoint;
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

class CheckpointExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'checkpoint_name', 'check_point_place', 'employee_quantity',
            'organ', 'create_reason',
            'officer_name', 'officer_f_name', 'officer_phone',
            'gun_type',
            'province', 'zone', 'district', 'village', 'specefic_place',
            'coordinates', 'note',
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

        $query = Checkpoint::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('checkpoint_name', 'like', "%{$search}%")
                       ->orWhere('organ', 'like', "%{$search}%")
                       ->orWhere('officer_name', 'like', "%{$search}%")
                       ->orWhere('officer_phone', 'like', "%{$search}%")
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

        if ($cardFilter === 'withCoordinates') {
            $query->whereNotNull('coordinates')->whereRaw("TRIM(coordinates) != ''");
        } elseif ($cardFilter === 'withOfficerPhone') {
            $query->whereNotNull('officer_phone')->whereRaw("TRIM(officer_phone) != ''");
        } elseif ($cardFilter === 'withResource') {
            $query->whereNotNull('resource_id')->whereRaw("TRIM(resource_id) != ''");
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->check_point_place ?? '—',
            $row->checkpoint_name ?? '—',
            $row->employee_quantity ?? '—',
            $row->organ ?? '—',
            $row->create_reason ?? '—',
            $row->officer_name ?? '—',
            $row->officer_f_name ?? '—',
            $row->officer_phone ?? '—',
            $row->gun_type ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specefic_place ?? '—',
            $row->coordinates ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'د چک پواینټ ځای',
            'د چک پواینټ نوم',
            'د کارمندانو شماره',
            'ارگان',
            'د راتلو علت',
            'د پوسته مسؤل',
            'د مسؤل د پلار نوم',
            'د مسؤل شمېره',
            'د سلاح نوم',
            'ولایت',
            'زون',
            'ولسوالۍ',
            'کلی',
            'مشخص ځای',
            'مختصات',
            'د منبع کوډ',
            'د منبع شمېره',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 20, 'C' => 22, 'D' => 14,
            'E' => 18, 'F' => 18, 'G' => 18, 'H' => 18,
            'I' => 16, 'J' => 16, 'K' => 16, 'L' => 16,
            'M' => 16, 'N' => 16, 'O' => 18, 'P' => 18,
            'Q' => 16, 'R' => 16, 'S' => 30,
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
