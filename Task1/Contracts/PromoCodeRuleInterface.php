<?php

namespace Task1\Contracts;

use Task1\DTO\PricingData;

interface PromoCodeRuleInterface
{
    public function apply(PricingData $pricing): void;
}