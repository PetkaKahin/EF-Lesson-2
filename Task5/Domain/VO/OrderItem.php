<?php

namespace Task5\Domain\VO;

use InvalidArgumentException;

readonly class OrderItem
{
    public function __construct(
        public float $price,
        public float $quantity,
    )
    {
        if ($this->price < 0) {
            throw new InvalidArgumentException('Price must be positive');
        }
        if ($this->quantity < 1) {
            throw new InvalidArgumentException('Quantity must be greater than 0');
        }
    }
}