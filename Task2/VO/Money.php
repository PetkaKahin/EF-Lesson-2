<?php

declare(strict_types=1);

namespace Task2\VO;

use InvalidArgumentException;
use Task2\Enums\Currency;

final readonly class Money
{
    public function __construct(
        public int $amount,
        public Currency $currency,
    ) {
        if ($this->amount < 0) {
            throw new InvalidArgumentException("Amount must be greater than or equal to 0");
        }
    }

    public function add(self $money): self
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidArgumentException("Currency must be equal to $this->currency");
        }

        return new self($money->amount + $this->amount, $this->currency);
    }

    public function multiply(int $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Factor must be non-negative');
        }

        return new self($this->amount * $factor, $this->currency);
    }

    public function equals(self $money): bool
    {
        return $this->amount === $money->amount
            && $this->currency === $money->currency;
    }
}