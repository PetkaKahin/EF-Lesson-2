<?php

declare(strict_types=1);

namespace Task2\VO;

use InvalidArgumentException;

final readonly class Email
{
    public function __construct(
        public string $email,
    ) {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email');
        }
    }

    public function equals(self $email): bool
    {
        return $this->email === $email->email;
    }
}