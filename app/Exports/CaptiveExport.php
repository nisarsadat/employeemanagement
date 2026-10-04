<?php

namespace App\Exports;

use App\Models\fifthyFormats\Captive;
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

class CaptiveExport implements FromQuery, WithHeadings, WithMapping, WithEvents, WithColumnWidths, ShouldQueue
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
            'name', 'nick_name', 'father_name',
            'main_province', 'main_district', 'main_village',
            'now_province', 'now_zone', 'now_district', 'now_village',
            'specific_place', 'street_number', 'home_number',
            'specific_form', 'band_distince', 'now_activity',
            'phone_number', 'note',
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

        $query = Captive::query()
            ->leftJoin('resources', 'captives.resource_id', '=', 'resources.id')
            ->select([
                'captives.*',
                'resources.code_number as resource_code',
                'resources.phone_number as resource_phone_number',
            ])
            ->with(['resource:id,name,code_number,phone_number'])
            ->when($searchValue !== null && $searchValue !== '' && $searchField, function ($q) use ($searchField, $searchValue) {
                $q->where("captives.{$searchField}", 'like', "%{$searchValue}%");
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('captives.name', 'like', "%{$search}%")
                       ->orWhere('captives.father_name', 'like', "%{$search}%")
                       ->orWhere('captives.nick_name', 'like', "%{$search}%")
                       ->orWhere('captives.note', 'like', "%{$search}%")
                       ->orWhere('captives.phone_number', 'like', "%{$search}%")
                       ->orWhereHas('resource', function ($sub) use ($search) {
                           $sub->where('code_number', 'like', "%{$search}%");
                       });
                });
            });

        if (!empty($this->filters['resource_id'])) {
            $query->where('captives.resource_id', $this->filters['resource_id']);
        }

        return $query->orderByDesc('captives.id');
    }

    public function map($row): array
    {
        $resourceCode = $row->resource?->code_number ?? $row->resource_code ?? '—';
        $resourcePhone = $row->resource?->phone_number ?? $row->resource_phone_number ?? '—';

        return [
            ++$this->rowNumber,
            $row->nick_name ?? '—',
            $row->name ?? '—',
            $row->father_name ?? '—',
            $row->main_province ?? '—',
            $row->main_district ?? '—',
            $row->main_village ?? '—',
            $row->now_province ?? '—',
            $row->now_zone ?? '—',
            $row->now_district ?? '—',
            $row->now_village ?? '—',
            $row->specific_place ?? '—',
            $row->street_number ?? '—',
            $row->home_number ?? '—',
            $row->specific_form ?? '—',
            $row->band_distince ?? '—',
            $row->now_activity ?? '—',
            $row->phone_number ?? '—',
            $resourceCode,
            $resourcePhone,
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'شمېره',
            'د تخلص نوم',
            'نوم',
            'د پلار نوم',
            'اصلي ولایت',
            'اصلي ولسوالۍ',
            'اصلي کلی',
            'اوسنی ولایت',
            'اوسنی آمریت',
            'اوسنۍ ولسوالۍ',
            'اوسنی کلی',
            'مشخص ځای',
            'د سړک شمېره',
            'د کور شمېره',
            '特殊 فورم',
            'د بانډ فاصله',
            'اوسنۍ فعالیت',
            'د تلیفون شمېره',
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
            'M' => 14, 'N' => 14, 'O' => 18, 'P' => 14,
            'Q' => 18, 'R' => 16, 'S' => 16, 'T' => 16,
            'U' => 30,
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
