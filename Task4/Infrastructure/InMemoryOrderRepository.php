<?php

declare(strict_types=1);

namespace Task4\Infrastructure;

use Task4\Domain\Contracts\OrderRepositoryInterface;
use Task4\Domain\Order;

final class InMemoryOrderRepository implements OrderRepositoryInterface
{
    /**
     * @var array<Order>
     */
    private array $orders = [];

    /**
     * @return Order[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    public function save(Order $order): void
    {
        $this->orders[$order->id] = $order;
    }

    public function findById(string $orderId): ?Order
    {
        return $this->orders[$orderId] ?? null;
    }
}
