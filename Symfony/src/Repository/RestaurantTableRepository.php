<?php

namespace App\Repository;

use App\Entity\RestaurantTable;
use App\Repository\Filter\FilterablePaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RestaurantTable>
 */
class RestaurantTableRepository extends ServiceEntityRepository
{
    use FilterablePaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RestaurantTable::class);
    }

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, tableNumber (exact),
     * seats_min / seats_max.
     *
     * @param array<string, mixed> $filters
     * @return array{items: RestaurantTable[], meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(array $filters, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('t')->orderBy('t.id', 'ASC');

        $this->applyFilters($qb, 't', $filters, [
            'id' => ['type' => 'exact', 'valueType' => 'int'],
            'tableNumber' => ['type' => 'exact', 'valueType' => 'int'],
            'seats' => ['type' => 'range', 'valueType' => 'int'],
        ]);

        return $this->paginateQuery($qb, 't', $page, $itemsPerPage);
    }
}
