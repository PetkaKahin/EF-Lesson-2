<?php

namespace Task5\Domain;

use DomainException;
use Task5\Domain\Contracts\PromoCodeRuleInterface;
use Task5\Domain\VO\PromoCode;

class PromoCodeRulesRegistry
{
    /**
     * @var array<PromoCodeRuleInterface>
     */
    private array $rules = [];

    public function set(PromoCode $promoCode, PromoCodeRuleInterface $rule): void
    {
        $this->rules[$promoCode->value] = $rule;
    }

    public function get(PromoCode $promoCode): PromoCodeRuleInterface
    {
        $rule = $this->rules[$promoCode->value] ?? null;

        if ($rule === null) {
            throw new DomainException('PromoCode rule not found');
        }

        return $rule;
    }
}