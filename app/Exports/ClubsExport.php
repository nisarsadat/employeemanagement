<?php

namespace App\Exports;

use App\Models\fifthyFormats\Clubs;
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

class ClubsExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'name', 'club_name', 'club_type', 'father_name', 'position', 'phone_number',
            'province', 'zone', 'district', 'specific_place', 'cordinate',
            'am_students', 'pm_students', 'notes',
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

        $query = Clubs::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                       ->orWhere('club_name', 'like', "%{$search}%")
                       ->orWhere('phone_number', 'like', "%{$search}%")
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
            $query->whereNotNull('cordinate')->whereRaw("TRIM(cordinate) != ''");
        } elseif ($cardFilter === 'withPhone') {
            $query->whereNotNull('phone_number')->whereRaw("TRIM(phone_number) != ''");
        } elseif ($cardFilter === 'withResource') {
            $query->whereNotNull('resource_id')->whereRaw("TRIM(resource_id) != ''");
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->name ?? '—',
            $row->club_name ?? '—',
            $row->club_type ?? '—',
            $row->father_name ?? '—',
            $row->position ?? '—',
            $row->phone_number ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->specific_place ?? '—',
            $row->cordinate ?? '—',
            $row->am_students ?? '—',
            $row->pm_students ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->Province_addr ?? '—',
            $row->notes ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'د مسؤل نوم',
            'د کلب نوم',
            'د کلب ډول',
            'د پلار نوم',
            'وظیفه',
            'شماره تماس',
            'ولایت',
            'آمریت',
            'ولسوالۍ',
            'مشخص ځای',
            'مختصات',
            'صباح شاگردان',
            'ماه شاگردان',
            'د منبع کوډ',
            'د منبع شمېره',
            'آدرس',
            'ملاحظات',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 20, 'C' => 20, 'D' => 18,
            'E' => 18, 'F' => 18, 'G' => 16, 'H' => 16,
            'I' => 16, 'J' => 16, 'K' => 18, 'L' => 18,
            'M' => 16, 'N' => 16, 'O' => 16, 'P' => 16,
            'Q' => 20, 'R' => 30,
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
