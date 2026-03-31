<?php

namespace App\Exports;

use App\Models\Resource;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;


class ResourcesExport implements FromCollection, WithHeadings, WithEvents
{
    protected ?string $search;
    protected ?string $searchField;
    protected ?int $year;
    protected ?int $month;
    protected ?string $stat;
    protected ?string $dateField;

    public function __construct(
        ?string $search = null,
        ?string $searchField = null,
        ?int $year = null,
        ?int $month = null,
        ?string $stat = 'all',
        ?string $dateField = 'resource_saved_date'
    ) {
        $this->search = trim((string) $search) ?: null;
        $this->searchField = $searchField;
        $this->year = $year;
        $this->month = $month;
        $this->stat = $stat ?: 'all';
        $this->dateField = in_array($dateField, ['resource_saved_date', 'created_at', 'updated_at'], true)
            ? $dateField
            : 'resource_saved_date';
    }

    private function searchableColumns(): array
    {
        return [
            'letry_format_name',
            'resedint_code',
            'code_number',
            'name',
            'father_name',
            'grand_father_name',
            'nick_name',
            'tazkira',
            'main_province',
            'main_district',
            'main_village',
            'now_province',
            'now_district',
            'now_village',
            'now_specific_place',
            'now_street_number',
            'now_home_number',
            'old_job',
            'now_job',
            'job_place',
            'zone',
            'hawza',
            'specefic_place',
            'coordinates',
            'activity',
            'halat_madani',
            'age',
            'elmi_level',
            'faculty',
            'specific_skills',
            'resource_saved_date',
            'resource_activity_type',
            'resource_cover',
            'language',
            'nation',
            'blood_group',
            'train',
            'project',
            'phone_number',
            'phone_number_1',
            'phone_number_2',
            'Province_addr',
            'trust_percentage',
            'note',
        ];
    }

    private function resolveSearchField(?string $field): ?string
    {
        $field = trim((string) $field);
        return in_array($field, $this->searchableColumns(), true) ? $field : null;
    }

