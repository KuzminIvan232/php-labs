<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(name: 'order_id', nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(name: 'menu_item_id', nullable: false)]
    private ?MenuItem $menuItem = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(name: 'unit_price', type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getMenuItem(): ?MenuItem
    {
        return $this->menuItem;
    }

    public function setMenuItem(?MenuItem $menuItem): static
    {
        $this->menuItem = $menuItem;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'orderId' => $this->order?->getId(),
            'menuItemId' => $this->menuItem?->getId(),
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
        ];
    }
}
