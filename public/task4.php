<?php

use Task4\Application\CreateOrder;
use Task4\Application\PayOrder;
use Task4\Domain\Contracts\ClockInterface;
use Task4\Domain\Contracts\LoggerInterface;
use Task4\Domain\Contracts\OrderRepositoryInterface;
use Task4\Domain\Requests\CreateOrderRequest;
use Task4\Domain\Requests\PayOrderRequest;
use Task4\Infrastructure\EchoLogger;
use Task4\Infrastructure\InMemoryOrderRepository;
use Task4\Infrastructure\SystemClock;

$clock = new SystemClock();
$logger = new EchoLogger();
$repository = new InMemoryOrderRepository();

$createOrder = new CreateOrder(
    clock: $clock,
    repository: $repository,
    logger: $logger,
);
$payOrder = new PayOrder(
    clock: $clock,
    repository: $repository,
    logger: $logger,
);

$bindings = [
    [ClockInterface::class, SystemClock::class],
    [LoggerInterface::class, EchoLogger::class],
    [OrderRepositoryInterface::class, InMemoryOrderRepository::class],
];

$createRequest = new CreateOrderRequest(amount: 1150);

ob_start();
$order = $createOrder->create($createRequest);
$payOrder->pay(new PayOrderRequest($order->id));
$logsOutput = trim((string)ob_get_clean());

$storedOrder = $repository->findById($order->id);

$resultRows = [
    ['id', $order->id],
    ['amount', $order->amount],
    ['status', $order->status->value],
    ['createdAt', $order->createdAt],
    ['paidAt', isset($order->paidAt) ? (string)$order->paidAt : 'null'],
    ['stored in repository', $storedOrder !== null ? 'yes' : 'no'],
    ['orders count', count($repository->getOrders())],
];

$messages = [];
if ($logsOutput !== '') {
    preg_match_all('/\[[^\]]+\][^\[]*/', $logsOutput, $matches);
    $messages = $matches[0] ?: [$logsOutput];
}

$transitionRows = [];
try {
    $payOrder->pay(new PayOrderRequest($order->id));
    $transitionRows[] = ['pay() from paid', 'OK', 'status = ' . $order->status->value];
} catch (Throwable $e) {
    $transitionRows[] = ['pay() from paid', 'EXCEPTION', $e->getMessage()];
}
?>
<section>
    <h2>Задание 4. Границы Domain/Application/Infrastructure</h2>

    <h3>Связка интерфейсов и инфраструктуры</h3>
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

    <h3>Логи (EchoLogger)</h3>
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

    <h3>Проверка инварианта</h3>
    <table>
        <thead>
            <tr><th>Действие</th><th>Результат</th><th>Подробности</th></tr>
        </thead>
        <tbody>
        <?php foreach ($transitionRows as [$action, $outcome, $detail]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($action) ?></td>
                <td class="<?= $outcome === 'OK' ? 'ok' : 'err' ?>"><?= htmlspecialchars($outcome) ?></td>
                <td><?= htmlspecialchars((string)$detail) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
