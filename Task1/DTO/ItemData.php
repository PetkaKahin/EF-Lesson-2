<?php

declare(strict_types=1);

namespace Task1\DTO;

use InvalidArgumentException;

final readonly class ItemData
{
    public function __construct(
        public string $sku,
        public string $title,
        public float $price,
        public int $qty,
    )
    {
        $this->validate();
    }

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'title' => $this->title,
            'price' => $this->price,
            'qty' => $this->qty,
        ];
    }

    private function validate(): void
    {
        if ($this->sku === '') {
            throw new InvalidArgumentException('sku is required');
        }

        if ($this->title === '') {
            throw new InvalidArgumentException('title is required');
        }

        if ($this->price < 0) {
            throw new InvalidArgumentException('price must be >= 0');
        }

        if ($this->qty < 1) {
            throw new InvalidArgumentException('qty must be >= 1');
        }
    }
}
