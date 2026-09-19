<?php

namespace App\Exports;

use App\Exports\Concerns\LocalizesExcelExport;
use App\Models\Meeting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MeetingsExport implements
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
        ?string $dateField = 'start_date',
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
                    'today',
                    'upcoming',
                    'ongoing',
                    'completed',
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
                    'start_date',
                    'end_date',
                    'created_at',
                    'updated_at',
                ],
                true
            )
                ? $dateField
                : 'start_date';

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
            'meeting_title',
            'organ',
            'province',
            'district',
            'specific_place',
            'coordinates',
            'start_date',
            'end_date',
            'total_time',
            'participant',
            'boss_participant',
            'office_employee_count',
            'leader_name',
            'done_actions',
            'mashkok_situations',
            'problems',
            'meeting_supplier',
            'meeting_supplier_phone',
            'meeting_amniyat_responsable',
            'meeting_amniyat_responsable_phone',
            'amniyat_mansobine_count',
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

        $today =
            now()->toDateString();

        $query =
            Meeting::query()
                ->select([
                    'id',
                    'meeting_title',
                    'organ',
                    'province',
                    'district',
                    'specific_place',
                    'coordinates',
                    'start_date',
                    'end_date',
                    'total_time',
                    'participant',
                    'boss_participant',
                    'office_employee_count',
                    'leader_name',
                    'done_actions',
                    'mashkok_situations',
                    'problems',
                    'meeting_supplier',
                    'meeting_supplier_phone',
                    'meeting_amniyat_responsable',
                    'meeting_amniyat_responsable_phone',
                    'amniyat_mansobine_count',
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
                    Builder $sub
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
            $this->stat ===
            'today'
        ) {
            $query->whereDate(
                'start_date',
                $today
            );
        }

        if (
            $this->stat ===
            'upcoming'
        ) {
            $query->whereDate(
                'start_date',
                '>',
                $today
            );
        }

        if (
            $this->stat ===
            'ongoing'
        ) {
            $query
                ->whereDate(
                    'start_date',
                    '<=',
                    $today
                )
                ->whereDate(
                    'end_date',
                    '>=',
                    $today
                );
        }

        if (
            $this->stat ===
            'completed'
        ) {
            $query->whereDate(
                'end_date',
                '<',
                $today
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
            $row->meeting_title ?? '—',
            $row->organ ?? '—',
            $row->province ?? '—',
            $row->district ?? '—',
            $row->specific_place ?? '—',
            $row->coordinates ?? '—',

            $this->formatShamsiDate(
                $row->start_date
            ),

            $this->formatShamsiDate(
                $row->end_date
            ),

            $row->total_time ?? '—',
            $row->participant ?? '—',
            $row->boss_participant ?? '—',
            $row->office_employee_count ?? 0,
            $row->leader_name ?? '—',
            $row->done_actions ?? '—',
            $row->mashkok_situations ?? '—',
            $row->problems ?? '—',
            $row->meeting_supplier ?? '—',
            $row->meeting_supplier_phone ?? '—',
            $row->meeting_amniyat_responsable ?? '—',
            $row->meeting_amniyat_responsable_phone ?? '—',
            $row->amniyat_mansobine_count ?? 0,
            $row->note ?? '—',
        ];
    }

    public function headings(): array
    {
        return [
            $this->headingFor('id', 'آی دی'),
            $this->headingFor('meeting_title', 'عنوان جلسه'),
            $this->headingFor('organ', 'ارگان'),
            $this->headingFor('province', 'ولایت'),
            $this->headingFor('district', 'ولسوالی'),
            $this->headingFor('specific_place', 'محل مشخص'),
            $this->headingFor('coordinates', 'کوردینات'),
            $this->headingFor('start_date', 'تاریخ شروع'),
            $this->headingFor('end_date', 'تاریخ ختم'),
            $this->headingFor('total_time', 'مجموع زمان'),
            $this->headingFor('participant', 'اشتراک کننده گان'),
            $this->headingFor('boss_participant', 'اشتراک کننده مسئول'),
            $this->headingFor('office_employee_count', 'تعداد کارمندان دفتر'),
            $this->headingFor('leader_name', 'نام مسئول'),
            $this->headingFor('done_actions', 'فعالیت های انجام شده'),
            $this->headingFor('mashkok_situations', 'حالات مشکوک'),
            $this->headingFor('problems', 'مشکلات'),
            $this->headingFor('meeting_supplier', 'تهیه کننده جلسه'),
            $this->headingFor('meeting_supplier_phone', 'شماره تماس تهیه کننده'),
            $this->headingFor('meeting_amniyat_responsable', 'مسئول امنیت جلسه'),
            $this->headingFor('meeting_amniyat_responsable_phone', 'شماره تماس مسئول امنیت'),
            $this->headingFor('amniyat_mansobine_count', 'تعداد منسوبین امنیتی'),
            $this->headingFor('note', 'ملاحظات'),
        ];
    }

    private function defaultHeadingsByLocale(): array
    {
        return [
            'fa' => [
                'id' => 'آی دی',
                'meeting_title' => 'عنوان جلسه',
                'organ' => 'ارگان',
                'province' => 'ولایت',
                'district' => 'ولسوالی',
                'specific_place' => 'محل مشخص',
                'coordinates' => 'کوردینات',
                'start_date' => 'تاریخ شروع',
                'end_date' => 'تاریخ ختم',
                'total_time' => 'مجموع زمان',
                'participant' => 'اشتراک کننده گان',
                'boss_participant' => 'اشتراک کننده مسئول',
                'office_employee_count' => 'تعداد کارمندان دفتر',
                'leader_name' => 'نام مسئول',
                'done_actions' => 'فعالیت های انجام شده',
                'mashkok_situations' => 'حالات مشکوک',
                'problems' => 'مشکلات',
                'meeting_supplier' => 'تهیه کننده جلسه',
                'meeting_supplier_phone' => 'شماره تماس تهیه کننده',
                'meeting_amniyat_responsable' => 'مسئول امنیت جلسه',
                'meeting_amniyat_responsable_phone' => 'شماره تماس مسئول امنیت',
                'amniyat_mansobine_count' => 'تعداد منسوبین امنیتی',
                'note' => 'ملاحظات',
            ],
            'ps' => [
                'id' => 'آی‌ډي',
                'meeting_title' => 'مجلس / غونډې عنوان',
                'organ' => 'دائرونکی ارګان',
                'province' => 'ولایت',
                'district' => 'ناحیه/ولسوالي',
                'specific_place' => 'مشخص ځای',
                'coordinates' => 'کوردینات',
                'start_date' => 'د پیل نېټه',
                'end_date' => 'د پای نېټه',
                'total_time' => 'د دوام موده',
                'participant' => 'ګډون کوونکو شمېر',
                'boss_participant' => 'ګډون کوونکو مشرانو شمېر',
                'office_employee_count' => 'د مدیریت خدمتي کارمندانو شمېر',
                'leader_name' => 'د سیسټم نوم',
                'done_actions' => 'ترسره کړې کړنې',
                'mashkok_situations' => 'مشکوک وضعیتونه',
                'problems' => 'ستونزې',
                'meeting_supplier' => 'د غونډې تنظیمونکی اداري واحد',
                'meeting_supplier_phone' => 'د غونډې د منتظم تیلیفون شمېر',
                'meeting_amniyat_responsable' => 'د عمومي امنیت مسؤول',
                'meeting_amniyat_responsable_phone' => 'د عمومي امنیت د مسؤول شمېره',
                'amniyat_mansobine_count' => 'د امنیت تأمینونکو امنیتي منسوبینو شمېر',
                'note' => 'کتنې',
            ],
            'en' => [
                'id' => 'ID',
                'meeting_title' => 'Meeting Title',
                'organ' => 'Organization',
                'province' => 'Province',
                'district' => 'District',
                'specific_place' => 'Specific location',
                'coordinates' => 'Coordinates',
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'total_time' => 'Duration',
                'participant' => 'Number of Participants',
                'boss_participant' => 'Number of Participant Leaders',
                'office_employee_count' => 'Number of Service Staff',
                'leader_name' => 'System Name',
                'done_actions' => 'Actions Taken',
                'mashkok_situations' => 'Suspicious Situations',
                'problems' => 'Problems',
                'meeting_supplier' => 'Meeting Organizer Administrative Unit',
                'meeting_supplier_phone' => 'Meeting Organizer Phone Number',
                'meeting_amniyat_responsable' => 'General Security Responsible',
                'meeting_amniyat_responsable_phone' => 'General Security Responsible Phone Number',
                'amniyat_mansobine_count' => 'Number of Security Personnel',
                'note' => 'Notes',
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 24,
            'C' => 20,
            'D' => 18,
            'E' => 18,
            'F' => 24,
            'G' => 22,
            'H' => 16,
            'I' => 16,
            'J' => 16,
            'K' => 30,
            'L' => 30,
            'M' => 18,
            'N' => 22,
            'O' => 35,
            'P' => 35,
            'Q' => 35,
            'R' => 24,
            'S' => 20,
            'T' => 24,
            'U' => 20,
            'V' => 20,
            'W' => 35,
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

    private function formatShamsiDate(
        $value
    ): string {
        if (!$value) {
            return '—';
        }

        try {
            $date =
                $value instanceof Carbon
                    ? $value
                    : Carbon::parse(
                        $value
                    );

            [
                $jy,
                $jm,
                $jd
            ] =
                $this
                    ->gregorianToJalali(
                        (int) $date
                            ->format('Y'),

                        (int) $date
                            ->format('m'),

                        (int) $date
                            ->format('d')
                    );

            return sprintf(
                '%04d/%02d/%02d',
                $jy,
                $jm,
                $jd
            );
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function gregorianToJalali(
        int $gy,
        int $gm,
        int $gd
    ): array {
        $gdm = [
            0,
            31,
            59,
            90,
            120,
            151,
            181,
            212,
            243,
            273,
            304,
            334,
        ];

        if ($gy > 1600) {
            $jy = 979;
            $gy -= 1600;
        } else {
            $jy = 0;
            $gy -= 621;
        }

        $gy2 =
            $gm > 2
                ? $gy + 1
                : $gy;

        $days =
            365 * $gy
            + intdiv(
                $gy2 + 3,
                4
            )
            - intdiv(
                $gy2 + 99,
                100
            )
            + intdiv(
                $gy2 + 399,
                400
            )
            - 80
            + $gd
            + $gdm[
                $gm - 1
            ];

        $jy +=
            33 *
            intdiv(
                $days,
                12053
            );

        $days %= 12053;

        $jy +=
            4 *
            intdiv(
                $days,
                1461
            );

        $days %= 1461;

        if ($days > 365) {
            $jy +=
                intdiv(
                    $days - 1,
                    365
                );

            $days =
                ($days - 1)
                % 365;
        }

        if ($days < 186) {
            $jm =
                1 +
                intdiv(
                    $days,
                    31
                );

            $jd =
                1 +
                ($days % 31);
        } else {
            $jm =
                7 +
                intdiv(
                    $days - 186,
                    30
                );

            $jd =
                1 +
                (
                    ($days - 186)
                    % 30
                );
        }

        return [
            $jy,
            $jm,
            $jd,
        ];
    }
}