<?php

namespace App\Repository;

use App\Entity\Customer;
use App\Repository\Filter\FilterablePaginationTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
class CustomerRepository extends ServiceEntityRepository
{
    use FilterablePaginationTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    /**
     * Lab 4: filtering by every field + page/itemsPerPage pagination.
     *
     * Supported query params: id, name, phone, email (partial match),
     * createdAt_min / createdAt_max (ISO-8601).
     *
     * @param array<string, mixed> $filters
     * @return array{items: Customer[], meta: array{page: int, itemsPerPage: int, totalItems: int, totalPages: int}}
     */
    public function search(array $filters, int $page, int $itemsPerPage): array
    {
        $qb = $this->createQueryBuilder('c')->orderBy('c.id', 'ASC');

        $this->applyFilters($qb, 'c', $filters, [
            'id' => ['type' => 'exact', 'valueType' => 'int'],
            'name' => ['type' => 'like'],
            'phone' => ['type' => 'like'],
            'email' => ['type' => 'like'],
            'createdAt' => ['type' => 'range', 'valueType' => 'datetime'],
        ]);

        return $this->paginateQuery($qb, 'c', $page, $itemsPerPage);
    }
}
