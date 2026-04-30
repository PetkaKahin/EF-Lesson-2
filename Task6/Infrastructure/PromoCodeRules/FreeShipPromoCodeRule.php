<?php

declare(strict_types=1);

namespace Task6\Infrastructure\PromoCodeRules;

use Task6\Domain\Contracts\PromoCodeRuleInterface;
use Task6\Domain\DTO\PaymentData;

final class FreeShipPromoCodeRule implements PromoCodeRuleInterface
{
    public function apply(PaymentData $payment): PaymentData
    {
        return new PaymentData(
            subtotal: $payment->subtotal,
            discount: $payment->discount,
            tax: $payment->tax,
            total: $payment->total,
            delivery: 0,
        );
    }

    public function getPromoCode(): string
    {
        return "FREESHIP";
    }
}