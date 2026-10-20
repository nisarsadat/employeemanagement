<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutgoingIncomingRequest;
use App\Http\Requests\UpdateOutgoingIncomingRequest;
use App\Http\Resources\OutgoingIncomingResource;
use App\Models\OutgoingIncoming;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Services\NotificationService; // 🔔 notifications
use Carbon\Carbon;
class OutgoingIncomingController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('perPage', 25);
        $search  = $request->get('search');

        $query = OutgoingIncoming::query()
            ->search($search)
            ->latest('id');

        return OutgoingIncomingResource::collection(
            $query->paginate($perPage)
        );
    }

    public function store(StoreOutgoingIncomingRequest $request)
    {
        $user = $request->user(); // 🔔 who did this

        try {
            return DB::transaction(function () use ($request, $user) {
                $row = OutgoingIncoming::create($request->validated());

                // 🔔 Notification: CREATED (a notification failure must never break the save)
                try {
                    NotificationService::record(
                        $user,
                        module: 'صادره/وارده',
                        action: 'created',
                        title: 'نوی ریکارډ ثبت شو',
                        message: 'مرجع: ' . ($row->marja ?? ''),
                        routeName: 'outgoing-incomings.show',
                        routeParams: ['outgoing_incoming' => $row->id],
                        level: 'success',
                        changes: null
                    );
                } catch (\Throwable $notifyError) {
                    report($notifyError);
                }

                return OutgoingIncomingResource::make($row);
            });
        } catch (QueryException $e) {
            // ✅ Duplicate unique constraint (MySQL: 1062)
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            // ❗ Any other DB / server failure → 500
            report($e);

            return response()->json([
                'message' => 'د نوي ریکارډ ثبت کول په اوږدمهاله ناکامی وشوه؛ مهرباني وکړئ بیا هڅوئ.',
                'errors'  => [
                    'server' => ['د ثبت کولو په اوږدمهاله سیستم ستونزه لري (500).'],
                ],
            ], 500);
        } catch (\Throwable $e) {
            // ❗ Any non-DB failure (notification, route, model, …) → JSON 500, never an HTML error page
            report($e);

            return response()->json([
                'message' => 'د نوي ریکارډ ثبت کول په اوږدمهاله ناکامی وشوه؛ مهرباني وکړئ بیا هڅوئ.',
                'errors'  => [
                    'server' => ['د ثبت کولو په اوږدمهاله سیستم ستونزه لري (500).'],
                ],
            ], 500);
        }
    }

    public function show(OutgoingIncoming $outgoingIncoming)
    {
        return OutgoingIncomingResource::make($outgoingIncoming);
    }

    public function update(UpdateOutgoingIncomingRequest $request, OutgoingIncoming $outgoingIncoming)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($request, $outgoingIncoming, $user) {
                // 🧮 build changes BEFORE update
                $original  = $outgoingIncoming->getOriginal();
                $validated = $request->validated();
                $changes   = [];

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

                // 🔄 apply update
                $outgoingIncoming->update($validated);

                // 🔔 Notification: UPDATED + changes (never break the update if it fails)
                try {
                    NotificationService::record(
                        $user,
                        module: 'صادره/وارده',
                        action: 'updated',
                        title: 'ریکارډ اصلاح شو',
                        message: 'مرجع: ' . ($outgoingIncoming->marja ?? ''),
                        routeName: 'outgoing-incomings.show',
                        routeParams: ['outgoing_incoming' => $outgoingIncoming->id],
                        level: 'info',
                        changes: !empty($changes) ? $changes : null
                    );
                } catch (\Throwable $notifyError) {
                    report($notifyError);
                }

                return OutgoingIncomingResource::make($outgoingIncoming);
            });
        } catch (QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return response()->json([
                    'message' => 'دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).',
                    'errors'  => [
                        'duplicate' => ['دا ریکارډ له مخکې نه موجود دی (تکراري ثبت اجازه نشته).'],
                    ],
                ], 422);
            }

            // ❗ Any other DB / server failure → 500
            report($e);

            return response()->json([
                'message' => 'د ریکارډ اصلاح کول په اوږدمهاله ناکامی وشوه؛ مهرباني وکړئ بیا هڅوئ.',
                'errors'  => [
                    'server' => ['د اصلاح کولو په اوږدمهاله سیستم ستونزه لري (500).'],
                ],
            ], 500);
        } catch (\Throwable $e) {
            // ❗ Any non-DB failure (notification, route, model, …) → JSON 500, never an HTML error page
            report($e);

            return response()->json([
                'message' => 'د ریکارډ اصلاح کول په اوږدمهاله ناکامی وشوه؛ مهرباني وکړئ بیا هڅوئ.',
                'errors'  => [
                    'server' => ['د اصلاح کولو په اوږدمهاله سیستم ستونزه لري (500).'],
                ],
            ], 500);
        }
    }

    public function destroy(Request $request, OutgoingIncoming $outgoingIncoming)
    {
        $user = $request->user(); // 🔔

        try {
            return DB::transaction(function () use ($outgoingIncoming, $user) {
                $marja = $outgoingIncoming->marja;

                $outgoingIncoming->delete();

                // 🔔 Notification: DELETED (never break the delete if it fails)
                try {
                    NotificationService::record(
                        $user,
                        module: 'صادره/وارده',
                        action: 'deleted',
                        title: 'ریکارډ حذف شو',
                        message: 'مرجع: ' . ($marja ?? ''),
                        routeName: null,
                        routeParams: [],
                        level: 'warning',
                        changes: null
                    );
                } catch (\Throwable $notifyError) {
                    report($notifyError);
                }

                return response()->noContent();
            });
        } catch (\Throwable $e) {
            // ❗ Any failure while deleting → 500
            report($e);

            return response()->json([
                'message' => 'د ریکارډ ړنګول په اوږدمهاله ناکامی وشوه؛ مهرباني وکړئ بیا هڅوئ.',
                'errors'  => [
                    'server' => ['د ړنګولو په اوږدمهاله سیستم ستونزه لري (500).'],
                ],
            ], 500);
        }
    }
    private function searchableColumns(): array
{
    return [
        'id',
        'docs_type',
        'marja',
        'tafseel',

        'office_outgoing_page',
        'office_outgoing_date',
        'office_incoming_page',
        'office_incoming_date',

        'reyasat_outgoing_page',
        'reyasat_outgoing_date',
        'reyasat_incomming_page',
        'reyasat_incomming_date',

        'loy_reyasat_outgoing_page',
        'loy_reyasat_outgoing_date',

        'peshnehad',
        'outgoing_incoming_date',

        'answer_page',
        'answer_date',

        'page',
        'dusya',
        'fihrist_page',

        'naqal_count',
        'awraq',
        'zamima',

        'notes',
    ];
}

