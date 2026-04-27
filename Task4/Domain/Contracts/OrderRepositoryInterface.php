<?php

declare(strict_types=1);

namespace Task4\Domain\Contracts;

use Task4\Domain\Order;

interface OrderRepositoryInterface
{
    /**
     * @return array<Order>
     */
    public function getOrders(): array;

    public function save(Order $order): void;

    public function findById(string $orderId): ?Order;
}