<?php

declare(strict_types=1);

namespace Task1\DTO;

use InvalidArgumentException;

final readonly class OrderData
{
    /**
     * @param ItemData[] $items
     */
    public function __construct(
        public CustomerData $customer,
        public array $items,
        public PaymentData $payment,
        public DeliveryData $delivery,
        public ?string $promoCode = null
    )
    {
        $this->validate();
    }

    public function toArray(): array
    {
        return [
            'customer' => $this->customer->toArray(),
            'items' => array_map(
                static fn (ItemData $item): array => $item->toArray(),
                $this->items
            ),
            'payment' => $this->payment->toArray(),
            'delivery' => $this->delivery->toArray(),
            'promoCode' => $this->promoCode,
        ];
    }

    private function validate(): void
    {
        if (count($this->items) === 0) {
            throw new InvalidArgumentException('items must be more than 1');
        }

        foreach ($this->items as $index => $item) {
            if (!$item instanceof ItemData) {
                throw new InvalidArgumentException("items[$index] must be instance of ItemData");
            }
        }
    }
}
