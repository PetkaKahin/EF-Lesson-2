<?php

namespace Task3\Services;

use Task3\Contracts\ClockInterface;
use Task3\Contracts\LoggerInterface;
use Task3\Contracts\OrderRepositoryInterface;

class ReportGenerator
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private LoggerInterface $logger,
        private ClockInterface $clock,
    ) {}

    public function generate(): void
    {
        $orders = $this->orderRepository->getOrders();

        $total = 0;

        foreach ($orders as $order) {
            $total += $order->getAmount();
        }

        $log = "Generated at: " . $this->clock->now()->format('Y-m-d H:i:s') . PHP_EOL
            . "Orders count: " . count($orders) . PHP_EOL
            . "Total amount: " . $total . PHP_EOL;

        $this->logger->log($log);
    }
}