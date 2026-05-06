<?php

declare(strict_types=1);

namespace Task6\LegacyWithFeature\DTO;

class PaymentData
{
    public function __construct(
        public float $subtotal = 0.0,
        public float $discount = 0.0,
        public float $tax = 0.0,
        public float $total = 0.0,
        public float $delivery = 0.0,
    ) {
    }
}
