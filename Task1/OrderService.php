<?php

declare(strict_types=1);

namespace Task1;

use Task1\Contracts\PricingCalculatorInterface;
use Task1\DTO\OrderData;
use Task1\Contracts\OrderRepositoryInterface;
use Task1\Contracts\NotifyInterface;

class OrderService
{
    /**
     * @param array<NotifyInterface> $notifications
     */
    public function __construct(
        private OrderRepositoryInterface   $repository,
        private array                      $notifications,
        private PricingCalculatorInterface $pricingCalculator,
    ){}

    public function create(OrderData $data): Order
    {
        $pricing = $this->pricingCalculator->calculate($data);

        $paymentStatus = $data->payment->method->toStatus();

        $order = new Order(
            customer: $data->customer,
            items: $data->items,
            delivery: $data->delivery,
            payment: $data->payment,
            paymentStatus: $paymentStatus,
            pricing: $pricing,
        );

        $this->repository->setOrder($order);

        foreach ($this->notifications as $notify) {
            $notify->send($order);
        }

        return $order;
    }
}