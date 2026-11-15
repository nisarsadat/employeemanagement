<?php
// app/Http/Controllers/OilReportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class OilReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'daily'); // daily, weekly, yearly
        $from   = $request->date('from');           // optional YYYY-MM-DD
        $to     = $request->date('to');             // optional YYYY-MM-DD

        if (!in_array($period, ['daily','weekly','yearly'], true)) {
            return response()->json(['message' => 'Invalid period'], 422);
        }

        // Default ranges
        if (!$from || !$to) {
            if ($period === 'daily') {
                $to = Carbon::today();
                $from = (clone $to)->subDays(29);
            } elseif ($period === 'weekly') {
                $to = Carbon::today();
                $from = (clone $to)->subWeeks(11);
            } else { // yearly => last 12 months (bucket by month)
                $to = Carbon::today()->endOfMonth();
                $from = (clone $to)->subMonths(11)->startOfMonth();
            }
        }

        // Build buckets & formatters
        [$buckets, $format, $phpKeyFormat] = $this->makeBuckets($period, $from, $to);

        // Fetch raw aggregates from DB
        [$incomeRows, $expenseRows] = $this->fetchAggregates($period, $from, $to);

        // Index rows by bucket key
        $incomeIndex  = collect($incomeRows)->keyBy('bucket')->map(fn($r) => (float)$r->total);
        $expenseIndex = collect($expenseRows)->keyBy('bucket')->map(fn($r) => (float)$r->total);

        // Build series aligned to buckets
        $labels = [];
        $income = [];
        $expense = [];
        $net = [];
        $sumIncome = 0.0;
        $sumExpense = 0.0;

        foreach ($buckets as $bucket) {
            $key = $bucket->format($phpKeyFormat);
            $labels[] = $bucket->format($format);

            $inc = $incomeIndex->get($key, 0.0);
            $exp = $expenseIndex->get($key, 0.0);

            $income[]  = $inc;
            $expense[] = $exp;
            $net[]     = round($inc - $exp, 3);

            $sumIncome  += $inc;
            $sumExpense += $exp;
        }

        return response()->json([
            'period' => $period,
            'range'  => [
                'from' => $from->toDateString(),
                'to'   => $to->toDateString(),
            ],
            'labels'  => $labels,
            'series'  => [
                'income'  => $income,   // liters in
                'expense' => $expense,  // liters out
                'net'     => $net,      // in - out
            ],
            'totals' => [
                'income'  => round($sumIncome, 3),
                'expense' => round($sumExpense, 3),
                'net'     => round($sumIncome - $sumExpense, 3),
            ],
        ]);
    }

    private function makeBuckets(string $period, Carbon $from, Carbon $to): array
    {
        if ($period === 'daily') {
            $periodObj = CarbonPeriod::create($from->copy()->startOfDay(), '1 day', $to->copy()->startOfDay());
            // label format & key format
            return [$periodObj, 'Y-m-d', 'Y-m-d'];
        }

        if ($period === 'weekly') {
            // We’ll bucket by ISO Year-Week like 2025-W43
            $buckets = [];
            $cur = $from->copy()->startOfWeek(); // ISO week start (Mon)
            $end = $to->copy()->endOfWeek();
            while ($cur <= $end) {
                $buckets[] = $cur->copy(); // representative date for that week
                $cur->addWeek();
            }
            return [collect($buckets), 'o-\WW', 'o-W']; // label: 2025-W43, key: 2025-43
        }

        // yearly => actually month buckets across the last 12 months
        $buckets = [];
        $cur = $from->copy()->startOfMonth();
        $end = $to->copy()->endOfMonth();
        while ($cur <= $end) {
            $buckets[] = $cur->copy();
            $cur->addMonth();
        }
        // label like Oct 2025, key like 2025-10
        return [collect($buckets), 'M Y', 'Y-m'];
    }

    private function fetchAggregates(string $period, Carbon $from, Carbon $to): array
    {
        if ($period === 'daily') {
            $income = DB::table('income_oils')
                ->selectRaw("DATE(`date`) as bucket, SUM(oil_quantity) as total")
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $expense = DB::table('expense_oils')
                ->selectRaw("DATE(`date`) as bucket, SUM(liter_in_number) as total")
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            return [$income, $expense];
        }

        if ($period === 'weekly') {
            // ISO week: use YEARWEEK(date, 3) => mode 3 = ISO 8601 (weeks start Monday)
            $income = DB::table('income_oils')
                ->selectRaw("DATE_FORMAT(`date`, '%x-%v') as bucket, SUM(oil_quantity) as total")
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            $expense = DB::table('expense_oils')
                ->selectRaw("DATE_FORMAT(`date`, '%x-%v') as bucket, SUM(liter_in_number) as total")
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->groupBy('bucket')
                ->orderBy('bucket')
                ->get();

            return [$income, $expense];
        }

        // yearly => month buckets (YYYY-MM)
        $income = DB::table('income_oils')
            ->selectRaw("DATE_FORMAT(`date`, '%Y-%m') as bucket, SUM(oil_quantity) as total")
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        $expense = DB::table('expense_oils')
            ->selectRaw("DATE_FORMAT(`date`, '%Y-%m') as bucket, SUM(liter_in_number) as total")
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->get();

        return [$income, $expense];
    }
}
