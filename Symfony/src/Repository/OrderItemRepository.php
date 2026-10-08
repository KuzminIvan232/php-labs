<?php

namespace App\Repository;

use App\Entity\OrderItem;
use App\Repository\Filter\FilterablePaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrderItem>
 */
class OrderItemRepository extends ServiceEntityRepository
{
    use FilterablePaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderItem::class);
    }

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, orderId, menuItemId (exact),
     * quantity_min / quantity_max, unitPrice_min / unitPrice_max.
     *
     * @param array<string, mixed> $filters
     * @return array{items: OrderItem[], meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(array $filters, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('i')->orderBy('i.id', 'ASC');

        $this->applyFilters($qb, 'i', $filters, [
            'id' => ['type' => 'exact', 'valueType' => 'int'],
            'orderId' => ['type' => 'exact', 'field' => 'order', 'valueType' => 'int'],
            'menuItemId' => ['type' => 'exact', 'field' => 'menuItem', 'valueType' => 'int'],
            'quantity' => ['type' => 'range', 'valueType' => 'int'],
            'unitPrice' => ['type' => 'range', 'valueType' => 'float'],
        ]);

        return $this->paginateQuery($qb, 'i', $page, $itemsPerPage);
    }
}
