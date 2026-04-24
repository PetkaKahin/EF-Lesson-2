<?php

declare(strict_types=1);

namespace Task1\PromoCodeRules;

use Task1\DTO\PricingData;
use Task1\Contracts\PromoCodeRuleInterface;

final readonly class VipPromoCodeRule implements PromoCodeRuleInterface
{
    public function __construct(
        private float $threshold,
        private float $discountBeforeThreshold,
        private float $discountAfterThreshold,
    ){}

    public function apply(PricingData $pricing): void
    {
        if ($pricing->subtotal >= $this->threshold) {
            $pricing->discount = $this->discountAfterThreshold;
        } else {
            $pricing->discount = $this->discountBeforeThreshold;
        }
    }
}