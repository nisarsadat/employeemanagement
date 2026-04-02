<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesRelatedFilters
{
    protected function relatedFilterRules(): array
    {
        return [
            'office_employee_id'   => ['column' => null, 'callback' => null],
            'resource_id'          => ['column' => null, 'callback' => null],
            'information_id'       => ['column' => null, 'callback' => null],
            'event_id'             => ['column' => null, 'callback' => null],
            'fifty_six_format_id'  => ['column' => null, 'callback' => null],
        ];
    }

    protected function applyRelatedFilters(Builder $query, Request $request): Builder
    {
        $rules = $this->relatedFilterRules();

        foreach ($rules as $param => $rule) {
            $value = $request->input($param);

            if ($value === null || $value === '') {
                continue;
            }

            if (!empty($rule['column'])) {
                $query->where($rule['column'], $value);
                continue;
            }

            if (!empty($rule['callback']) && is_callable($rule['callback'])) {
                $rule['callback']($query, $value, $request);
            }
        }

        return $query;
    }
}