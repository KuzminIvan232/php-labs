<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Repository\Filter\FilterablePaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    use FilterablePaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, customerId, restaurantTableId, status (exact),
     * reservedFor_min / reservedFor_max, guestsCount_min / guestsCount_max,
     * createdAt_min / createdAt_max.
     *
     * @param array<string, mixed> $filters
     * @return array{items: Reservation[], meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(array $filters, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('r')->orderBy('r.id', 'ASC');

        $this->applyFilters($qb, 'r', $filters, [
            'id' => ['type' => 'exact', 'valueType' => 'int'],
            'customerId' => ['type' => 'exact', 'field' => 'customer', 'valueType' => 'int'],
            'restaurantTableId' => ['type' => 'exact', 'field' => 'restaurantTable', 'valueType' => 'int'],
            'reservedFor' => ['type' => 'range', 'valueType' => 'datetime'],
            'guestsCount' => ['type' => 'range', 'valueType' => 'int'],
            'status' => ['type' => 'exact'],
            'createdAt' => ['type' => 'range', 'valueType' => 'datetime'],
        ]);

        return $this->paginateQuery($qb, 'r', $page, $itemsPerPage);
    }
}
