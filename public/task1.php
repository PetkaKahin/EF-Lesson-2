<?php

use Legacy\OrderService as LegacyOrderService;
use Task1\DTO\CustomerData;
use Task1\DTO\DeliveryData;
use Task1\DTO\ItemData;
use Task1\DTO\OrderData;
use Task1\DTO\PaymentData;
use Task1\Enums\DeliveryType;
use Task1\Enums\PaymentMethod;
use Task1\Enums\PromoCode;
use Task1\Notifications\AdminOrderNotify;
use Task1\Notifications\CustomerOrderNotify;
use Task1\OrderRepository;
use Task1\OrderService;
use Task1\PricingCalculator;

$input = [
    'customer' => ['email' => 'alice@example.com', 'name' => 'Alice'],
    'items' => [
        ['sku' => 'A1', 'title' => 'Widget', 'price' => 500, 'qty' => 2],
        ['sku' => 'B2', 'title' => 'Gadget', 'price' => 150, 'qty' => 1],
    ],
    'payment' => ['method' => 'card', 'cardNumber' => '4111111111111111'],
    'delivery' => ['type' => 'courier', 'address' => 'Baker st 221b'],
    'promoCode' => 'WELCOME10',
];

$legacyOrder = new LegacyOrderService()->createOrder($input)['order'];

$storageFile = __DIR__ . '/../var/orders.json';
if (!is_dir(dirname($storageFile))) {
    mkdir(dirname($storageFile), 0777, true);
}
if (!file_exists($storageFile)) {
    file_put_contents($storageFile, '[]');
}

$orderData = new OrderData(
    customer: new CustomerData('alice@example.com', 'Alice'),
    items: [
        new ItemData('A1', 'Widget', 500, 2),
        new ItemData('B2', 'Gadget', 150, 1),
    ],
    payment: new PaymentData(PaymentMethod::Card, '4111111111111111'),
    delivery: new DeliveryData(DeliveryType::Courier, 'Baker st 221b'),
    promoCode: PromoCode::Welcome10,
);

$service = new OrderService(
    repository: new OrderRepository($storageFile),
    notifications: [
        new AdminOrderNotify('admin@example.com', true),
        new CustomerOrderNotify(true),
    ],
    pricingCalculator: new PricingCalculator(),
);

$newOrder = $service->create($orderData);

$rows = [
    ['id',            $legacyOrder['id'],                       $newOrder->id],
    ['subtotal',      $legacyOrder['pricing']['subtotal'],      $newOrder->pricing->subtotal],
    ['discount',      $legacyOrder['pricing']['discount'],      $newOrder->pricing->discount],
    ['tax',           $legacyOrder['pricing']['tax'],           $newOrder->pricing->tax],
    ['deliveryCost',  $legacyOrder['delivery']['cost'],         $newOrder->pricing->deliveryCost],
    ['total',         $legacyOrder['pricing']['total'],         $newOrder->pricing->total],
    ['promoCode',     $legacyOrder['pricing']['promoCode'],     $newOrder->pricing->promoCode?->value],
    ['paymentStatus', $legacyOrder['payment']['status'],        $newOrder->paymentStatus->value],
];
?>
<section>
    <h2>Задание 1. Рефакторинг “грязного” кода в SOLID</h2>
    <table>
        <thead>
            <tr>
                <th>Поле</th>
                <th>Legacy</th>
                <th>Новая реализация</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as [$field, $legacy, $new]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($field) ?></td>
                <td><?= htmlspecialchars((string)$legacy) ?></td>
                <td><?= htmlspecialchars((string)$new) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>