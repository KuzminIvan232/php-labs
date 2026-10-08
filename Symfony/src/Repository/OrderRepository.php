<?php

namespace App\Repository;

use App\Entity\Order;
use App\Repository\Filter\FilterablePaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    use FilterablePaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, customerId, restaurantTableId, status (exact),
     * orderedAt_min / orderedAt_max.
     *
     * @param array<string, mixed> $filters
     * @return array{items: Order[], meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(array $filters, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('o')->orderBy('o.id', 'ASC');

        $this->applyFilters($qb, 'o', $filters, [
            'id' => ['type' => 'exact', 'valueType' => 'int'],
            'customerId' => ['type' => 'exact', 'field' => 'customer', 'valueType' => 'int'],
            'restaurantTableId' => ['type' => 'exact', 'field' => 'restaurantTable', 'valueType' => 'int'],
            'status' => ['type' => 'exact'],
            'orderedAt' => ['type' => 'range', 'valueType' => 'datetime'],
        ]);

        return $this->paginateQuery($qb, 'o', $page, $itemsPerPage);
    }
}
