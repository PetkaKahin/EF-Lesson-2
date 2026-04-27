<?php

namespace Task4\Domain\Requests;

class PayOrderRequest
{
    public function __construct(
        public readonly string $orderId
    )
    {}
}