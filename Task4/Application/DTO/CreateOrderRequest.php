<?php

declare(strict_types=1);

namespace Task4\Application\DTO;

class CreateOrderRequest
{
    public function __construct(
        public readonly float $amount,
    )
    {
    }
}
