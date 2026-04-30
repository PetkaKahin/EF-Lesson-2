<?php

declare(strict_types=1);

namespace Task6\Domain;

use DateTimeImmutable;

class User
{
    public function __construct(
        private(set) public string $email,
        private(set) public int $ordersCount,
        private(set) public DateTimeImmutable $createdAt,
    ) {}

    public function registerOrder(): void
    {
        $this->ordersCount++;
    }
}
