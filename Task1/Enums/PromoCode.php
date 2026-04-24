<?php

namespace Task1\Enums;

use Task1\Contracts\PromoCodeRuleInterface;
use Task1\DTO\PricingData;
use Task1\PromoCodeRules\FreeShipPromoCodeRule;
use Task1\PromoCodeRules\VipPromoCodeRule;
use Task1\PromoCodeRules\Welcome10PromoCodeRule;

enum PromoCode: string implements PromoCodeRuleInterface
{
    case Welcome10 = 'WELCOME10';
    case Vip = 'VIP';
    case FreeShip = 'FREESHIP';

    public function apply(PricingData $pricing): void
    {
        match ($this) {
            self::Welcome10 => new Welcome10PromoCodeRule()->apply($pricing),
            self::Vip => new VipPromoCodeRule(2000, 100, 300)->apply($pricing),
            self::FreeShip => new FreeShipPromoCodeRule()->apply($pricing),
        };
    }
}
