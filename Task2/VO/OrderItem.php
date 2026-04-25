<?php

declare(strict_types=1);

namespace Task2\VO;

use InvalidArgumentException;

final readonly class OrderItem
{
    public function __construct(
        public string $name,
        public int $quantity,
        public Money $price,
    ) {
        if ($this->name === '') {
            throw new InvalidArgumentException('Name cannot be empty');
        }
        if ($this->quantity < 1) {
            throw new InvalidArgumentException('Quantity must be greater than 0');
        }
    }

    public function equals(self $product): bool
    {
        return $product->name === $this->name
            && $product->quantity === $this->quantity
            && $product->price->equals($this->price);
    }
}