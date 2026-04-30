<?php

declare(strict_types=1);

namespace Task6\Infrastructure\PromoCodeRules;

use Task6\Domain\Contracts\PromoCodeRuleInterface;
use Task6\Domain\DTO\PaymentData;

final class Welcome10PromoCodeRule implements PromoCodeRuleInterface
{
    public function apply(PaymentData $payment): PaymentData
    {
        return new PaymentData(
            subtotal: $payment->subtotal,
            discount: $payment->subtotal * 0.10,
            tax: $payment->tax,
            total: $payment->total,
            delivery: $payment->delivery,
        );
    }

    public function getPromoCode(): string
    {
        return "WELCOME10";
    }
}