<?php

namespace Task5\Domain\Contracts;

use Task5\Domain\DTO\PaymentData;

interface PromoCodeRuleInterface
{
    public function apply(PaymentData $payment): PaymentData;
}