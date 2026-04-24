<?php

declare(strict_types=1);

namespace Task1\Contracts;

use Task1\Order;

interface OrderRepositoryInterface
{
    public function setOrder(Order $order): void;
}