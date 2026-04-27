<?php

use Task3\Contracts\ClockInterface;
use Task3\Contracts\LoggerInterface;
use Task3\Contracts\OrderRepositoryInterface;
use Task3\DiContainer;
use Task3\Infrastructure\EchoLogger;
use Task3\Infrastructure\InMemoryOrderRepository;
use Task3\Infrastructure\SystemClock;
use Task3\Services\ReportGenerator;

$container = new DiContainer();

$container->singleton(LoggerInterface::class, fn() => new EchoLogger());
$container->singleton(OrderRepositoryInterface::class, fn() => new InMemoryOrderRepository());
$container->singleton(ClockInterface::class, fn() => new SystemClock());
$container->singleton(ReportGenerator::class, fn() => new ReportGenerator(
    orderRepository: $container->make(OrderRepositoryInterface::class),
    logger: $container->make(LoggerInterface::class),
    clock: $container->make(ClockInterface::class),
));

$bindings = [
    [LoggerInterface::class,          EchoLogger::class],
    [OrderRepositoryInterface::class, InMemoryOrderRepository::class],
    [ClockInterface::class,           SystemClock::class],
    [ReportGenerator::class,          ReportGenerator::class],
];

$report = $container->make(ReportGenerator::class);
$singletonCheck = $report === $container->make(ReportGenerator::class);

ob_start();
$report->generate();
$output = ob_get_clean();

$reportRows = [];
foreach (explode(PHP_EOL, trim($output)) as $line) {
    if ($line === '') {
        continue;
    }
    [$field, $value] = array_map('trim', explode(':', $line, 2));
    $reportRows[] = [$field, $value];
}
?>
<section>
    <h2>Задание 3. DI-контейнер и инверсия зависимостей</h2>

    <h3>Регистрации в контейнере</h3>
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

    <h3>Результат ReportGenerator::generate()</h3>
    <table>
        <thead>
            <tr><th>Поле</th><th>Значение</th></tr>
        </thead>
        <tbody>
        <?php foreach ($reportRows as [$field, $value]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($field) ?></td>
                <td><?= htmlspecialchars($value) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Проверка singleton</h3>
    <table>
        <thead>
            <tr><th>Действие</th><th>Результат</th><th>Подробности</th></tr>
        </thead>
        <tbody>
            <tr>
                <td class="field">make(ReportGenerator) дважды</td>
                <td class="<?= $singletonCheck ? 'ok' : 'err' ?>"><?= $singletonCheck ? 'OK' : 'FAIL' ?></td>
                <td><?= $singletonCheck ? 'возвращён один и тот же экземпляр' : 'экземпляры различаются' ?></td>
            </tr>
        </tbody>
    </table>
</section>
