<?php

namespace Task5\Domain;

use DateTimeImmutable;
use InvalidArgumentException;
use Task5\Domain\Contracts\PromoCodeRuleInterface;
use Task5\Domain\DTO\PaymentData;
use Task5\Domain\VO\OrderItem;
use Task5\Domain\VO\OrderStatus;

class Order
{
    private(set) public PaymentData $payment;

    /**
     * @param array<OrderItem> $items
     */
    public function __construct(
        private(set) public string            $id,
        private(set) public DateTimeImmutable $createdAt,
        private(set) public string            $customerEmail,
        private(set) public OrderStatus       $status,
        private(set) public array             $items = [],
    )
    {
        $this->validateEmail();
        $this->validateItems();

        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        $this->payment = new PaymentData(subtotal: $subtotal);
    }

    public function applyPromoCode(PromoCodeRuleInterface $rule): void
    {
        $this->payment = $rule->apply($this->payment);
        $this->recalculateTotal();
    }

    public function applyTax(float $taxPercent): void
    {
        if ($taxPercent < 0 || $taxPercent > 100) {
            throw new InvalidArgumentException("Tax must be between 0 and 100");
        }

        $tax = ($this->payment->subtotal - $this->payment->discount) * ($taxPercent / 100);

        $this->payment = new PaymentData(
            subtotal: $this->payment->subtotal,
            discount: $this->payment->discount,
            tax: $tax,
            total: $this->payment->total,
        );
        $this->recalculateTotal();
    }

    private function recalculateTotal(): void
    {
        $total = $this->payment->subtotal - $this->payment->discount + $this->payment->tax;

        $this->payment = new PaymentData(
            subtotal: $this->payment->subtotal,
            discount: $this->payment->discount,
            tax: $this->payment->tax,
            total: $total,
        );
    }

    private function validateEmail(): void
    {
        if (!filter_var($this->customerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email '{$this->customerEmail}' is not valid");
        }
    }

    private function validateItems(): void
    {
        foreach ($this->items as $item) {
            if (!$item instanceof OrderItem) {
                throw new InvalidArgumentException('Each item must be an instance of OrderItem');
            }
        }
    }
}