<?php

namespace Task6\Domain;

use DomainException;
use Task6\Domain\Contracts\PromoCodeRuleInterface;

class PromoCodeRulesRegistry
{
    /**
     * @var array<PromoCodeRuleInterface>
     */
    private array $rules = [];

    public function set(string $promoCode, PromoCodeRuleInterface $rule): void
    {
        $promoCode = $this->promoCodeNormalize($promoCode);
        $this->rules[$promoCode] = $rule;
    }

    public function get(string $promoCode): PromoCodeRuleInterface
    {
        $promoCode = $this->promoCodeNormalize($promoCode);
        $rule = $this->rules[$promoCode] ?? null;

        if ($rule === null) {
            throw new DomainException('PromoCode rule not found');
        }

        return $rule;
    }

    private function promoCodeNormalize(string $promoCode): string
    {
        return strtoupper(trim($promoCode));
    }
}