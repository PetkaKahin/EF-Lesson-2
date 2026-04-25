<?php

declare(strict_types=1);

namespace Task2\Factory;

use InvalidArgumentException;
use Task2\DTO\CreateOrderRequest;
use Task2\Entity\Order;
use Task2\Enums\Currency;
use Task2\Enums\OrderStatus;
use Task2\VO\Email;
use Task2\VO\Money;
use Task2\VO\OrderId;
use Task2\VO\OrderItem;

class OrderFactory
{
    public static function createFromRequest(CreateOrderRequest $request): Order
    {
        if (count($request->items) === 0) {
            throw new InvalidArgumentException('Order must contain at least one item');
        }

        $id = new OrderId(uniqid('order_', true));
        $items = [];
        $totalPrice = new Money(0, Currency::from($request->items[0]->price['currency']));

        foreach ($request->items as $item) {
            $money = new Money(
                amount: $item->price['amount'],
                currency: Currency::from($item->price['currency'])
            );

            $totalPrice = $totalPrice->add($money->multiply($item->quantity));

            $items[] = new OrderItem(
                name: $item->name,
                quantity: $item->quantity,
                price: $money,
            );
        }

        $email = new Email($request->email);
        $status = OrderStatus::Draft;

        return new Order($id, $email, $items, $totalPrice, $status);
    }
}