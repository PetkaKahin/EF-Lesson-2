<?php

namespace Task1\DTO;

class PricingData
{
    public function __construct(
        public float $subtotal = 0,
        public float $discount = 0,
        public float $tax = 0,
        public float $total = 0,
        public float $deliveryCost = 0,
        public ?string $promoCode = null,
    ) {}

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total' => $this->total,
            'deliveryCost' => $this->deliveryCost,
            'promoCode' => $this->promoCode,
        ];
    }
}
