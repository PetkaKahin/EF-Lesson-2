<?php

namespace Task5\Infrastructure\Repositories;

use Task5\Domain\Order;
use Task5\Domain\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var array<Order>
     */
    private(set) public array $orders;

    public function __construct(
        private readonly string $storage
    )
    {
        if (file_exists($storage)) {
            $raw = file_get_contents($storage);
            $data = json_decode((string)$raw, true);
            if (is_array($data)) {
                $this->orders = $data['orders'] ?? [];
            }
        }
    }

    public function save(Order $order): void
    {
        $this->orders[$order->id] = $order;

        file_put_contents(
            $this->storage,
            json_encode($this->orders, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }

    public function orders(): array
    {
        return $this->orders;
    }

    public function order(string $id): ?Order
    {
        return $this->orders[$id] ?? null;
    }
}