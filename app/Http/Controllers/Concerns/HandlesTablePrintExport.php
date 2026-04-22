<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait HandlesTablePrintExport
{
    protected function normalizeSelectedColumns(Request $request, array $allowedColumns): array
    {
        $selected = $request->input('selected_columns', []);

        if (!is_array($selected) || empty($selected)) {
            return $allowedColumns;
        }

        return array_values(array_intersect($allowedColumns, $selected));
    }

    protected function buildPrintMeta(Request $request): array
    {
        return [
            'printed_at' => now()->format('Y-m-d H:i:s'),
            'filters' => $request->except(['page', 'perPage']),
        ];
    }
}