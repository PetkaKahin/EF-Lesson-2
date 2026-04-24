<?php

declare(strict_types=1);

namespace Task1;

use DateTimeImmutable;
use Task1\DTO\CustomerData;
use Task1\DTO\DeliveryData;
use Task1\DTO\ItemData;
use Task1\DTO\PaymentData;
use Task1\DTO\PricingData;
use Task1\Enums\PaymentStatus;

class Order
{
    public readonly string $id;
    public readonly string $createdAt;

    /**
     * @param array<ItemData> $items
     */
    public function __construct(
        public CustomerData $customer,
        public array $items,
        public DeliveryData $delivery,
        public PaymentData $payment,
        public PaymentStatus $paymentStatus,
        public PricingData $pricing,
    ) {
        $this->createdAt = new DateTimeImmutable()->format('c');
        $this->id = time() . '-' . rand(1000, 9999);
    }

    public function toArray(): array
    {
        $delivery = $this->delivery->toArray();
        $delivery['deliveryCost'] = $this->pricing->deliveryCost;

        return [
            'id' => $this->id,
            'createdAt' => $this->createdAt,
            'customer' => $this->customer->toArray(),
            'items' => array_map(
                static fn (ItemData $item): array => $item->toArray(),
                $this->items
            ),
            'delivery' => $delivery,
            'payment' => $this->payment->toArray(),
            'pricing' => $this->pricing->toArray(),
        ];
    }
}