<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKhayratReportRequest;
use App\Http\Requests\UpdateKhayratReportRequest;
use App\Http\Resources\KhayratReportResource;
use App\Models\KhayratReport;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class KhayratReportController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('perPage', 15);
        $search = trim((string) $request->input('search', ''));

        $query = KhayratReport::query()
            ->search($search)
            ->with([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,code_number',
            ])
            ->latest('id');

        return KhayratReportResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreKhayratReportRequest $request)
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $item = KhayratReport::create($request->validated());

            $item->load([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,code_number',
            ]);

            NotificationService::record(
                $user,
                module: 'د حاضری راپور',
                action: 'created',
                title: 'د حاضری راپور ثبت شوو',
                message: 'کوډ: ' . ($item->id ?? ''),
                routeName: 'KhayratReportsIndex',
                routeParams: ['id' => $item->id],
                level: 'success',
                changes: null
            );

            return new KhayratReportResource($item);
        });
    }

    public function show(KhayratReport $khayratReport)
    {
        $khayratReport->load([
            'resource:id,name,code_number,phone_number',
            'officeEmployee:id,name,code_number',
        ]);

        return new KhayratReportResource($khayratReport);
    }

    public function update(UpdateKhayratReportRequest $request, KhayratReport $khayratReport)
    {
        $user = $request->user();

        return DB::transaction(function () use ($request, $khayratReport, $user) {
            $original = $khayratReport->getOriginal();
            $validated = $request->validated();
            $changes = [];

            foreach ($validated as $field => $newValue) {
                if (array_key_exists($field, $original)) {
                    $oldValue = $original[$field];

                    if ($oldValue != $newValue) {
                        $changes[$field] = [
                            'old' => $oldValue,
                            'new' => $newValue,
                        ];
                    }
                }
            }

            $khayratReport->update($validated);

            $khayratReport->load([
                'resource:id,name,code_number,phone_number',
                'officeEmployee:id,name,code_number',
            ]);

            NotificationService::record(
                $user,
                module: 'د حاضری راپور',
                action: 'updated',
                title: 'د حاضری راپور اصلاح شوو',
                message: 'کوډ: ' . ($khayratReport->id ?? ''),
                routeName: 'KhayratReportsIndex',
                routeParams: ['id' => $khayratReport->id],
                level: 'info',
                changes: !empty($changes) ? $changes : null
            );

            return new KhayratReportResource($khayratReport);
        });
    }

    public function destroy(Request $request, KhayratReport $khayratReport)
    {
        $user = $request->user();

        return DB::transaction(function () use ($khayratReport, $user) {
            $id = $khayratReport->id;

            $khayratReport->delete();

            NotificationService::record(
                $user,
                module: 'د حاضری راپور',
                action: 'deleted',
                title: 'د حاضری راپور حذف شوو',
                message: 'کوډ: ' . ($id ?? ''),
                routeName: null,
                routeParams: [],
                level: 'warning',
                changes: null
            );

            return response()->json([
                'message' => 'Deleted',
            ]);
        });
    }
    private function gregorianToJalali(int $gy, int $gm, int $gd): array
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];

    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }

    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;

    $days = (365 * $gy)
        + intdiv(($gy2 + 3), 4)
        - intdiv(($gy2 + 99), 100)
        + intdiv(($gy2 + 399), 400)
        - 80
        + $gd
        + $g_d_m[$gm - 1];

    $jy += 33 * intdiv($days, 12053);
    $days %= 12053;

    $jy += 4 * intdiv($days, 1461);
    $days %= 1461;

    if ($days > 365) {
        $jy += intdiv(($days - 1), 365);
        $days = ($days - 1) % 365;
    }

    if ($days < 186) {
        $jm = 1 + intdiv($days, 31);
        $jd = 1 + ($days % 31);
    } else {
        $jm = 7 + intdiv(($days - 186), 30);
        $jd = 1 + (($days - 186) % 30);
    }

    return [$jy, $jm, $jd];
}

private function formatPrintDateToShamsi($value): string
{
    if ($value === null || trim((string) $value) === '') {
        return '';
    }

    $raw = trim((string) $value);

    // Only convert real date-looking values.
    if (!preg_match('/^\d{4}[\/-]\d{1,2}[\/-]\d{1,2}/', $raw)) {
        return $raw;
    }

    try {
        $carbon = Carbon::parse($raw);

        [$jy, $jm, $jd] = $this->gregorianToJalali(
            (int) $carbon->format('Y'),
            (int) $carbon->format('m'),
            (int) $carbon->format('d')
        );

        return sprintf('%04d/%02d/%02d', $jy, $jm, $jd);
    } catch (\Throwable $e) {
        $raw = preg_replace('/\s+\d{2}:\d{2}:\d{2}$/', '', $raw);
        $raw = preg_replace('/T\d{2}:\d{2}:\d{2}(\.\d+)?Z?$/', '', $raw);

        return str_replace('-', '/', trim($raw));
    }
}

public function printView(Request $request)
{
    $search = trim((string) $request->input('search', ''));

    $query = KhayratReport::query()
        ->search($search)
        ->with([
            'resource:id,name,code_number,phone_number',
            'officeEmployee:id,name,code_number',
        ])
        ->latest('id');

    $rows = $query
        ->get()
        ->map(function ($row) {
            $row->resource_display =
                $row->resource?->code_number
                ?? $row->resource_id
                ?? '-';

            $row->office_employee_display =
                $row->officeEmployee?->code_number
                ?? $row->office_employee_id
                ?? '-';

            if ($row->saved_date !== null && trim((string) $row->saved_date) !== '') {
                $row->saved_date = $this->formatPrintDateToShamsi($row->saved_date);
            }

            if ($row->created_at !== null && trim((string) $row->created_at) !== '') {
                $row->created_at = $this->formatPrintDateToShamsi($row->created_at);
            }

            if ($row->updated_at !== null && trim((string) $row->updated_at) !== '') {
                $row->updated_at = $this->formatPrintDateToShamsi($row->updated_at);
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ گزارش خیرات');

    return response()->view('prints.table-print', [
        'title'           => $title,
        'rows'            => $rows,
        'selectedColumns' => $selectedColumns,
        'columnLabels'    => $columnLabels,
        'printLabels'     => $printLabels,
        'locale'          => $locale,
    ]);
}
}