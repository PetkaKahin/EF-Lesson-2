<?php

declare(strict_types=1);

namespace Task6\Infrastructure\PromoCodeRules;

use InvalidArgumentException;
use Task6\Domain\Contracts\PromoCodeRuleInterface;
use Task6\Domain\DTO\PaymentData;

final readonly class VipPromoCodeRule implements PromoCodeRuleInterface
{
    public function __construct(
        private float $threshold,
        private float $discountBeforeThreshold,
        private float $discountAfterThreshold,
    )
    {
        if ($this->threshold < 0) {
            throw new InvalidArgumentException('Threshold must be positive');
        }
        if ($this->discountBeforeThreshold < 0) {
            throw new InvalidArgumentException('Discount before threshold must be positive');
        }
        if ($this->discountAfterThreshold < 0) {
            throw new InvalidArgumentException('Discount after threshold must be positive');
        }
    }

    public function apply(PaymentData $payment): PaymentData
    {
        $discount = $this->discountBeforeThreshold;

        if ($payment->subtotal >= $this->threshold) {
            $discount = $this->discountAfterThreshold;
        }

        return new PaymentData(
            subtotal: $payment->subtotal,
            discount: $discount,
            tax: $payment->tax,
            total: $payment->total,
            delivery: $payment->delivery,
        );
    }

    public function getPromoCode(): string
    {
        return "VIP";
    }
}