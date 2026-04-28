<?php

namespace Task5\Infrastructure\PromoCodeRules;

use Task5\Domain\Contracts\PromoCodeRuleInterface;
use Task5\Domain\DTO\PaymentData;

class VipPromoCodeRule implements PromoCodeRuleInterface
{
    public function __construct(
        private readonly float $discountFixed
    )
    {
    }

    public function apply(PaymentData $payment): PaymentData
    {
        return new PaymentData(
            subtotal: $payment->subtotal,
            discount: $this->discountFixed,
            tax: $payment->tax,
            total: $payment->total,
        );
    }
}