<?php

declare(strict_types=1);

namespace Task1\DTO;

use InvalidArgumentException;
use Task1\Enums\PaymentMethod;

final readonly class PaymentData
{
    public function __construct(
        public PaymentMethod $method,
        public ?string $cardNumber = null
    )
    {
        $this->validate();
    }

    public function toArray(): array
    {
        return [
            'payload' => $this->method->value,
            'cardNumber' => $this->cardNumber,
        ];
    }

    private function validate(): void
    {
        if ($this->method === PaymentMethod::Card) {
            $card = preg_replace('/\s+/', '', $this->cardNumber);

            if (strlen($card) < 12) {
                throw new InvalidArgumentException('invalid card number');
            }
        }
    }
}
