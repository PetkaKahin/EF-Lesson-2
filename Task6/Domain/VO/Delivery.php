<?php

namespace Task6\Domain\VO;

use DomainException;

readonly class Delivery
{
    public function __construct(
        public string       $address,
        public DeliveryType $type,
        public float        $defaultPrice,
    )
    {
        if ($this->type === DeliveryType::Courier ||
            $this->type === DeliveryType::Post) {
            if ($this->address === '') {
                throw new DomainException('Address should not be empty');
            }
        }

        if ($this->defaultPrice < 0) {
            throw new DomainException('Default price cannot be negative');
        }
    }
}