private function resolveSearchField(?string $field): ?string
{
    $field = trim((string) $field);

    return in_array($field, $this->searchableColumns(), true) ? $field : null;
}

private function applySearchFilter($query, ?string $search, ?string $searchField = null, $searchValue = null)
{
    $resolvedField = $this->resolveSearchField($searchField);
    $search = trim((string) $search);

    if ($searchValue !== null && $searchValue !== '' && $resolvedField) {
        $query->where($resolvedField, $searchValue);
    }

    if ($search === '') {
        return $query;
    }

    if ($resolvedField) {
        $query->where($resolvedField, 'like', "%{$search}%");
        return $query;
    }

    $query->where(function ($qq) use ($search) {
        foreach ($this->searchableColumns() as $index => $column) {
            if ($index === 0) {
                $qq->where($column, 'like', "%{$search}%");
            } else {
                $qq->orWhere($column, 'like', "%{$search}%");
            }
        }
    });

    return $query;
}

private function applyDocsTypeFilter($query, ?string $docsType)
{
    $docsType = trim((string) $docsType);

    if ($docsType === '') {
        return $query;
    }

    return $query->where('docs_type', $docsType);
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
    // Supports: 2026-04-26, 2026/04/26, 2026-04-26 00:00:00, ISO dates
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
    $query = OutgoingIncoming::query();

    $search      = $request->input('search');
    $searchField = $request->input('search_field');
    $searchValue = $request->input('search_value');
    $docsType    = $request->input('docs_type');

    $this->applySearchFilter(
        $query,
        $search,
        $searchField,
        $searchValue
    );

    $this->applyDocsTypeFilter($query, $docsType);

    $dateColumns = [
        'office_outgoing_date',
        'office_incoming_date',
        'reyasat_outgoing_date',
        'reyasat_incomming_date',
        'loy_reyasat_outgoing_date',
        'answer_date',
        'outgoing_incoming_date',
        'created_at',
        'updated_at',
    ];

    $rows = $query
        ->latest('id')
        ->get()
        ->map(function ($row) use ($dateColumns) {
            foreach ($dateColumns as $column) {
                $value = $row->{$column} ?? null;

                if ($value === null || trim((string) $value) === '') {
                    continue;
                }

                $row->{$column} = $this->formatPrintDateToShamsi($value);
            }

            return $row;
        });

    $selectedColumns = $request->input('selected_columns', []);
    $columnLabels    = $request->input('column_labels', []);
    $printLabels     = $request->input('print_labels', []);
    $locale          = $request->input('locale', 'fa');
    $title           = $request->input('title', 'چاپ مکتوب های صادره/وارده');

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