    public function collection()
    {
        $today = now()->toDateString();
        $searchField = $this->resolveSearchField($this->searchField);
        $stat = in_array($this->stat, ['all', 'today', 'active', 'inactive'], true)
            ? $this->stat
            : 'all';

        $rows = Resource::query()
            ->with(['officeEmployee:id,name,code_number,father_name'])
            ->withSum('finances as payed_money', 'money_amount_number')
            ->withCount([
                'events',
                'informations',
                'khayratReports',
            ])
            ->when($this->year !== null, function (Builder $q) {
                $q->whereNotNull($this->dateField)
                    ->whereYear($this->dateField, $this->year);
            })
            ->when($this->month !== null && $this->month >= 1 && $this->month <= 12, function (Builder $q) {
                $q->whereNotNull($this->dateField)
                    ->whereMonth($this->dateField, $this->month);
            })
            ->when($this->search, function (Builder $q) use ($searchField) {
                if ($searchField) {
                    $q->where($searchField, 'like', "%{$this->search}%");
                } else {
                    $q->where(function ($sub) {
                        foreach ($this->searchableColumns() as $index => $column) {
                            if ($index === 0) {
                                $sub->where($column, 'like', "%{$this->search}%");
                            } else {
                                $sub->orWhere($column, 'like', "%{$this->search}%");
                            }
                        }
                    });
                }
            })
            ->when($stat === 'today', function (Builder $q) use ($today) {
                $q->whereNotNull($this->dateField)
                    ->whereDate($this->dateField, $today);
            })
            ->when($stat === 'active', function (Builder $q) {
                $q->where('activity', 'فعال');
            })
            ->when($stat === 'inactive', function (Builder $q) {
                $q->where('activity', 'غیر فعال');
            })
            ->latest('id')
            ->get();

        return $rows->map(function ($row) {
            return [
                'id' => $row->id,
                'payed_money' => $row->payed_money,
                'events_count' => $row->events_count,
                'informations_count' => $row->informations_count,
                'khayrat_reports_count' => $row->khayrat_reports_count,
                'letry_format_name' => $row->letry_format_name,
                'office_employee_code' => $row->officeEmployee?->code_number,
                'office_employee_name' => $row->officeEmployee?->name,
                'resedint_code' => $row->resedint_code,
                'code_number' => $row->code_number,
                'name' => $row->name,
                'father_name' => $row->father_name,
                'grand_father_name' => $row->grand_father_name,
                'nick_name' => $row->nick_name,
                'tazkira' => $row->tazkira,
                'main_province' => $row->main_province,
                'main_district' => $row->main_district,
                'main_village' => $row->main_village,
                'now_province' => $row->now_province,
                'now_district' => $row->now_district,
                'now_village' => $row->now_village,
                'now_specific_place' => $row->now_specific_place,
                'now_street_number' => $row->now_street_number,
                'now_home_number' => $row->now_home_number,
                'old_job' => $row->old_job,
                'now_job' => $row->now_job,
                'job_place' => $row->job_place,
                'zone' => $row->zone,
                'hawza' => $row->hawza,
                'specefic_place' => $row->specefic_place,
                'coordinates' => $row->coordinates,
                'activity' => $row->activity,
                'halat_madani' => $row->halat_madani,
                'age' => $row->age,
                'elmi_level' => $row->elmi_level,
                'faculty' => $row->faculty,
                'specific_skills' => $row->specific_skills,
                'resource_saved_date' => $row->resource_saved_date,
                'resource_activity_type' => $row->resource_activity_type,
                'resource_cover' => $row->resource_cover,
                'language' => $row->language,
                'nation' => $row->nation,
                'phone_number' => $row->phone_number,
                'phone_number_1' => $row->phone_number_1,
                'phone_number_2' => $row->phone_number_2,
                'Province_addr' => $row->Province_addr,
                'blood_group' => $row->blood_group,
                'train' => $row->train,
                'project' => $row->project,
                'trust_percentage' => $row->trust_percentage,
                'note' => $row->note,
                'created_at' => optional($row->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => optional($row->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'آی ډي',
            'ورکړل شوې پیسې',
            'د پېښو شمېر',
            'د معلوماتو شمېر',
            'د خیرات راپورونو شمېر',
            'د ۵۶ فارمټ نوم',
            'د کارمند کوډ',
            'د کارمند نوم',
            'رزیدنت کوډ',
            'کوډ نمبر',
            'نوم',
            'د پلار نوم',
            'د نیکه نوم',
            'مستعار نوم',
            'تذکره',
            'اصلي ولایت',
            'اصلي ولسوالۍ',
            'اصلي کلی',
            'اوسنی ولایت',
            'اوسنی ولسوالۍ',
            'اوسنی کلی',
            'اوسنی مشخص ځای',
            'د سړک نمبر',
            'د کور نمبر',
            'پخوانۍ دنده',
            'اوسنۍ دنده',
            'د کار ځای',
            'زون',
            'حوزه',
            'مشخص ځای',
            'کوردینات',
            'فعالیت',
            'مدني حالت',
            'عمر',
            'تعلیم درجه',
            'فاکولته',
            'ځانګړي مهارتونه',
            'د ثبت نېټه',
            'د فعالیت ډول',
            'د منبع پوښښ',
            'ژبه',
            'قوم',
            'د تلیفون نمبر',
            'تلیفون نمبر ۱',
            'تلیفون نمبر ۲',
            'پته (ولایت)',
            'د وینې ګروپ',
            'روزنه',
            'پروژه',
            'د باور فیصدی',
            'یادښت',
            'جوړ شوی',
            'تازه شوی',
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // ✅ Set RTL direction
            $event->sheet->getDelegate()->getStyle('A1:ZZ1')->getFont()->setBold(true);

                // Optional: align all text to right
                $event->sheet->getDelegate()->getStyle('A1:ZZ1000')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }
}