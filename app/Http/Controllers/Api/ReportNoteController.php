<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportNoteRequest;
use App\Models\ReportNote;
use Illuminate\Http\Request;

class ReportNoteController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'perPage' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'search'  => ['nullable', 'string', 'max:255'],
            'type'    => ['nullable', 'string', 'in:daily,weekly,monthly,quarter,yearly'],

            // ✅ NEW: created_at range filters
            'from'    => ['nullable', 'date'],
            'to'      => ['nullable', 'date'],
        ]);

        $perPage = (int)($request->integer('perPage') ?: 50);
        $search = trim((string)($request->input('search') ?? ''));
        $type = $request->input('type');

        $from = $request->input('from'); // YYYY-MM-DD or datetime
        $to   = $request->input('to');   // YYYY-MM-DD or datetime

        $query = ReportNote::query();

        if ($type) {
            $query->where('type', $type);
        }

        if ($search !== '') {
            $query->where('report_note', 'like', "%{$search}%");
        }

        // ✅ created_at range
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $query->latest('id');

        return response()->json(
            $query->paginate($perPage)
        );
    }

    public function store(ReportNoteRequest $request)
    {
        $note = ReportNote::create($request->validated());

        return response()->json([
            'message' => 'Report note created successfully.',
            'data' => $note,
        ], 201);
    }

    public function show(ReportNote $reportNote)
    {
        return response()->json([
            'data' => $reportNote,
        ]);
    }

    public function update(ReportNoteRequest $request, ReportNote $reportNote)
    {
        $reportNote->update($request->validated());

        return response()->json([
            'message' => 'Report note updated successfully.',
            'data' => $reportNote,
        ]);
    }

    public function destroy(ReportNote $reportNote)
    {
        $reportNote->delete();

        return response()->json([
            'message' => 'Report note deleted successfully.',
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        ReportNote::query()->whereIn('id', $request->input('ids'))->delete();

        return response()->json([
            'message' => 'Selected report notes deleted successfully.',
        ]);
    }
}
