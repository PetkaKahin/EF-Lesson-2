<?php

namespace Task5\Domain;

use DateTimeImmutable;

class User
{
    public function __construct(
        private(set) public string $email,
        private(set) public string $ordersConunt,
        private(set) public DateTimeImmutable $createdAt,
    ) {}
}