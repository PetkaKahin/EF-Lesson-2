<?php

declare(strict_types=1);

namespace Task4\Domain\Requests;

class CreateOrderRequest
{
    public function __construct(
        public readonly float                    $amount,
    )
    {
    }
}