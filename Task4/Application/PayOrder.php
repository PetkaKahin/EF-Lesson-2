<?php

declare(strict_types=1);

namespace Task4\Application;

use RuntimeException;
use Task4\Application\DTO\PayOrderRequest;
use Task4\Domain\Contracts\ClockInterface;
use Task4\Domain\Contracts\LoggerInterface;
use Task4\Domain\Contracts\OrderRepositoryInterface;

class PayOrder
{
    public function __construct(
        private readonly ClockInterface $clock,
        private readonly OrderRepositoryInterface $repository,
        private readonly LoggerInterface $logger,
    ) {}

    public function pay(PayOrderRequest $order): void
    {
        $order = $this->repository->findById($order->orderId);

        if ($order === null) {
            throw new RuntimeException('Order not found');
        }

        $order->markPaid($this->clock->now());
        $this->logger->log("[$order->paidAt] Order marked as paid");

        $this->repository->save($order);
    }
}
