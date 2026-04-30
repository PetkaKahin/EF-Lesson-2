<?php

namespace Task6\Domain\Contracts;

use Task6\Domain\Order;

interface NotifyInterface
{
    public function send(Order $order): void;
}