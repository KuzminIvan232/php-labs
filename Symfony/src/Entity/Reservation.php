<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
#[ORM\Table(name: 'reservations')]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(name: 'restaurant_table_id', nullable: false)]
    private ?RestaurantTable $restaurantTable = null;

    #[ORM\Column(name: 'reserved_for', type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $reservedFor = null;

    #[ORM\Column(name: 'guests_count')]
    private ?int $guestsCount = null;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getRestaurantTable(): ?RestaurantTable
    {
        return $this->restaurantTable;
    }

    public function setRestaurantTable(?RestaurantTable $restaurantTable): static
    {
        $this->restaurantTable = $restaurantTable;

        return $this;
    }

    public function getReservedFor(): ?\DateTimeImmutable
    {
        return $this->reservedFor;
    }

    public function setReservedFor(\DateTimeImmutable $reservedFor): static
    {
        $this->reservedFor = $reservedFor;

        return $this;
    }

    public function getGuestsCount(): ?int
    {
        return $this->guestsCount;
    }

    public function setGuestsCount(int $guestsCount): static
    {
        $this->guestsCount = $guestsCount;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'customerId' => $this->customer?->getId(),
            'restaurantTableId' => $this->restaurantTable?->getId(),
            'reservedFor' => $this->reservedFor?->format(DATE_ATOM),
            'guestsCount' => $this->guestsCount,
            'status' => $this->status,
            'createdAt' => $this->createdAt?->format(DATE_ATOM),
        ];
    }
}
