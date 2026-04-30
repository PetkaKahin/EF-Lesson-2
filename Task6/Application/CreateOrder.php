<?php

declare(strict_types=1);

namespace Task6\Application;

use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;
use Task6\Application\DTO\CreateOrderRequest;
use Task6\Domain\Contracts\NotifyInterface;
use Task6\Domain\Contracts\OrderRepositoryInterface;
use Task6\Domain\Contracts\UserRepositoryInterface;
use Task6\Domain\Order;
use Task6\Domain\PromoCodeRulesRegistry;
use Task6\Domain\VO\OrderStatus;

final class CreateOrder
{
    /**
     * @param array<NotifyInterface> $notifications
     */
    public function __construct(
        private UserRepositoryInterface  $userRepository,
        private OrderRepositoryInterface $orderRepository,
        private PromoCodeRulesRegistry   $promoCodeRulesRegistry,
        private array                    $notifications = [],
    )
    {
        foreach ($this->notifications as $notification) {
            if (!$notification instanceof NotifyInterface) {
                throw new RuntimeException('Notification should implement NotifyInterface');
            }
        }
    }

    public function create(CreateOrderRequest $request): Order
    {
        $user = $this->userRepository->user($request->customerEmail);

        if ($user === null) {
            throw new RuntimeException('User not found');
        }

        $order = new Order(
            id: uniqid('order_', true),
            createdAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
            customerEmail: $request->customerEmail,
            status: OrderStatus::New,
            deliveryData: $request->delivery,
            items: $request->items,
        );

        foreach ($request->promoCodes as $promoCode) {
            $order->applyPromoCode($this->promoCodeRulesRegistry->get($promoCode));
        }

        $order->applyTax($request->taxPercent);

        $user->registerOrder();
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
