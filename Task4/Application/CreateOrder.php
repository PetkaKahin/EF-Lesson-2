<?php

declare(strict_types=1);

namespace Task4\Application;

use InvalidArgumentException;
use Task4\Domain\Contracts\ClockInterface;
use Task4\Domain\Contracts\LoggerInterface;
use Task4\Domain\Contracts\OrderRepositoryInterface;
use Task4\Domain\Order;
use Task4\Domain\Requests\CreateOrderRequest;

class CreateOrder
{
    public function __construct(
        private readonly ClockInterface           $clock,
        private readonly OrderRepositoryInterface $repository,
        private readonly LoggerInterface          $logger,
    )
    {
    }

    public function create(CreateOrderRequest $request): Order
    {
        $this->validate($request);

        $order = new Order(
            $request->amount,
            $this->clock->now(),
        );

        $this->logger->log("[$order->createdAt] Order created");
        $this->repository->save($order);

        return $order;
    }

    private function validate(CreateOrderRequest $request): void
    {
        if ($request->amount < 0) {
            throw new InvalidArgumentException('Amount must be positive');
        }
    }
}