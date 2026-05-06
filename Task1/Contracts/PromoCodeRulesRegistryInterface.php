<?php

declare(strict_types=1);

namespace Task1\Contracts;

interface PromoCodeRulesRegistryInterface
{
    public function get(string $promoCode): PromoCodeRuleInterface;
}
