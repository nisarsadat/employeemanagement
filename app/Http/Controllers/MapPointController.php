<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MapPointController extends Controller
{
    private array $columnExistsCache = [];

    public function index(Request $request)
    {
        [$minLng, $minLat, $maxLng, $maxLat] = $this->parseBbox($request->query('bbox'));

        $defaultCoordinateCandidates = config('map_points.default_coordinate_candidates', ['coordinates']);
        $defaultPictureCandidates = config('map_points.default_picture_candidates', ['picture']);
        $sources = config('map_points.sources', []);

        $points = collect();

        foreach ($sources as $src) {
            $table = $src['table'] ?? null;
            if (!$table || !Schema::hasTable($table)) {
                continue;
            }

            $coordinateColumn = $this->pickFirstExistingColumn(
                $table,
                $src['coordinate_candidates'] ?? $defaultCoordinateCandidates
            );

            if (!$coordinateColumn) {
                continue;
            }

            $titleColumn = $this->pickFirstExistingColumn(
                $table,
                $src['title_candidates'] ?? ['name', 'title', 'code_number']
            );

            $pictureColumn = $this->pickFirstExistingColumn(
                $table,
                $src['picture_candidates'] ?? $defaultPictureCandidates
            );

            $codeColumns = $this->pickExistingColumns(
                $table,
                $src['code_candidates'] ?? []
            );

            $phoneColumns = $this->pickExistingColumns(
                $table,
                $src['phone_candidates'] ?? []
            );

            $activeColumn = $this->pickFirstExistingColumn(
                $table,
                $src['active_candidates'] ?? []
            );

            $hasResourceId = $this->columnExists($table, 'resource_id');
            $joinResource = ($src['join_resource'] ?? false) === true && $hasResourceId;

            $selects = [
                "{$table}.id as id",
                "{$table}.{$coordinateColumn} as coordinates",
            ];

            if ($titleColumn) {
                $selects[] = "{$table}.{$titleColumn} as own_title";
            }

            if ($pictureColumn) {
                $selects[] = "{$table}.{$pictureColumn} as own_picture";
            }

            foreach ($codeColumns as $idx => $col) {
                $selects[] = "{$table}.{$col} as own_code_" . $idx;
            }

            foreach ($phoneColumns as $idx => $col) {
                $selects[] = "{$table}.{$col} as own_phone_" . $idx;
            }

            if ($activeColumn) {
                $selects[] = "{$table}.{$activeColumn} as own_active";
            }

            $query = DB::table($table)->select($selects)
                ->whereNotNull("{$table}.{$coordinateColumn}")
                ->where("{$table}.{$coordinateColumn}", '!=', '');

            if ($joinResource) {
                $query->leftJoin('resources', "{$table}.resource_id", '=', 'resources.id');

                if ($this->columnExists('resources', 'code_number')) {
                    $query->addSelect('resources.code_number as resource_code_number');
                }
                if ($this->columnExists('resources', 'phone_number')) {
                    $query->addSelect('resources.phone_number as resource_phone_number');
                }
                if ($this->columnExists('resources', 'phone_number_1')) {
                    $query->addSelect('resources.phone_number_1 as resource_phone_number_1');
                }
                if ($this->columnExists('resources', 'phone_number_2')) {
                    $query->addSelect('resources.phone_number_2 as resource_phone_number_2');
                }
                if ($this->columnExists('resources', 'phone_number_3')) {
                    $query->addSelect('resources.phone_number_3 as resource_phone_number_3');
                }
            }

            $rows = $query->orderBy("{$table}.id")->get();

            $mapped = collect($rows)
                ->filter(function ($row) use ($minLng, $minLat, $maxLng, $maxLat) {
                    return $this->inBounds($row->coordinates ?? null, $minLng, $minLat, $maxLng, $maxLat);
                })
                ->map(function ($row) use ($src, $joinResource) {
                    $title = $this->pickText(
                        $row->own_title ?? null,
                        $src['typeLabel'] ?? null,
                        $src['type'] ?? null
                    );

                    $picture = $this->pickText($row->own_picture ?? null);

                    $subtitle = $joinResource
                        ? $this->buildResourceSubtitle($row)
                        : $this->buildSelfSubtitle($row);

                    $isActive = true;
                    if (($src['type'] ?? '') === 'resource') {
                        $isActive = $this->normalizeActive($row->own_active ?? null);
                    }

                    return [
                        'type' => $src['type'],
                        'typeLabel' => $src['typeLabel'],
                        'id' => (int) $row->id,
                        'coordinates' => (string) $row->coordinates,
                        'title' => $title,
                        'subtitle' => $subtitle,
                        'picture' => $picture ?: null,
                        'is_active' => $isActive,
                        '_showUrl' => rtrim($src['show_url'], '/') . '/' . $row->id,
                    ];
                })
                ->values();

            $points = $points->concat($mapped);
        }

        return response()->json([
            'data' => $points->values(),
            'meta' => [
                'count' => $points->count(),
            ],
        ]);
    }

    private function parseBbox(?string $bbox): array
    {
        if (!$bbox) {
            return [null, null, null, null];
        }

        $parts = array_map('trim', explode(',', $bbox));
        if (count($parts) !== 4) {
            return [null, null, null, null];
        }

        $minLng = is_numeric($parts[0]) ? (float) $parts[0] : null;
        $minLat = is_numeric($parts[1]) ? (float) $parts[1] : null;
        $maxLng = is_numeric($parts[2]) ? (float) $parts[2] : null;
        $maxLat = is_numeric($parts[3]) ? (float) $parts[3] : null;

        return [$minLng, $minLat, $maxLng, $maxLat];
    }

    private function parseCoordinates(?string $value): ?array
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        if (!preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', $value, $m)) {
            return null;
        }

        $lng = (float) $m[1];
        $lat = (float) $m[2];

        if (!is_finite($lng) || !is_finite($lat)) {
            return null;
        }

        return [$lng, $lat];
    }

    private function inBounds(?string $coordinates, ?float $minLng, ?float $minLat, ?float $maxLng, ?float $maxLat): bool
    {
        $coords = $this->parseCoordinates($coordinates);
        if (!$coords) {
            return false;
        }

        if ($minLng === null || $minLat === null || $maxLng === null || $maxLat === null) {
            return true;
        }

        [$lng, $lat] = $coords;

        return $lng >= $minLng
            && $lng <= $maxLng
            && $lat >= $minLat
            && $lat <= $maxLat;
    }

    private function buildResourceSubtitle(object $row): string
    {
        $parts = [];

        $resourceCode = $this->pickText(
            $row->resource_code_number ?? null
        );

        $resourcePhones = array_values(array_filter(array_unique([
            $this->pickText($row->resource_phone_number ?? null),
            $this->pickText($row->resource_phone_number_1 ?? null),
            $this->pickText($row->resource_phone_number_2 ?? null),
            $this->pickText($row->resource_phone_number_3 ?? null),
        ])));

        if ($resourceCode) {
            $parts[] = 'کد: ' . $resourceCode;
        }

        if (!empty($resourcePhones)) {
            $parts[] = 'تماس: ' . implode(' / ', $resourcePhones);
        }

        return implode(' • ', $parts);
    }

    private function buildSelfSubtitle(object $row): string
    {
        $parts = [];

        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $value = $this->pickText($row->{'own_code_' . $i} ?? null);
            if ($value && !in_array($value, $codes, true)) {
                $codes[] = $value;
            }
        }

        $phones = [];
        for ($i = 0; $i < 10; $i++) {
            $value = $this->pickText($row->{'own_phone_' . $i} ?? null);
            if ($value && !in_array($value, $phones, true)) {
                $phones[] = $value;
            }
        }

        if (!empty($codes)) {
            $parts[] = 'کد: ' . implode(' / ', $codes);
        }

        if (!empty($phones)) {
            $parts[] = 'تماس: ' . implode(' / ', $phones);
        }

        return implode(' • ', $parts);
    }

    private function pickText(...$values): string
    {
        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $s = trim((string) $value);

            if ($s !== '' && $s !== 'null' && $s !== 'undefined' && $s !== '-') {
                return $s;
            }
        }

        return '';
    }

    private function normalizeActive($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $s = strtolower(trim((string) $value));

        if (in_array($s, ['1', 'true', 'yes', 'active', 'فعال'], true)) {
            return true;
        }

        if (in_array($s, ['0', 'false', 'no', 'inactive', 'غیر فعال', 'غير فعال'], true)) {
            return false;
        }

        return true;
    }

    private function pickFirstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if ($this->columnExists($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function pickExistingColumns(string $table, array $candidates): array
    {
        $out = [];

        foreach ($candidates as $column) {
            if ($this->columnExists($table, $column)) {
                $out[] = $column;
            }
        }

        return array_values(array_unique($out));
    }

    private function columnExists(string $table, string $column): bool
    {
        $key = $table . '.' . $column;

        if (!array_key_exists($key, $this->columnExistsCache)) {
            $this->columnExistsCache[$key] = Schema::hasColumn($table, $column);
        }

        return $this->columnExistsCache[$key];
    }
}