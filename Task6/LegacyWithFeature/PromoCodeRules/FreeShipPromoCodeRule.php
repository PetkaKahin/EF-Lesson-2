<?php

declare(strict_types=1);

namespace Task6\LegacyWithFeature\PromoCodeRules;

use Task6\LegacyWithFeature\Contracts\PromoCodeRuleInterface;
use Task6\LegacyWithFeature\DTO\PaymentData;

final class FreeShipPromoCodeRule implements PromoCodeRuleInterface
{
    public function getPromoCode(): string
    {
        return 'SHIPFREE';
    }

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
}
