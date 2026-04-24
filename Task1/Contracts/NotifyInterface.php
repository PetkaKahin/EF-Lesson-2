<?php

declare(strict_types=1);

namespace Task1\Contracts;

use Task1\Order;

interface NotifyInterface
{
    public function send(Order $order): void;
}