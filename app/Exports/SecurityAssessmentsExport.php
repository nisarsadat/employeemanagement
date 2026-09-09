<?php

namespace App\Exports;

use App\Models\SecurityAssessment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SecurityAssessmentsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithEvents,
    WithColumnWidths
{
    protected ?string $search;
    protected ?string $searchField;
    protected ?string $searchValue;
    protected ?int $year;
    protected ?int $month;
    protected string $dateField;
    protected ?string $date;
    protected ?string $dateFrom;
    protected ?string $dateTo;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?string $searchValue = null,
        ?int $year = null,
        ?int $month = null,
        ?string $dateField = 'created_at',
        ?string $date = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->search =
            trim(
                (string) $search
            ) ?: null;

        $this->searchField =
            trim(
                (string) $searchField
            ) ?: null;

        $this->searchValue =
            trim(
                (string) $searchValue
            ) ?: null;

        $this->year =
            $year;

        $this->month =
            $month;

        $this->dateField =
            in_array(
                $dateField,
                [
                    'created_at',
                    'updated_at',
                ],
                true
            )
                ? $dateField
                : 'created_at';

        $this->date =
            $date;

        $this->dateFrom =
            $dateFrom;

        $this->dateTo =
            $dateTo;
    }

    private function searchableColumns(): array
    {
        return [
            'place_name',
            'responsible_owner_name',
            'province',
            'district',
            'specific_place',
            'coordinates',
            'direction',
            'daily_visitors_count',
            'risk_places',
            'vulnerable_places',
            'security_gaps',
            'weak_points',
            'security_incidents',
            'entrance_gate',
            'exit_gate',
            'emergency_gate',
            'secondary_gate',
            'physical_check_status',
            'technical_check_status',
            'security_cameras',
            'guards_count',
            'active_towers_count',
            'active_guard_posts_count',
            'security_responsible_name',
            'security_responsible_phone',
            'other_problems',
            'note',
        ];
    }

    private function resolveSearchField(
        ?string $field
    ): ?string {
        return in_array(
            $field,
            $this->searchableColumns(),
            true
        )
            ? $field
            : null;
    }

    public function query()
    {
        $searchField =
            $this->resolveSearchField(
                $this->searchField
            );

        $query =
            SecurityAssessment::query()
                ->select([
                    'id',
                    'place_name',
                    'responsible_owner_name',
                    'province',
                    'district',
                    'specific_place',
                    'coordinates',
                    'direction',
                    'daily_visitors_count',
                    'risk_places',
                    'vulnerable_places',
                    'security_gaps',
                    'weak_points',
                    'security_incidents',
                    'entrance_gate',
                    'exit_gate',
                    'emergency_gate',
                    'secondary_gate',
                    'physical_check_status',
                    'technical_check_status',
                    'security_cameras',
                    'guards_count',
                    'active_towers_count',
                    'active_guard_posts_count',
                    'security_responsible_name',
                    'security_responsible_phone',
                    'other_problems',
                    'note',
                    'created_at',
                    'updated_at',
                ]);

        if ($searchField) {
            $value =
                $this->searchValue
                ?: $this->search;

            if ($value) {
                $query->where(
                    $searchField,
                    'like',
                    '%' . $value . '%'
                );
            }
        } elseif ($this->search) {
            $query->where(
                function (
                    $sub
                ) {
                    foreach (
                        $this->searchableColumns()
                        as $index =>
                            $column
                    ) {
                        if (
                            $index === 0
                        ) {
                            $sub->where(
                                $column,
                                'like',
                                '%' .
                                $this->search .
                                '%'
                            );
                        } else {
                            $sub->orWhere(
                                $column,
                                'like',
                                '%' .
                                $this->search .
                                '%'
                            );
                        }
                    }
                }
            );
        }

        if ($this->year) {
            $query->whereYear(
                $this->dateField,
                $this->year
            );
        }

        if ($this->month) {
            $query->whereMonth(
                $this->dateField,
                $this->month
            );
        }

        if ($this->date) {
            $query->whereDate(
                $this->dateField,
                $this->date
            );
        }

        if ($this->dateFrom) {
            $query->whereDate(
                $this->dateField,
                '>=',
                $this->dateFrom
            );
        }

        if ($this->dateTo) {
            $query->whereDate(
                $this->dateField,
                '<=',
                $this->dateTo
            );
        }

        return $query->latest(
            'id'
        );
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->place_name ?? '—',
            $row->responsible_owner_name ?? '—',
            $row->province ?? '—',
            $row->district ?? '—',
            $row->specific_place ?? '—',
            $row->coordinates ?? '—',
            $this->translateDirection($row->direction),
            $row->daily_visitors_count ?? 0,
            $row->risk_places ?? '—',
            $row->vulnerable_places ?? '—',
            $row->security_gaps ?? '—',
            $row->weak_points ?? '—',
            $row->security_incidents ?? '—',
            $row->entrance_gate ?? '—',
            $row->exit_gate ?? '—',
            $row->emergency_gate ?? '—',
            $row->secondary_gate ?? '—',
            $row->physical_check_status ?? '—',
            $row->technical_check_status ?? '—',
            $row->security_cameras ?? '—',
            $row->guards_count ?? 0,
            $row->active_towers_count ?? 0,
            $row->active_guard_posts_count ?? 0,
            $row->security_responsible_name ?? '—',
            $row->security_responsible_phone ?? '—',
            $row->other_problems ?? '—',
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            'آی دی',
            'نام محل',
            'نام مسؤول / مالک',
            'ولایت',
            'ولسوالی',
            'محل مشخص',
            'کوردینات',
            'سمت',
            'تعداد روزانه مراجعین',
            'محلات آسیب‌رسان',
            'محلات آسیب‌پذیر',
            'خلأهای امنیتی',
            'نقاط ضعف',
            'حوادث امنیتی رخ‌داده',
            'دروازه ورودی',
            'دروازه خروجی',
            'دروازه اضطراری',
            'دروازه فرعی',
            'وضعیت بررسی فزیکی',
            'وضعیت بررسی تخنیکی',
            'کمره‌های امنیتی',
            'تعداد محافظین',
            'تعداد برج‌های فعال',
            'تعداد قرارگاه‌های فعال',
            'نام مسؤول امنیتی',
            'شماره مسؤول امنیتی',
            'سایر مشکلات',
            'ملاحظات',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 26,
            'C' => 26,
            'D' => 18,
            'E' => 18,
            'F' => 24,
            'G' => 22,
            'H' => 14,
            'I' => 22,
            'J' => 35,
            'K' => 35,
            'L' => 35,
            'M' => 35,
            'N' => 35,
            'O' => 22,
            'P' => 22,
            'Q' => 22,
            'R' => 22,
            'S' => 24,
            'T' => 24,
            'U' => 22,
            'V' => 18,
            'W' => 20,
            'X' => 24,
            'Y' => 26,
            'Z' => 22,
            'AA' => 35,
            'AB' => 35,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (
                    AfterSheet $event
                ) {
                    $sheet =
                        $event->sheet
                            ->getDelegate();

                    $highestRow =
                        $sheet
                            ->getHighestRow();

                    $highestColumn =
                        $sheet
                            ->getHighestColumn();

                    $fullRange =
                        'A1:' .
                        $highestColumn .
                        $highestRow;

                    $headerRange =
                        'A1:' .
                        $highestColumn .
                        '1';

                    $sheet
                        ->setRightToLeft(
                            true
                        );

                    $sheet
                        ->freezePane(
                            'A2'
                        );

                    $sheet
                        ->getStyle(
                            $headerRange
                        )
                        ->getFont()
                        ->setBold(
                            true
                        );

                    $sheet
                        ->getStyle(
                            $headerRange
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::
                                HORIZONTAL_CENTER
                        )
                        ->setVertical(
                            Alignment::
                                VERTICAL_CENTER
                        );

                    $sheet
                        ->getStyle(
                            $headerRange
                        )
                        ->getFill()
                        ->setFillType(
                            Fill::
                                FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB(
                            'FFEFEFEF'
                        );

                    $sheet
                        ->getStyle(
                            $fullRange
                        )
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::
                                HORIZONTAL_RIGHT
                        )
                        ->setVertical(
                            Alignment::
                                VERTICAL_CENTER
                        )
                        ->setWrapText(
                            true
                        );

                    $sheet
                        ->getRowDimension(
                            1
                        )
                        ->setRowHeight(
                            24
                        );

                    $sheet
                        ->setAutoFilter(
                            $headerRange
                        );
                },
        ];
    }

    private function translateDirection(
        ?string $direction
    ): string {
        return match ($direction) {
            'north' => 'شمال',
            'south' => 'جنوب',
            'east' => 'شرق',
            'west' => 'غرب',
            default => '—',
        };
    }
}
