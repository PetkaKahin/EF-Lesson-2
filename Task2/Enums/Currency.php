<?php

declare(strict_types=1);

namespace Task2\Enums;

enum Currency: string
{
    case RUB = "RUB";
    case USD = "USD";
    case EUR = "EUR";
    case CNY = "CNY";
}
