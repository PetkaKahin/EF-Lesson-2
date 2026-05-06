<?php

namespace Task1;

use Task1\Contracts\PricingCalculatorInterface;
use Task1\Contracts\PromoCodeRulesRegistryInterface;
use Task1\DTO\OrderData;
use Task1\DTO\PricingData;

class PricingCalculator implements PricingCalculatorInterface
{
    public function __construct(
        private PromoCodeRulesRegistryInterface $promoCodeRulesRegistry,
    ) {}

    public function calculate(OrderData $data): PricingData
    {
        $promoCode = $this->normalizePromoCode($data->promoCode);

        $pricing = new PricingData(
            promoCode: $promoCode,
        );

        foreach ($data->items as $item) {
            $pricing->subtotal += $item->price * $item->qty;
        }

        $pricing->deliveryCost = $data->delivery->type->cost($pricing->subtotal);

        if ($promoCode !== null) {
            $this->promoCodeRulesRegistry->get($promoCode)->apply($pricing);
        }

        $pricing->tax = ($pricing->subtotal - $pricing->discount) * 0.05;
        $pricing->total = ($pricing->subtotal - $pricing->discount) + $pricing->tax + $pricing->deliveryCost;

        if ($pricing->total < 0) $pricing->total = 0;

        return $pricing;
    }

    private function normalizePromoCode(?string $promoCode): ?string
    {
        if ($promoCode === null) {
            return null;
        }

        $promoCode = strtoupper(trim($promoCode));

        return $promoCode !== '' ? $promoCode : null;
    }
}
