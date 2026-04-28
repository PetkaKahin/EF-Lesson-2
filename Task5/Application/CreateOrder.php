<?php

namespace Task5\Application;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Task5\Domain\Contracts\NotifyInterface;
use Task5\Domain\Contracts\OrderRepositoryInterface;
use Task5\Domain\Contracts\UserRepositoryInterface;
use Task5\Domain\Order;
use Task5\Domain\PromoCodeRulesRegistry;
use Task5\Domain\VO\OrderItem;
use Task5\Domain\VO\OrderStatus;
use Task5\Domain\VO\PromoCode;

class CreateOrder
{
    /**
     * @param array<NotifyInterface> $notifications
     */
    public function __construct(
        private UserRepositoryInterface  $userRepository,
        private OrderRepositoryInterface $orderRepository,
        private PromoCodeRulesRegistry   $rulesRegistry,
        private array                    $notifications = []
    )
    {
        foreach ($notifications as $notification) {
            if (!$notification instanceof NotifyInterface) {
                throw new RuntimeException('notification should implement NotifyInterface');
            }
        }
    }

    /**
     * @param array<OrderItem> $items
     */
    public function create(
        array     $items,
        PromoCode $promoCode,
        string    $email = '',
        float     $tax = 0.0
    ): Order
    {
        $user = $this->userRepository->user($email);

        if ($user === null) {
            throw new RuntimeException('User not found');
        }

        $order = new Order(
            id: uniqid('order_', true),
            createdAt: new DateTimeImmutable("now", new DateTimeZone("UTC")),
            customerEmail: $email,
            status: OrderStatus::New,
            items: $items,
        );

        $order->applyPromoCode($this->rulesRegistry->get($promoCode));
        $order->applyTax($tax);

        $this->userRepository->save($user);
        $this->orderRepository->save($order);
        $this->sendNotifications($order);

        return $order;
    }

    private function sendNotifications(Order $order): void
    {
        foreach ($this->notifications as $notification) {
            $notification->send($order);
        }
    }
}