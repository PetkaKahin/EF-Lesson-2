<?php

use Task6\Application\CreateOrder;
use Task6\Application\DTO\CreateOrderRequest;
use Task6\Domain\Contracts\NotifyInterface;
use Task6\Domain\Contracts\OrderRepositoryInterface;
use Task6\Domain\Contracts\UserRepositoryInterface;
use Task6\Domain\PromoCodeRulesRegistry;
use Task6\Domain\User;
use Task6\Domain\VO\Delivery;
use Task6\Domain\VO\DeliveryType;
use Task6\Domain\VO\OrderItem;
use Task6\Infrastructure\DiContainer;
use Task6\Infrastructure\Notification\EchoLogger;
use Task6\Infrastructure\PromoCodeRules\FreeShipPromoCodeRule;
use Task6\Infrastructure\PromoCodeRules\VipPromoCodeRule;
use Task6\Infrastructure\PromoCodeRules\Welcome10PromoCodeRule;
use Task6\Infrastructure\Repositories\OrderRepository;
use Task6\Infrastructure\Repositories\UserRepository;

$storageDir = __DIR__ . '/../var/task6';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0775, true);
}
$usersStorage = $storageDir . '/users.json';
$ordersStorage = $storageDir . '/orders.json';
file_put_contents($usersStorage, json_encode(['users' => []]));
file_put_contents($ordersStorage, json_encode(['orders' => []]));

$container = new DiContainer();

$container->singleton(UserRepositoryInterface::class, fn() => new UserRepository($usersStorage));
$container->singleton(OrderRepositoryInterface::class, fn() => new OrderRepository($ordersStorage));
$container->singleton(NotifyInterface::class, fn() => new EchoLogger());
$container->singleton(PromoCodeRulesRegistry::class, function () {
    $registry = new PromoCodeRulesRegistry();
    $registry->set('WELCOME10', new Welcome10PromoCodeRule());
    $registry->set('SHIPFREE', new FreeShipPromoCodeRule());
    $registry->set('FREESHIP', new FreeShipPromoCodeRule());
    $registry->set('VIP', new VipPromoCodeRule(
        threshold: 2000,
        discountBeforeThreshold: 100,
        discountAfterThreshold: 300,
    ));
    return $registry;
});
$container->singleton(CreateOrder::class, fn() => new CreateOrder(
    userRepository: $container->make(UserRepositoryInterface::class),
    orderRepository: $container->make(OrderRepositoryInterface::class),
    promoCodeRulesRegistry: $container->make(PromoCodeRulesRegistry::class),
    notifications: [$container->make(NotifyInterface::class)],
));

$bindings = [
    [UserRepositoryInterface::class, UserRepository::class],
    [OrderRepositoryInterface::class, OrderRepository::class],
    [NotifyInterface::class, EchoLogger::class],
    [PromoCodeRulesRegistry::class, PromoCodeRulesRegistry::class . ' (WELCOME10, SHIPFREE, FREESHIP, VIP)'],
    [CreateOrder::class, CreateOrder::class],
];

$userRepo = $container->make(UserRepositoryInterface::class);
$orderRepo = $container->make(OrderRepositoryInterface::class);
$useCase = $container->make(CreateOrder::class);

$email = 'alice@example.com';
$userRepo->save(new User(
    email: $email,
    ordersCount: 0,
    createdAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
));

$items = [
    new OrderItem(sku: 'A1', title: 'Keyboard', price: 500, quantity: 2),
    new OrderItem(sku: 'B2', title: 'Mouse', price: 150, quantity: 1),
];
$delivery = new Delivery(
    address: 'Moscow, Tverskaya 1',
    type: DeliveryType::Courier,
    defaultPrice: 199,
);
$promoCodes = ['WELCOME10', 'SHIPFREE'];

$inputRows = [
    ['email', $email],
    ['items', 'Keyboard 500 x 2 + Mouse 150 x 1'],
    ['delivery', $delivery->type->value . ', ' . number_format($delivery->defaultPrice, 2, '.', '')],
    ['promoCodes', implode(', ', $promoCodes)],
    ['tax', '5%'],
];

ob_start();
$order = $useCase->create(new CreateOrderRequest(
    customerEmail: $email,
    delivery: $delivery,
    items: $items,
    promoCodes: $promoCodes,
    taxPercent: 5,
));
$logsOutput = trim((string)ob_get_clean());

