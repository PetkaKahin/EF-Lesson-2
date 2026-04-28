<?php

namespace Task5\Domain\Contracts;

use Task5\Domain\Order;

interface NotifyInterface
{
    public function send(Order $order): void;
}