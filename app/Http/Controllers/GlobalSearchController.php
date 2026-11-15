<?php

namespace App\Http\Controllers;

use App\Support\GlobalSearchSources;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class GlobalSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $variants = $this->variants($query);
        $results = [];
        $ran = 0;
        $deadline = microtime(true) + 1.6;

        $mainModels = [
            \App\Models\OfficeEmployee::class,
            \App\Models\Resource::class,
            \App\Models\Information::class,
            \App\Models\Event::class,
            \App\Models\Meeting::class,
        ];

        foreach (GlobalSearchSources::all() as $source) {
            if (! in_array($source['model'], $mainModels, true)) {
                continue;
            }
            if (count($results) >= 24) {
                break;
            }
            if ($ran >= 16 && microtime(true) > $deadline) {
                break;
            }
            if (! $this->allowed($user, $source['perms'])) {
                continue;
            }

            $ran++;

            try {
                $rows = $this->search($source, $variants);
            } catch (Throwable $e) {
                report($e);
                continue;
            }

            foreach ($rows as $row) {
                $item = $this->present($source, $row);
                if ($item) {
                    $results[] = $item;
                }
                if (count($results) >= 24) {
                    break;
                }
            }
        }

        return response()->json(['results' => $results]);
    }

    private function allowed($user, array $perms): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('Admin'))) {
            return true;
        }

        foreach (array_merge(['*'], $perms) as $perm) {
            try {
                if ($user->can($perm)) {
                    return true;
                }
            } catch (Throwable $e) {
                continue;
            }
        }

        return false;
    }

    private function search(array $source, array $variants)
    {
        /** @var class-string<Model> $model */
        $model = $source['model'];

        return $model::query()
            ->where(function ($builder) use ($source, $variants) {
                $first = true;
                foreach ($variants as $variant) {
                    $like = '%'.$this->escapeLike($variant).'%';
                    foreach ($source['search'] as $column) {
                        if ($first) {
                            $builder->where($column, 'like', $like);
                            $first = false;
                        } else {
                            $builder->orWhere($column, 'like', $like);
                        }
                    }
                }
            })
            ->limit(3)
            ->get(array_values(array_unique(array_merge(
                ['id'],
                $source['title'],
                $source['subtitle']
            ))));
    }

    private function present(array $source, Model $row): ?array
    {
        $title = $this->firstText($row, $source['title']);
        if ($title === '') {
            return null;
        }

        $subtitle = [];
        foreach ($source['subtitle'] as $column) {
            $value = trim((string) ($row->{$column} ?? ''));
            if ($value === '' || $value === $title) {
                continue;
            }
            $subtitle[] = mb_strlen($value) > 90 ? mb_substr($value, 0, 90).'…' : $value;
            if (count($subtitle) >= 2) {
                break;
            }
        }

        return [
            'id' => $source['label'].'-'.$row->getKey(),
            'kind' => 'data',
            'label' => $source['label'],
            'title' => $title,
            'subtitle' => implode(' · ', $subtitle),
            'icon' => $source['icon'],
            'path' => str_replace('{id}', (string) $row->getKey(), $source['path']),
        ];
    }

    private function firstText(Model $row, array $columns): string
    {
        foreach ($columns as $column) {
            $value = trim((string) ($row->{$column} ?? ''));
            if ($value !== '') {
                return mb_strlen($value) > 120 ? mb_substr($value, 0, 120).'…' : $value;
            }
        }

        return '';
    }

    private function variants(string $query): array
    {
        $digits = strtr($query, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);

        $persian = str_replace(['ي', 'ك', 'ة', 'ۀ'], ['ی', 'ک', 'ه', 'ه'], $digits);
        $arabic = str_replace(['ی', 'ک', 'ه'], ['ي', 'ك', 'ة'], $persian);

        return array_values(array_unique(array_filter([$persian, $arabic, $digits], fn ($v) => $v !== '')));
    }

    private function escapeLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }
}
