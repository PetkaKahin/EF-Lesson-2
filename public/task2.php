<?php

use Task2\DTO\CreateOrderRequest;
use Task2\DTO\CreateOrderItemRequest;
use Task2\Entity\Order;
use Task2\Factory\OrderFactory;

$request = new CreateOrderRequest(
    email: 'alice@example.com',
    items: [
        new CreateOrderItemRequest('Widget', 2, 500, 'RUB'),
        new CreateOrderItemRequest('Gadget', 1, 150, 'RUB'),
    ],
);

$order = OrderFactory::createFromRequest($request);

$inputRows = [
    ['email',             $request->email],
    ['items[0].name',     $request->items[0]->name],
    ['items[0].quantity', $request->items[0]->quantity],
    ['items[0].price',    $request->items[0]->priceAmount . ' ' . $request->items[0]->priceCurrency],
    ['items[1].name',     $request->items[1]->name],
    ['items[1].quantity', $request->items[1]->quantity],
    ['items[1].price',    $request->items[1]->priceAmount . ' ' . $request->items[1]->priceCurrency],
];

$resultRows = [
    ['id',           $order->id->id],
    ['email',        $order->email->email],
    ['items count',  count($order->items)],
    ['totalAmount',  $order->totalAmount->amount . ' ' . $order->totalAmount->currency->value],
    ['status',       $order->status->value],
];

$transitions = [];

$cases = [
    'markPaid() из Draft'   => fn(Order $o) => $o->markPaid(),
    'cancel() из Draft'     => fn(Order $o) => $o->cancel(),
    'refund() из Cancelled' => function (Order $o) {
        $o->cancel();
        $o->refund();
    },
];

foreach ($cases as $label => $action) {
    $tmp = OrderFactory::createFromRequest($request);
    try {
        $action($tmp);
        $transitions[] = [$label, 'OK', 'статус = ' . $tmp->status->value];
    } catch (Throwable $e) {
        $transitions[] = [$label, 'EXCEPTION', $e->getMessage()];
    }
}
?>
<section>
    <h2>Задание 2. DTO vs Value Object vs Entity</h2>

    <h3>Входные данные (DTO)</h3>
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

    <h3>Результат (Entity)</h3>
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

    <h3>Переходы состояний</h3>
    <table>
        <thead>
            <tr><th>Действие</th><th>Результат</th><th>Подробности</th></tr>
        </thead>
        <tbody>
        <?php foreach ($transitions as [$action, $outcome, $detail]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($action) ?></td>
                <td class="<?= $outcome === 'OK' ? 'ok' : 'err' ?>"><?= htmlspecialchars($outcome) ?></td>
                <td><?= htmlspecialchars((string)$detail) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
