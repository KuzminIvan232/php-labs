<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Shared helpers for turning request query parameters into Eloquent query
 * filters, plus page/itemsPerPage pagination (Lab 4).
 *
 * Supported filter types, declared per field in a $fieldMap:
 *  - 'exact' : WHERE column = value                        (ids, foreign keys, status/category codes)
 *  - 'like'  : WHERE LOWER(column) LIKE %value%             (free-text, case-insensitive, partial match)
 *  - 'range' : WHERE column >= value_min AND <= value_max   (numbers, prices, dates — via ?field_min=&field_max=)
 *
 * The $fieldMap key is the API-facing query parameter name (kept camelCase,
 * matching the camelCase keys already used in request/response bodies
 * elsewhere in this project); 'column' is the actual DB column, when it
 * differs from the key (e.g. "customerId" -> "customer_id").
 */
trait FiltersAndPaginates
{
    /**
     * @param array<string, array{type: string, column?: string}> $fieldMap filter key => spec
     */
    private function applyFilters(Builder $query, Request $request, array $fieldMap): void
    {
        foreach ($fieldMap as $filterKey => $spec) {
            $column = $spec['column'] ?? $filterKey;
            $type = $spec['type'];

            if ($type === 'range') {
                $min = $request->query($filterKey . '_min');
                $max = $request->query($filterKey . '_max');

                if ($min !== null && $min !== '') {
                    $query->where($column, '>=', $min);
                }

                if ($max !== null && $max !== '') {
                    $query->where($column, '<=', $max);
                }

                continue;
            }

            $value = $request->query($filterKey);
            if ($value === null || $value === '') {
                continue;
            }

            if ($type === 'like') {
                $query->whereRaw('LOWER(' . $column . ') LIKE ?', ['%' . mb_strtolower((string) $value) . '%']);
            } else {
                $query->where($column, $value);
            }
        }
    }

    /**
     * @return array{items: \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>, meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    private function paginateQuery(Builder $query, Request $request): array
    {
        $page = max(1, (int) $request->query('page', 1));
        $itemsPerPage = max(1, min(100, (int) $request->query('itemsPerPage', 10)));

        $totalItems = (clone $query)->count();

        $items = $query->forPage($page, $itemsPerPage)->get();

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $itemsPerPage > 0 ? (int) ceil($totalItems / $itemsPerPage) : 0,
            ],
        ];
    }
}
