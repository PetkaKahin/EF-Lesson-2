<?php

declare(strict_types=1);

namespace Task6\Application\DTO;

use InvalidArgumentException;
use Task6\Domain\VO\Delivery;
use Task6\Domain\VO\OrderItem;

final class CreateOrderRequest
{
    /**
     * @param array<OrderItem> $items
     * @param array<string> $promoCodes
     */
    public function __construct(
        public readonly string $customerEmail,
        public readonly Delivery $delivery,
        public readonly array $items,
        public readonly array $promoCodes = [],
        public readonly float $taxPercent = 5.0,
    )
    {
        if ($this->customerEmail === '') {
            throw new InvalidArgumentException('Customer email is required');
        }

        if ($this->items === []) {
            throw new InvalidArgumentException('Items are required');
        }

        foreach ($this->items as $item) {
            if (!$item instanceof OrderItem) {
                throw new InvalidArgumentException('Each item must be an instance of OrderItem');
            }
        }

        foreach ($this->promoCodes as $promoCode) {
            if (!is_string($promoCode)) {
                throw new InvalidArgumentException('Each promo code must be a string');
            }
        }

        if ($this->taxPercent < 0 || $this->taxPercent > 100) {
            throw new InvalidArgumentException('Tax percent must be between 0 and 100');
        }
    }
}
