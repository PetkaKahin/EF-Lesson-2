<?php

declare(strict_types=1);

namespace Task1\PromoCodeRules;

use Task1\DTO\PricingData;
use Task1\Contracts\PromoCodeRuleInterface;

final class Welcome10PromoCodeRule implements PromoCodeRuleInterface
{
    public function apply(PricingData $pricing): void
    {
        $pricing->discount = $pricing->subtotal * 0.10;
    }
}