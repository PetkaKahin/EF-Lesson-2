<?php

namespace Task1;

use Task1\Contracts\PricingCalculatorInterface;
use Task1\DTO\OrderData;
use Task1\DTO\PricingData;

class PricingCalculator implements PricingCalculatorInterface
{
    public function calculate(OrderData $data): PricingData
    {
        $pricing = new PricingData(
            promoCode: $data->promoCode,
        );

        foreach ($data->items as $item) {
            $pricing->subtotal += $item->price * $item->qty;
        }

        $pricing->deliveryCost = $data->delivery->type->cost($pricing->subtotal);
        $data->promoCode?->apply($pricing);

        $pricing->tax = ($pricing->subtotal - $pricing->discount) * 0.05;
        $pricing->total = ($pricing->subtotal - $pricing->discount) + $pricing->tax + $pricing->deliveryCost;

        if ($pricing->total < 0) $pricing->total = 0;

        return $pricing;
    }
}