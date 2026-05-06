<?php

declare(strict_types=1);

namespace Task4\Application\DTO;

class PayOrderRequest
{
    public function __construct(
        public readonly string $orderId
    )
    {
    }
}
