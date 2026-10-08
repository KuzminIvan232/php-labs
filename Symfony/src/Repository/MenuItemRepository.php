<?php

namespace App\Repository;

use App\Entity\MenuItem;
use App\Repository\Filter\FilterablePaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuItem>
 */
class MenuItemRepository extends ServiceEntityRepository
{
    use FilterablePaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id (exact), name, description, category (partial match),
     * price_min / price_max.
     *
     * @param array<string, mixed> $filters
     * @return array{items: MenuItem[], meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(array $filters, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('m')->orderBy('m.id', 'ASC');

        $this->applyFilters($qb, 'm', $filters, [
            'id' => ['type' => 'exact', 'valueType' => 'int'],
            'name' => ['type' => 'like'],
            'description' => ['type' => 'like'],
            'price' => ['type' => 'range', 'valueType' => 'float'],
            'category' => ['type' => 'like'],
        ]);

        return $this->paginateQuery($qb, 'm', $page, $itemsPerPage);
    }
}
