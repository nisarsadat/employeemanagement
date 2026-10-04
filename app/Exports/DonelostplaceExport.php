<?php

namespace App\Exports;

use App\Models\fifthyformatfour\Donelostplace;
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

class DonelostplaceExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'place_name',
            'lost_person_name','lost_person_f_name','lost_person_phone','lost_person_job',
            'main_province','main_district','main_village',
            'now_province','now_district','now_village',
            'province','zone','district','village','specific_place','coordinates',
            'lost_quantity','note',
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

        $query = Donelostplace::query()
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where($searchField, 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('place_name', 'like', "%{$search}%")
                       ->orWhere('lost_person_name', 'like', "%{$search}%")
                       ->orWhere('lost_person_phone', 'like', "%{$search}%")
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
        } elseif ($cardFilter === 'withPerson') {
            $query->whereNotNull('lost_person_name')->whereRaw("TRIM(lost_person_name) != ''");
        } elseif ($cardFilter === 'withResource') {
            $query->whereNotNull('resource_id');
        }

        return $query->orderByDesc('id');
    }

    public function map($row): array
    {
        return [
            ++$this->rowNumber,
            $row->place_name ?? '—',
            $row->lost_person_name ?? '—',
            $row->lost_person_f_name ?? '—',
            $row->lost_person_job ?? '—',
            $row->lost_person_phone ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->province ?? '—',
            $row->zone ?? '—',
            $row->district ?? '—',
            $row->village ?? '—',
            $row->specific_place ?? '—',
            $row->coordinates ?? '—',
            $row->lost_quantity ?? '—',
            $row->resource?->code_number ?? $row->resource_id ?? '—',
            $row->resource?->phone_number ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'د اختطاف ساحې نوم',
            'د ګم شوی/موميړی شخص نوم',
            'د پلار نوم',
            'دنده',
            'د اړیکه شمېره',
            'اصلي ولایت',
            'اصلي ولسوالۍ',
            'اصلي کلي',
            'اوسنی ولایت',
            'اوسنی ولسوالۍ',
            'اوسنی کلي',
            'ولایت',
            'آمریت',
            'ولسوالۍ',
            'کلی',
            'مشخص ځای',
            'مختصات',
            'د مواردو شماره',
            'د منبع کوډ',
            'د منبع شمېره',
            'یادښت',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, 'B' => 25, 'C' => 25, 'D' => 18,
            'E' => 16, 'F' => 18, 'G' => 16, 'H' => 16,
            'I' => 16, 'J' => 16, 'K' => 16, 'L' => 16,
            'M' => 16, 'N' => 16, 'O' => 16, 'P' => 16,
            'Q' => 18, 'R' => 18, 'S' => 16, 'T' => 16,
            'U' => 16, 'V' => 30,
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
