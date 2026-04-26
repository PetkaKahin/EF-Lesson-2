<?php

declare(strict_types=1);

namespace Task3\Contracts;

use Task3\VO\Order;

interface OrderRepositoryInterface
{
    /**
     * @return array<Order>
     */
    public function getOrders(): array;
}