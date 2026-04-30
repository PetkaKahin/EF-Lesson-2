<?php

namespace Task6\Domain\VO;

use InvalidArgumentException;

readonly class OrderItem
{
    public function __construct(
        public string $sku,
        public string $title,
        public float  $price,
        public float  $quantity,
    )
    {
        if ($this->price < 0) {
            throw new InvalidArgumentException('Price must be positive');
        }
        if ($this->quantity < 1) {
            throw new InvalidArgumentException('Quantity must be greater than 0');
        }
        if ($this->sku === '') {
            throw new InvalidArgumentException('Sku cannot be empty');
        }
        if ($this->title === '') {
            throw new InvalidArgumentException('Title cannot be empty');
        }
    }
}