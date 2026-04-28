<?php

use Task5\Application\CreateOrder;
use Task5\Domain\Contracts\NotifyInterface;
use Task5\Domain\Contracts\OrderRepositoryInterface;
use Task5\Domain\Contracts\UserRepositoryInterface;
use Task5\Domain\PromoCodeRulesRegistry;
use Task5\Domain\User;
use Task5\Domain\VO\OrderItem;
use Task5\Domain\VO\PromoCode;
use Task5\Infrastructure\DiContainer;
use Task5\Infrastructure\Notification\EchoLogger;
use Task5\Infrastructure\PromoCodeRules\VipPromoCodeRule;
use Task5\Infrastructure\PromoCodeRules\WelcomePromoCodeRule;
use Task5\Infrastructure\Repositories\OrderRepository;
use Task5\Infrastructure\Repositories\UserRepository;

$storageDir = __DIR__ . '/../var/task5';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0775, true);
}
$usersStorage  = $storageDir . '/users.json';
$ordersStorage = $storageDir . '/orders.json';
file_put_contents($usersStorage, json_encode(['users' => []]));
file_put_contents($ordersStorage, json_encode(['orders' => []]));

$container = new DiContainer();

$container->singleton(UserRepositoryInterface::class, fn() => new UserRepository($usersStorage));
$container->singleton(OrderRepositoryInterface::class, fn() => new OrderRepository($ordersStorage));
$container->singleton(NotifyInterface::class, fn() => new EchoLogger());
$container->singleton(PromoCodeRulesRegistry::class, function () {
    $registry = new PromoCodeRulesRegistry();
    $registry->set(PromoCode::Welcome, new WelcomePromoCodeRule(10));
    $registry->set(PromoCode::Vip, new VipPromoCodeRule(50));
    return $registry;
});
$container->singleton(CreateOrder::class, fn() => new CreateOrder(
    userRepository:  $container->make(UserRepositoryInterface::class),
    orderRepository: $container->make(OrderRepositoryInterface::class),
    rulesRegistry:   $container->make(PromoCodeRulesRegistry::class),
    notifications:   [$container->make(NotifyInterface::class)],
));

$bindings = [
    [UserRepositoryInterface::class,  UserRepository::class],
    [OrderRepositoryInterface::class, OrderRepository::class],
    [NotifyInterface::class,          EchoLogger::class],
    [PromoCodeRulesRegistry::class,   PromoCodeRulesRegistry::class . ' (Welcome 10%, Vip −50)'],
    [CreateOrder::class,              CreateOrder::class],
];

$userRepo  = $container->make(UserRepositoryInterface::class);
$orderRepo = $container->make(OrderRepositoryInterface::class);
$useCase   = $container->make(CreateOrder::class);

$email = 'alice@example.com';
$userRepo->save(new User(
    email: $email,
    ordersConunt: '0',
    createdAt: new DateTimeImmutable('now', new DateTimeZone('UTC')),
));

$items = [
    new OrderItem(price: 500, quantity: 2),
    new OrderItem(price: 150, quantity: 1),
];

$inputRows = [
    ['email',     $email],
    ['items',     '500 × 2 + 150 × 1'],
    ['promoCode', PromoCode::Welcome->value . ' (−10%)'],
    ['tax',       '5%'],
];

ob_start();
$order = $useCase->create(
    items: $items,
    promoCode: PromoCode::Welcome,
    email: $email,
    tax: 5,
);
$logsOutput = trim((string)ob_get_clean());

$resultRows = [
    ['id',         $order->id],
    ['createdAt',  $order->createdAt->format('Y-m-d H:i:s')],
    ['email',      $order->customerEmail],
    ['status',     $order->status->value],
    ['items',      (string)count($order->items)],
    ['subtotal',   number_format($order->payment->subtotal, 2, '.', '')],
    ['discount',   number_format($order->payment->discount, 2, '.', '')],
    ['tax',        number_format($order->payment->tax, 2, '.', '')],
    ['total',      number_format($order->payment->total, 2, '.', '')],
    ['stored in repository', $orderRepo->order($order->id) !== null ? 'yes' : 'no'],
];

$messages = [];
if ($logsOutput !== '') {
    preg_match_all('/\[[^\]]+\][^\[]*/', $logsOutput, $matches);
    $messages = $matches[0] ?: [$logsOutput];
}

$payment = $order->payment;
$invariantHolds = abs($payment->total - ($payment->subtotal - $payment->discount + $payment->tax)) < 0.0001;

$invariantRows = [
    [
        'total = subtotal − discount + tax',
        $invariantHolds ? 'OK' : 'FAIL',
        sprintf(
            '%.2f = %.2f − %.2f + %.2f',
            $payment->total, $payment->subtotal, $payment->discount, $payment->tax
        ),
    ],
];

try {
    $useCase->create(
        items: $items,
        promoCode: PromoCode::Welcome,
        email: 'unknown@example.com',
        tax: 5,
    );
    $invariantRows[] = ['create() с несуществующим email', 'FAIL', 'исключение не выброшено'];
} catch (Throwable $e) {
    $invariantRows[] = ['create() с несуществующим email', 'OK', $e->getMessage()];
}
?>
<section>
    <h2>Задание 5. Убрать антипаттерны</h2>

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