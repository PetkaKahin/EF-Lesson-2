<?php

declare(strict_types=1);

namespace Task6\LegacyWithFeature;

use InvalidArgumentException;
use Task6\LegacyWithFeature\Contracts\PromoCodeRuleInterface;

class PromoCodeRulesRegistry
{
    public const int MAX_PROMO_CODES_PER_ORDER = 2;

    /**
     * @var array<string, PromoCodeRuleInterface>
     */
    private array $rules = [];

    public function set(string $promoCode, PromoCodeRuleInterface $rule): void
    {
        $promoCode = $this->promoCodeNormalize($promoCode);
        $this->rules[$promoCode] = $rule;
    }

    public function get(string $promoCode): ?PromoCodeRuleInterface
    {
        $promoCode = $this->promoCodeNormalize($promoCode);
        return $this->rules[$promoCode] ?? null;
    }

    /**
     * @return array<string>
     */
    public function promoCodesFromInput(array $input): array
    {
        $promoCodes = [];

        // для старого функционала
        if (array_key_exists('promoCode', $input)) {
            $promoCodes[] = $input['promoCode'];
        }

        if (array_key_exists('promoCodes', $input)) {
            $inputPromoCodes = is_array($input['promoCodes'])
                ? $input['promoCodes']
                : [$input['promoCodes']];
            $promoCodes = array_merge($promoCodes, $inputPromoCodes);
        }

        $promoCodes = array_map(
            static fn(mixed $promoCode): string => strtoupper(trim((string)$promoCode)),
            $promoCodes,
        );

        $promoCodes = array_filter($promoCodes, static fn(string $promoCode): bool => $promoCode !== '');
        $promoCodes = array_values(array_unique($promoCodes));

        if (count($promoCodes) > self::MAX_PROMO_CODES_PER_ORDER) {
            throw new InvalidArgumentException('no more than 2 promo codes allowed');
        }

        return $promoCodes;
    }

    private function promoCodeNormalize(string $promoCode): string
    {
        return strtoupper(trim($promoCode));
    }
}
