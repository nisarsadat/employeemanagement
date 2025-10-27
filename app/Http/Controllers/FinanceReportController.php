<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class FinanceReportController extends Controller
{
    /** GET /finance-reports/income */
    public function incomeReport(Request $request)
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        [$buckets, $labelFmt, $phpKeyFmt] = $this->makeBuckets($period, $from, $to);

        // 🔎 Auto-detect date column for incomes
        $dateCol = $this->pickDateColumn('incomes', ['date', 'received_at']);

        $rows = $this->aggregateByPeriod('incomes', 'amount', $dateCol, $period, $from, $to);
        $index = collect($rows)->keyBy('bucket')->map(fn($r) => (float)$r->total);

        $labels = [];
        $series = [];
        $sum = 0.0;

        foreach ($buckets as $b) {
            $key = $b->format($phpKeyFmt);
            $labels[] = $b->format($labelFmt);
            $v = (float) ($index[$key] ?? 0.0);
            $series[] = $v;
            $sum += $v;
        }

        return response()->json([
            'period' => $period,
            'range'  => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'labels' => $labels,
            'series' => ['income' => $series],
            'totals' => ['income' => round($sum, 2)],
        ]);
    }

    /** GET /finance-reports/expense */
    public function expenseReport(Request $request)
    {
        [$period, $from, $to] = $this->resolvePeriod($request);
        [$buckets, $labelFmt, $phpKeyFmt] = $this->makeBuckets($period, $from, $to);

        // 🔎 Auto-detect date column for expenses
        $dateCol = $this->pickDateColumn('expenses', ['date', 'paid_at']);

        $rows = $this->aggregateByPeriod('expenses', 'amount_price', $dateCol, $period, $from, $to);
        $index = collect($rows)->keyBy('bucket')->map(fn($r) => (float)$r->total);

        $labels = [];
        $series = [];
        $sum = 0.0;

        foreach ($buckets as $b) {
            $key = $b->format($phpKeyFmt);
            $labels[] = $b->format($labelFmt);
            $v = (float) ($index[$key] ?? 0.0);
            $series[] = $v;
            $sum += $v;
        }

        return response()->json([
            'period' => $period,
            'range'  => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'labels' => $labels,
            'series' => ['expense' => $series],
            'totals' => ['expense' => round($sum, 2)],
        ]);
    }

    /** GET /finance-reports/accounts */
    public function accountsReport(Request $request)
    {
        $rows = DB::table('accounts')
            ->select('id', 'name', DB::raw('COALESCE(price,0) as price'))
            ->orderBy('name')
            ->get();

        $total = $rows->sum(fn($r) => (float)$r->price);

        return response()->json([
            'as_of'    => Carbon::today()->toDateString(),
            'accounts' => $rows,
            'total'    => round((float)$total, 2),
        ]);
    }

    /* ============================ Helpers ============================ */

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'daily'); // daily | weekly | yearly
        if (!in_array($period, ['daily','weekly','yearly'], true)) {
            abort(response()->json(['message' => 'Invalid period'], 422));
        }

        $from = $request->date('from');
        $to   = $request->date('to');

        if (!$from || !$to) {
            if ($period === 'daily') {
                $to = Carbon::today();
                $from = (clone $to)->subDays(29);
            } elseif ($period === 'weekly') {
                $to = Carbon::today();
                $from = (clone $to)->subWeeks(11);
            } else { // yearly (12 months by month)
                $to = Carbon::today()->endOfMonth();
                $from = (clone $to)->subMonths(11)->startOfMonth();
            }
        }

        return [$period, $from, $to];
    }

    /**
     * Decide which date column to use by checking existing columns.
     * @param string $table
     * @param array<string> $candidates e.g. ['date','received_at']
     */
    private function pickDateColumn(string $table, array $candidates): string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) return $col;
        }
        // Last-resort: give a clear 500 with details
        abort(response()->json([
            'message' => "No usable date column found on {$table}. Tried: ".implode(', ', $candidates)
        ], 500));
    }

    /**
     * Aggregate amounts by period using the provided date column.
     */
    private function aggregateByPeriod(
        string $table,
        string $amountColumn,
        string $dateColumn,
        string $period,
        Carbon $from,
        Carbon $to
    ) {
        $fromStr = $from->toDateString();
        $toStr   = $to->toDateString();

        if ($period === 'daily') {
            return DB::table($table)
                ->selectRaw("DATE(`{$dateColumn}`) as bucket, SUM({$amountColumn}) as total")
                ->whereBetween($dateColumn, [$fromStr, $toStr])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();
        }

        if ($period === 'weekly') {
            // ISO year-week
            return DB::table($table)
                ->selectRaw("DATE_FORMAT(`{$dateColumn}`, '%x-%v') as bucket, SUM({$amountColumn}) as total")
                ->whereBetween($dateColumn, [$fromStr, $toStr])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();
        }

        // yearly => monthly buckets (YYYY-MM)
        return DB::table($table)
            ->selectRaw("DATE_FORMAT(`{$dateColumn}`, '%Y-%m') as bucket, SUM({$amountColumn}) as total")
            ->whereBetween($dateColumn, [$fromStr, $toStr])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();
    }

    private function makeBuckets(string $period, Carbon $from, Carbon $to): array
    {
        if ($period === 'daily') {
            $periodObj = CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay());
            return [$periodObj, 'Y-m-d', 'Y-m-d'];
        }

        if ($period === 'weekly') {
            $buckets = [];
            $cur = $from->copy()->startOfWeek(); // ISO Mon
            $end = $to->copy()->endOfWeek();
            while ($cur <= $end) {
                $buckets[] = $cur->copy();
                $cur->addWeek();
            }
            return [collect($buckets), 'o-\WW', 'o-W'];
        }

        // yearly (by month)
        $buckets = [];
        $cur = $from->copy()->startOfMonth();
        $end = $to->copy()->endOfMonth();
        while ($cur <= $end) {
            $buckets[] = $cur->copy();
            $cur->addMonth();
        }
        return [collect($buckets), 'M Y', 'Y-m'];
    }
}
