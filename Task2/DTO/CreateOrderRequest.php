<?php

declare(strict_types=1);

namespace Task2\DTO;

final readonly class CreateOrderRequest
{
    public function __construct(
        public string $email,
        public array $items,
        public array $money,
    ) {}
}