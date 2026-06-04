<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

final class StructuredQueryBuilderService
{
    /**
     * @param Builder $query tenant-scoped Eloquent query
     * @param array<string, mixed> $filters
     * @param array<string, string> $allowed
     */
    public function apply(Builder $query, array $filters, array $allowed): Builder
    {
        foreach ($allowed as $key => $column) {
            $value = Arr::get($filters, $key);

            if ($value === null || $value === '') {
                continue;
            }

            if (str_ends_with($key, '_from')) {
                $query->where($column, '>=', $value);
                continue;
            }

            if (str_ends_with($key, '_to')) {
                $query->where($column, '<=', $value);
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($column, $value);
                continue;
            }

            $query->where($column, $value);
        }

        return $query;
    }
}
