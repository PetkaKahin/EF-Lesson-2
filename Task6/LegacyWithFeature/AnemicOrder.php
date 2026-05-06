<?php

namespace Task6\LegacyWithFeature;

class AnemicOrder
{
    public string $id;
    public string $customerEmail;
    public array $items = [];
    public float $subtotal = 0.0;
    public float $discount = 0.0;
    public float $tax = 0.0;
    public float $total = 0.0;
    public string $status;
    public string $createdAt;

    public function __construct()
    {
        $this->id = '';
        $this->customerEmail = '';
        $this->status = 'new';
        $this->createdAt = date('c');
    }
}