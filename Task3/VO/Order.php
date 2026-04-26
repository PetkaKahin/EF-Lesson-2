<?php

declare(strict_types=1);

namespace Task3\VO;

use InvalidArgumentException;

class Order
{
    public function __construct(
        private float $amount,
    )
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount must be greater than 0');
        }
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}