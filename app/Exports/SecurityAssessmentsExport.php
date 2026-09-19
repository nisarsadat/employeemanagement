<?php

namespace App\Exports;

use App\Exports\Concerns\LocalizesExcelExport;
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
    use LocalizesExcelExport;

    protected ?string $search;
    protected ?string $searchField;
    protected ?string $searchValue;
    protected string $stat;
    protected ?int $year;
    protected ?int $month;
    protected string $dateField;
    protected ?string $date;
    protected ?string $dateFrom;
    protected ?string $dateTo;
    protected string $locale;
    protected array $columnLabels;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?string $searchValue = null,
        ?string $stat = 'all',
        ?int $year = null,
        ?int $month = null,
        ?string $dateField = 'created_at',
        ?string $date = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $locale = 'fa',
        array $columnLabels = []
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

        $this->stat =
            in_array(
                $stat,
                [
                    'all',
                    'north',
                    'south',
                    'east',
                    'west',
                ],
                true
            )
                ? $stat
                : 'all';

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

        $this->locale =
            $this->normalizeLocale(
                $locale
            );

        $this->columnLabels =
            $columnLabels;
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

        if (
            $this->stat !==
            'all'
        ) {
            $query->where(
                'direction',
                $this->stat
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
            $this->headingFor('id', 'آی دی'),
            $this->headingFor('place_name', 'نام محل'),
            $this->headingFor('responsible_owner_name', 'نام مسؤول / مالک'),
            $this->headingFor('province', 'ولایت'),
            $this->headingFor('district', 'ولسوالی'),
            $this->headingFor('specific_place', 'محل مشخص'),
            $this->headingFor('coordinates', 'کوردینات'),
            $this->headingFor('direction', 'سمت'),
            $this->headingFor('daily_visitors_count', 'تعداد روزانه مراجعین'),
            $this->headingFor('risk_places', 'محلات آسیب‌رسان'),
            $this->headingFor('vulnerable_places', 'محلات آسیب‌پذیر'),
            $this->headingFor('security_gaps', 'خلأهای امنیتی'),
            $this->headingFor('weak_points', 'نقاط ضعف'),
            $this->headingFor('security_incidents', 'حوادث امنیتی رخ‌داده'),
            $this->headingFor('entrance_gate', 'دروازه ورودی'),
            $this->headingFor('exit_gate', 'دروازه خروجی'),
            $this->headingFor('emergency_gate', 'دروازه اضطراری'),
            $this->headingFor('secondary_gate', 'دروازه فرعی'),
            $this->headingFor('physical_check_status', 'وضعیت بررسی فزیکی'),
            $this->headingFor('technical_check_status', 'وضعیت بررسی تخنیکی'),
            $this->headingFor('security_cameras', 'کمره‌های امنیتی'),
            $this->headingFor('guards_count', 'تعداد محافظین'),
            $this->headingFor('active_towers_count', 'تعداد برج‌های فعال'),
            $this->headingFor('active_guard_posts_count', 'تعداد قرارگاه‌های فعال'),
            $this->headingFor('security_responsible_name', 'نام مسؤول امنیتی'),
            $this->headingFor('security_responsible_phone', 'شماره مسؤول امنیتی'),
            $this->headingFor('other_problems', 'سایر مشکلات'),
            $this->headingFor('note', 'ملاحظات'),
        ];
    }

    private function defaultHeadingsByLocale(): array
    {
        return [
            'fa' => [
                'id' => 'آی دی',
                'place_name' => 'نام محل',
                'responsible_owner_name' => 'نام مسؤول / مالک',
                'province' => 'ولایت',
                'district' => 'ناحیه / ولسوالی',
                'specific_place' => 'محل مشخص',
                'coordinates' => 'کوردینات',
                'direction' => 'سمت',
                'daily_visitors_count' => 'تعداد روزانه مراجعین',
                'risk_places' => 'محلات آسیب‌رسان',
                'vulnerable_places' => 'محلات آسیب‌پذیر',
                'security_gaps' => 'خلأهای امنیتی',
                'weak_points' => 'نقاط ضعف',
                'security_incidents' => 'حوادث امنیتی رخ‌داده',
                'entrance_gate' => 'دروازه ورودی',
                'exit_gate' => 'دروازه خروجی',
                'emergency_gate' => 'دروازه اضطراری',
                'secondary_gate' => 'دروازه فرعی',
                'physical_check_status' => 'وضعیت بررسی فزیکی',
                'technical_check_status' => 'وضعیت بررسی تخنیکی',
                'security_cameras' => 'کمره‌های امنیتی',
                'guards_count' => 'تعداد محافظین',
                'active_towers_count' => 'تعداد برج‌های فعال',
                'active_guard_posts_count' => 'تعداد قرارگاه‌های فعال',
                'security_responsible_name' => 'نام مسؤول امنیتی',
                'security_responsible_phone' => 'شماره مسؤول امنیتی',
                'other_problems' => 'سایر مشکلات',
                'note' => 'ملاحظات',
            ],
            'ps' => [
                'id' => 'آی‌ډي',
                'place_name' => 'د ځای نوم',
                'responsible_owner_name' => 'د مسؤول / مالک نوم',
                'province' => 'ولایت',
                'district' => 'ناحیه / ولسوالي',
                'specific_place' => 'مشخص ځای',
                'coordinates' => 'کوردینات',
                'direction' => 'سمت',
                'daily_visitors_count' => 'د مراجعینو ورځنی تعداد',
                'risk_places' => 'د زیان رسوونکو ځایونو',
                'vulnerable_places' => 'د زیان منونکو ځایونو',
                'security_gaps' => 'امنیتي خلاوې',
                'weak_points' => 'د ضعف ټکي',
                'security_incidents' => 'ترسره شوې امنیتي پېښې',
                'entrance_gate' => 'دخولي دروازه',
                'exit_gate' => 'خروجي دروازه',
                'emergency_gate' => 'اضطراري دروازه',
                'secondary_gate' => 'فرعي دروازه',
                'physical_check_status' => 'د فزیکي چک وضعیت',
                'technical_check_status' => 'د تخنیکي چک وضعیت',
                'security_cameras' => 'امنیتي کمرې',
                'guards_count' => 'د محافظینو شمېر',
                'active_towers_count' => 'د فعاله ټاورونو شمېر',
                'active_guard_posts_count' => 'د فعاله قراوولونو شمېر',
                'security_responsible_name' => 'د امنیتي مسؤول نوم',
                'security_responsible_phone' => 'د امنیتي مسؤول شمېره',
                'other_problems' => 'نورې ستونزې',
                'note' => 'ملاحظات',
            ],
            'en' => [
                'id' => 'ID',
                'place_name' => 'Place Name',
                'responsible_owner_name' => 'Responsible / Owner Name',
                'province' => 'Province',
                'district' => 'District',
                'specific_place' => 'Specific location',
                'coordinates' => 'Coordinates',
                'direction' => 'Direction',
                'daily_visitors_count' => 'Daily Number of Visitors',
                'risk_places' => 'Risk Locations',
                'vulnerable_places' => 'Vulnerable Locations',
                'security_gaps' => 'Security Gaps',
                'weak_points' => 'Weak Points',
                'security_incidents' => 'Security Incidents',
                'entrance_gate' => 'Entrance Gate',
                'exit_gate' => 'Exit Gate',
                'emergency_gate' => 'Emergency Gate',
                'secondary_gate' => 'Secondary Gate',
                'physical_check_status' => 'Physical Check Status',
                'technical_check_status' => 'Technical Check Status',
                'security_cameras' => 'Security Cameras',
                'guards_count' => 'Number of Guards',
                'active_towers_count' => 'Number of Active Towers',
                'active_guard_posts_count' => 'Number of Active Guard Posts',
                'security_responsible_name' => 'Security Responsible Name',
                'security_responsible_phone' => 'Security Responsible Phone Number',
                'other_problems' => 'Other Problems',
                'note' => 'Notes',
            ],
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
                            $this->isRtl()
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
                            $this
                                ->horizontalAlignment()
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
        $maps = [
            'fa' => [
                'north' => 'شمال',
                'south' => 'جنوب',
                'east' => 'شرق',
                'west' => 'غرب',
            ],
            'ps' => [
                'north' => 'شمال',
                'south' => 'جنوب',
                'east' => 'ختیځ',
                'west' => 'لویدیځ',
            ],
            'en' => [
                'north' => 'North',
                'south' => 'South',
                'east' => 'East',
                'west' => 'West',
            ],
        ];

        return $maps[$this->locale][$direction] ?? '—';
    }
}
