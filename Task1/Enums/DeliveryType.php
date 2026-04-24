<?php

namespace Task1\Enums;

enum DeliveryType: string
{
    case Courier = 'courier';
    case Pickup = 'pickup';
    case Post = 'post';

    public function cost(float $subtotal): float
    {
        return match ($this) {
            self::Courier => $subtotal >= 1000 ? 0 : 199,
            self::Pickup  => 0,
            self::Post    => 299,
        };
    }
}
