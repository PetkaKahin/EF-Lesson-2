<?php

namespace Task5\Infrastructure\PromoCodeRules;

use InvalidArgumentException;
use Task5\Domain\Contracts\PromoCodeRuleInterface;
use Task5\Domain\DTO\PaymentData;

class WelcomePromoCodeRule implements PromoCodeRuleInterface
{
    public function __construct(
        private readonly float $discountPercent
    )
    {
        if ($this->discountPercent < 0 || $this->discountPercent > 100) {
            throw new InvalidArgumentException('Discount percent must be between 0 and 100');
        }
    }

    public function apply(PaymentData $payment): PaymentData
    {
        return new PaymentData(
            subtotal: $payment->subtotal,
            discount: $payment->subtotal * $this->discountPercent / 100,
            tax: $payment->tax,
            total: $payment->total,
        );
    }
}