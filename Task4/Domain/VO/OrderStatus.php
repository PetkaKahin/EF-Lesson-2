<?php

declare(strict_types=1);

namespace Task4\Domain\VO;

enum OrderStatus: string
{
    case Draft = 'draft';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
}
