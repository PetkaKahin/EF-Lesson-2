<?php

namespace Task5\Domain\Contracts;

use Task5\Domain\Order;

interface OrderRepositoryInterface
{
    /**
     * @return array<Order>
     */
    public function orders(): array;
    public function order(string $id): ?Order;
    public function save(Order $order): void;
}