<?php

declare(strict_types=1);

namespace Task6\LegacyWithFeature\PromoCodeRules;

use Task6\LegacyWithFeature\Contracts\PromoCodeRuleInterface;
use Task6\LegacyWithFeature\DTO\PaymentData;

final class Welcome10PromoCodeRule implements PromoCodeRuleInterface
{
    public function getPromoCode(): string
    {
        return 'WELCOME10';
    }

    public function apply(PaymentData $payment): PaymentData
    {
        return new PaymentData(
            subtotal: $payment->subtotal,
            discount: $payment->discount + ($payment->subtotal * 0.10),
            tax: $payment->tax,
            total: $payment->total,
            delivery: $payment->delivery,
        );
    }
}
