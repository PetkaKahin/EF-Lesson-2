<?php

namespace Task6\Domain\Contracts;

use Task6\Domain\DTO\PaymentData;

interface PromoCodeRuleInterface
{
    public function getPromoCode(): string;
    public function apply(PaymentData $payment): PaymentData;
}