<?php

declare(strict_types=1);

namespace Task2\VO;

use InvalidArgumentException;

final readonly class OrderId
{
    public function __construct(
        public string $id,
    ) {
        if ($id === '') {
            throw new InvalidArgumentException('Order id cannot be empty');
        }
    }

    public function equals(self $orderId): bool
    {
        return $orderId->id === $this->id;
    }
}