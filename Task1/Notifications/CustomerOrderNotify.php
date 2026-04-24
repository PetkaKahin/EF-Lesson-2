<?php

declare(strict_types=1);

namespace Task1\Notifications;

use Task1\Contracts\NotifyInterface;
use Task1\Order;

class CustomerOrderNotify implements NotifyInterface
{
    public function __construct(
        private bool $debug,
    ) {}

    public function send(Order $order): void
    {
        $msg = "Thanks! Your order {$order->id} total={$order->pricing->total}";
        if ($this->debug) {
            error_log("[MAIL to {$order->customer->email}] {$msg}");
        }
    }
}