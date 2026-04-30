<?php

declare(strict_types=1);

namespace Task6\Domain;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Task6\Domain\Contracts\PromoCodeRuleInterface;
use Task6\Domain\DTO\PaymentData;
use Task6\Domain\VO\Delivery;
use Task6\Domain\VO\OrderItem;
use Task6\Domain\VO\OrderStatus;

class Order
{
    private const int MAX_APPLIED_PROMO_CODES = 2;

    private(set) public PaymentData $payment;
    /**
     * @var array<string>
     */
    private(set) public array $appliedPromoCodes = [];

    /**
     * @param array<OrderItem> $items
     */
    public function __construct(
        private(set) public string            $id,
        private(set) public DateTimeImmutable $createdAt,
        private(set) public string            $customerEmail,
        private(set) public OrderStatus       $status,
        private(set) public Delivery          $deliveryData,
        private(set) public array             $items = [],
    )
    {
        $this->validateEmail();
        $this->validateItems();

        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        $this->payment = new PaymentData(subtotal: $subtotal, delivery: $this->deliveryData->defaultPrice);
        $this->recalculateTotal();
    }

    public function applyPromoCode(PromoCodeRuleInterface $rule): void
    {
        if (count($this->appliedPromoCodes) >= self::MAX_APPLIED_PROMO_CODES) {
            throw new DomainException("No more than " . self::MAX_APPLIED_PROMO_CODES . " promo codes can be applied.");
        }
        if (isset($this->appliedPromoCodes[$rule->getPromoCode()])) {
            throw new DomainException("Promo code '{$rule->getPromoCode()}' already applied.");
        }

        $this->payment = $rule->apply($this->payment);
        $this->recalculateTotal();

        $this->appliedPromoCodes[$rule->getPromoCode()] = $rule->getPromoCode();
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
            delivery: $this->payment->delivery,
        );
        $this->recalculateTotal();
    }

    private function recalculateTotal(): void
    {
        $total = $this->payment->subtotal - $this->payment->discount + $this->payment->tax + $this->payment->delivery;

        if ($total < 0) {
            $total = 0;
        }

        $this->payment = new PaymentData(
            subtotal: $this->payment->subtotal,
            discount: $this->payment->discount,
            tax: $this->payment->tax,
            total: $total,
            delivery: $this->payment->delivery,
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