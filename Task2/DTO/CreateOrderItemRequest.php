<?php

declare(strict_types=1);

namespace Task2\DTO;

use InvalidArgumentException;
use Task2\Enums\Currency;

final readonly class CreateOrderItemRequest
{
    public function __construct(
        public string $name,
        public int $quantity,
        public int $priceAmount,
        public string $priceCurrency,
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Item name cannot be empty');
        }

        if ($this->quantity < 1) {
            throw new InvalidArgumentException('Item quantity must be greater than 0');
        }

        if ($this->priceAmount < 0) {
            throw new InvalidArgumentException('Item price amount must be greater than or equal to 0');
        }

        if (Currency::tryFrom($this->priceCurrency) === null) {
            throw new InvalidArgumentException('Item price currency is invalid');
        }
    }
}
