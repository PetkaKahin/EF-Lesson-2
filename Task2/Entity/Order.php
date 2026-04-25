<?php

declare(strict_types=1);

namespace Task2\Entity;

use DomainException;
use Task2\Enums\OrderStatus;
use Task2\VO\Email;
use Task2\VO\Money;
use Task2\VO\OrderId;
use Task2\VO\OrderItem;

final class Order
{
    /**
     * @param array<OrderItem> $items
     */
    public function __construct(
        public private(set) OrderId     $id,
        public private(set) Email       $email,
        public private(set) array       $items, // список позиций из тз, надеюсь правильно понял
        public private(set) Money       $totalAmount,
        public private(set) OrderStatus $status,
    ) {}

    public function confirm(): void
    {
        if ($this->status !== OrderStatus::Draft) {
            throw new DomainException('Cannot confirm order in status ' . $this->status->value);
        }
        if ($this->items === []) {
            throw new DomainException('Cannot confirm empty order');
        }
        $this->status = OrderStatus::PendingPayment;
    }

    public function markPaid(): void {
        if ($this->status !== OrderStatus::PendingPayment) {
            throw new DomainException('Cannot pay order in status ' . $this->status->value);
        }
        $this->status = OrderStatus::Paid;
    }

    public function startProcessing(): void
    {
        if ($this->status !== OrderStatus::Paid) {
            throw new DomainException('Cannot start processing order in status ' . $this->status->value);
        }

        $this->status = OrderStatus::Processing;
    }

    public function ship(): void
    {
        if ($this->status !== OrderStatus::Processing) {
            throw new DomainException('Cannot ship order in status ' . $this->status->value);
        }

        $this->status = OrderStatus::Shipped;
    }

    public function deliver(): void
    {
        if ($this->status !== OrderStatus::Shipped) {
            throw new DomainException('Cannot deliver order in status ' . $this->status->value);
        }

        $this->status = OrderStatus::Delivered;
    }

    public function cancel(): void
    {
        if (!in_array($this->status, [
            OrderStatus::Draft,
            OrderStatus::PendingPayment,
            OrderStatus::Paid,
            OrderStatus::Processing,
        ], true)) {
            throw new DomainException('Cannot cancel order in status ' . $this->status->value);
        }

        $this->status = OrderStatus::Cancelled;
    }

    public function refund(): void
    {
        if (!in_array($this->status, [
            OrderStatus::Paid,
            OrderStatus::Processing,
            OrderStatus::Shipped,
            OrderStatus::Delivered,
        ], true)) {
            throw new DomainException('Cannot refund order in status ' . $this->status->value);
        }

        $this->status = OrderStatus::Refunded;
    }
}