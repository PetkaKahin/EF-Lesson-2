<?php

declare(strict_types=1);

namespace Task6\LegacyWithFeature\Contracts;

use Task6\LegacyWithFeature\DTO\PaymentData;

interface PromoCodeRuleInterface
{
    public function getPromoCode(): string;

    public function apply(PaymentData $payment): PaymentData;
}
