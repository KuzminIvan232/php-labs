<?php

namespace App\Repository\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 * Shared helpers for turning request query parameters into Doctrine
 * QueryBuilder filters, plus page/itemsPerPage pagination (Lab 4).
 *
 * Supported filter types, declared per field in a $fieldMap:
 *  - 'exact' : WHERE alias.field = :param                       (ids, foreign keys, status/category codes)
 *  - 'like'  : WHERE LOWER(alias.field) LIKE :param              (free-text, case-insensitive, partial match)
 *  - 'range' : WHERE alias.field >= :param_min AND <= :param_max (numbers, prices, dates — via ?field_min=&field_max=)
 *
 * Each $fieldMap entry may also set:
 *  - 'field'     : the actual entity property name, when it differs from the filter key
 *                  (e.g. filter key "customerId" maps to the "customer" association)
 *  - 'valueType' : 'int' | 'float' | 'datetime' to cast the raw query string before binding
 */
trait FilterablePaginationTrait
{
    /**
     * @param array<string, mixed>                                        $filters  raw query parameters (e.g. $request->query->all())
     * @param array<string, array{type: string, field?: string, valueType?: string}> $fieldMap filter key => spec
     */
    private function applyFilters(QueryBuilder $qb, string $alias, array $filters, array $fieldMap): void
    {
        foreach ($fieldMap as $filterKey => $spec) {
            $field = $spec['field'] ?? $filterKey;
            $type = $spec['type'];
            $valueType = $spec['valueType'] ?? null;

            if ($type === 'range') {
                $minRaw = $filters[$filterKey . '_min'] ?? null;
                $maxRaw = $filters[$filterKey . '_max'] ?? null;

                if ($minRaw !== null && $minRaw !== '') {
                    $qb->andWhere("$alias.$field >= :{$filterKey}_min")
                        ->setParameter("{$filterKey}_min", $this->castFilterValue((string) $minRaw, $valueType));
                }

                if ($maxRaw !== null && $maxRaw !== '') {
                    $qb->andWhere("$alias.$field <= :{$filterKey}_max")
                        ->setParameter("{$filterKey}_max", $this->castFilterValue((string) $maxRaw, $valueType));
                }

                continue;
            }

            $raw = $filters[$filterKey] ?? null;
            if ($raw === null || $raw === '') {
                continue;
            }

            if ($type === 'like') {
                $qb->andWhere("LOWER($alias.$field) LIKE :$filterKey")
                    ->setParameter($filterKey, '%' . mb_strtolower((string) $raw) . '%');
            } else {
                $qb->andWhere("$alias.$field = :$filterKey")
                    ->setParameter($filterKey, $this->castFilterValue((string) $raw, $valueType));
            }
        }
    }

    private function castFilterValue(string $raw, ?string $valueType): mixed
    {
        return match ($valueType) {
            'datetime' => new \DateTimeImmutable($raw),
            'int' => (int) $raw,
            'float' => (float) $raw,
            default => $raw,
        };
    }

    /**
     * @return array{items: array<int, object>, meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    private function paginateQuery(QueryBuilder $qb, string $alias, int $page, int $itemsPerPage): array
    {
        $page = max(1, $page);
        $itemsPerPage = max(1, min(100, $itemsPerPage));

        $totalItems = (int) (clone $qb)
            ->select("COUNT($alias.id)")
            ->getQuery()
            ->getSingleScalarResult();

        $items = $qb
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

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
