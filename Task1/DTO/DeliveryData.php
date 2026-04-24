<?php

declare(strict_types=1);

namespace Task1\DTO;

use InvalidArgumentException;
use Task1\Enums\DeliveryType;

final readonly class DeliveryData
{
    public string $address;

    public function __construct(
        public DeliveryType $type,
        string              $address = ''
    )
    {
        $this->address = trim($address);
        $this->validate();
    }

    public function toArray(): array
    {
        return [
            'type'    => $this->type,
            'address' => $this->address,
        ];
    }

    private function validate(): void
    {
        if ($this->type !== DeliveryType::Pickup) {
            if ($this->address === '') {
                throw new InvalidArgumentException('address is required for courier and post');
            }
        }
    }
}