$storedUser = $userRepo->user($email);
$payment = $order->payment;

$resultRows = [
    ['id', $order->id],
    ['createdAt', $order->createdAt->format('Y-m-d H:i:s')],
    ['email', $order->customerEmail],
    ['status', $order->status->value],
    ['items', (string)count($order->items)],
    ['delivery', number_format($payment->delivery, 2, '.', '')],
    ['subtotal', number_format($payment->subtotal, 2, '.', '')],
    ['discount', number_format($payment->discount, 2, '.', '')],
    ['tax', number_format($payment->tax, 2, '.', '')],
    ['total', number_format($payment->total, 2, '.', '')],
    ['applied promo codes', implode(', ', $order->appliedPromoCodes)],
    ['stored in repository', $orderRepo->order($order->id) !== null ? 'yes' : 'no'],
    ['user orders count', $storedUser !== null ? (string)$storedUser->ordersCount : 'not found'],
];

$messages = [];
if ($logsOutput !== '') {
    preg_match_all('/\[[^\]]+\][^\[]*/', $logsOutput, $matches);
    $messages = $matches[0] ?: [$logsOutput];
}

$invariantHolds = abs($payment->total - ($payment->subtotal - $payment->discount + $payment->tax + $payment->delivery)) < 0.0001;

$invariantRows = [
    [
        'total = subtotal - discount + tax + delivery',
        $invariantHolds ? 'OK' : 'FAIL',
        sprintf(
            '%.2f = %.2f - %.2f + %.2f + %.2f',
            $payment->total,
            $payment->subtotal,
            $payment->discount,
            $payment->tax,
            $payment->delivery
        ),
    ],
];

try {
    $useCase->create(new CreateOrderRequest(
        customerEmail: $email,
        delivery: $delivery,
        items: $items,
        promoCodes: ['WELCOME10', 'FREESHIP', 'VIP'],
        taxPercent: 5,
    ));
    $invariantRows[] = ['create() with 3 promo codes', 'FAIL', 'exception was not thrown'];
} catch (Throwable $e) {
    $invariantRows[] = ['create() with 3 promo codes', 'OK', $e->getMessage()];
}
?>
<section>
    <h2>Задание 6. Финал блока — “фича в легаси”</h2>

    <h3>Регистрации в DI-контейнере</h3>
    <table>
        <thead>
        <tr><th>Абстракция</th><th>Реализация</th></tr>
        </thead>
        <tbody>
        <?php foreach ($bindings as [$abstract, $concrete]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($abstract) ?></td>
                <td><?= htmlspecialchars($concrete) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Входные данные</h3>
    <table>
        <thead>
        <tr><th>Поле</th><th>Значение</th></tr>
        </thead>
        <tbody>
        <?php foreach ($inputRows as [$field, $value]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($field) ?></td>
                <td><?= htmlspecialchars((string)$value) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Результат use case</h3>
    <table>
        <thead>
        <tr><th>Поле</th><th>Значение</th></tr>
        </thead>
        <tbody>
        <?php foreach ($resultRows as [$field, $value]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($field) ?></td>
                <td><?= htmlspecialchars((string)$value) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Уведомления (NotifyInterface)</h3>
    <table>
        <thead>
        <tr><th>#</th><th>Сообщение</th></tr>
        </thead>
        <tbody>
        <?php if ($messages === []): ?>
            <tr>
                <td class="field">-</td>
                <td>Нет сообщений</td>
            </tr>
        <?php else: ?>
            <?php foreach ($messages as $idx => $message): ?>
                <tr>
                    <td class="field"><?= $idx + 1 ?></td>
                    <td><?= htmlspecialchars(trim((string)$message)) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <h3>Проверка инвариантов</h3>
    <table>
        <thead>
        <tr><th>Действие</th><th>Результат</th><th>Подробности</th></tr>
        </thead>
        <tbody>
        <?php foreach ($invariantRows as [$action, $outcome, $detail]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($action) ?></td>
                <td class="<?= $outcome === 'OK' ? 'ok' : 'err' ?>"><?= htmlspecialchars($outcome) ?></td>
                <td><?= htmlspecialchars((string)$detail) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
