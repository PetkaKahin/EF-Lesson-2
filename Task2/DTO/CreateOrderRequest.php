<?php

declare(strict_types=1);

namespace Task2\DTO;

use InvalidArgumentException;

final readonly class CreateOrderRequest
{
    /**
     * @param array<CreateOrderItemRequest> $items
     */
    public function __construct(
        public string $email,
        public array $items,
    ) {
        if (count($this->items) === 0) {
            throw new InvalidArgumentException('Order must contain at least one item');
        }

        foreach ($this->items as $index => $item) {
            if (!$item instanceof CreateOrderItemRequest) {
                throw new InvalidArgumentException("items[$index] must be instance of CreateOrderItemRequest");
            }
        }
    }
}
