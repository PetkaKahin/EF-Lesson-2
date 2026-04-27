<?php

declare(strict_types=1);

namespace Task4\Domain;

use DateTimeImmutable;
use DomainException;
use Task4\Domain\VO\OrderStatus;

class Order
{
    // Поля можно VO сделать, но думаю тут и примитивы пойдут для учебки
    public readonly string          $id;
    private(set) public string      $createdAt;
    private(set) public string|null $paidAt;
    private(set) public OrderStatus $status;

    public function __construct(
        private(set) public float $amount,
        DateTimeImmutable $createdAt
    )
    {
        if ($amount < 0) {
            throw new DomainException('Amount must be positive');
        }

        $this->status = OrderStatus::Draft;
        $this->createdAt = $createdAt->format('Y-m-d H:i:s');

        $this->id = uniqid('order_', true);
    }

    public function markPaid(DateTimeImmutable $paidAt): void
    {
        if ($this->status !== OrderStatus::Draft) {
            throw new DomainException("Cannot mark order as paid from status: {$this->status->value}.");
        }

        $this->paidAt = $paidAt->format('Y-m-d H:i:s');
        $this->status = OrderStatus::Paid;
    }

    public function markShipped(): void
    {
        if ($this->status !== OrderStatus::Paid) {
            throw new DomainException("Cannot mark order as shipped from status {$this->status->value}.");
        }

        $this->status = OrderStatus::Shipped;
    }

    public function markCancelled(): void
    {
        // всегда можем отменить
        $this->status = OrderStatus::Cancelled;
    }
}