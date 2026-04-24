<?php

declare(strict_types=1);

namespace Task1\PromoCodeRules;

use Task1\DTO\PricingData;
use Task1\Contracts\PromoCodeRuleInterface;

final class FreeShipPromoCodeRule implements PromoCodeRuleInterface
{
    public function apply(PricingData $pricing): void
    {
        $pricing->deliveryCost = 0;
    }
}