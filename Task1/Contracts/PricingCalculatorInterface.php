<?php

namespace Task1\Contracts;

use Task1\DTO\OrderData;
use Task1\DTO\PricingData;

interface PricingCalculatorInterface
{
    public function calculate(OrderData $data): PricingData;
}