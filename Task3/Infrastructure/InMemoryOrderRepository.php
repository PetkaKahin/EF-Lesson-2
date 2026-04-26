<?php

declare(strict_types=1);

namespace Task3\Infrastructure;

use Task3\Contracts\OrderRepositoryInterface;
use Task3\VO\Order;

final class InMemoryOrderRepository implements OrderRepositoryInterface
{
    public function getOrders(): array
    {
        return [
            new Order(100),
            new Order(250),
            new Order(500),
        ];
    }
}
