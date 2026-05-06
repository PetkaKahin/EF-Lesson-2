<?php

use Task6\Legacy\OrderService as OriginalOrderService;
use Task6\LegacyWithFeature\OrderService as FeatureOrderService;
use Task6\LegacyWithFeature\PromoCodeRules\FreeShipPromoCodeRule;
use Task6\LegacyWithFeature\PromoCodeRules\VipPromoCodeRule;
use Task6\LegacyWithFeature\PromoCodeRules\Welcome10PromoCodeRule;
use Task6\LegacyWithFeature\PromoCodeRulesRegistry;
use Task6\LegacyWithFeature\ServiceLocator;

$storageDir = __DIR__ . '/../var/task6';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0775, true);
}

$originalOrdersStorage = $storageDir . '/legacy-orders.json';
$featureOrdersStorage = $storageDir . '/legacy-with-feature-orders.json';
file_put_contents($originalOrdersStorage, json_encode([]));
file_put_contents($featureOrdersStorage, json_encode([]));

ServiceLocator::reset();

$promoCodeRulesRegistry = new PromoCodeRulesRegistry();
$promoCodeRulesRegistry->set('WELCOME10', new Welcome10PromoCodeRule());
$promoCodeRulesRegistry->set('SHIPFREE', new FreeShipPromoCodeRule());
$promoCodeRulesRegistry->set('FREESHIP', new FreeShipPromoCodeRule());
$promoCodeRulesRegistry->set('VIP', new VipPromoCodeRule(
    threshold: 2000,
    discountBeforeThreshold: 100,
    discountAfterThreshold: 300,
));

ServiceLocator::set(PromoCodeRulesRegistry::class, $promoCodeRulesRegistry);
ServiceLocator::setFactory(FeatureOrderService::class, static fn() => new FeatureOrderService(
    promoCodeRulesRegistry: ServiceLocator::get(PromoCodeRulesRegistry::class),
    storageFile: $featureOrdersStorage,
));

$originalOrderService = new OriginalOrderService();
$originalStorageProperty = new ReflectionProperty($originalOrderService, 'storageFile');
$originalStorageProperty->setAccessible(true);
$originalStorageProperty->setValue($originalOrderService, $originalOrdersStorage);

$featureOrderService = ServiceLocator::get(FeatureOrderService::class);

$baseInput = [
    'customer' => [
        'email' => 'alice@example.com',
        'name' => 'Alice',
    ],
    'items' => [
        ['sku' => 'A1', 'title' => 'Keyboard', 'price' => 300, 'qty' => 2],
        ['sku' => 'B2', 'title' => 'Mouse', 'price' => 150, 'qty' => 1],
    ],
    'payment' => [
        'method' => 'card',
        'cardNumber' => '4111111111111111',
    ],
    'delivery' => [
        'type' => 'courier',
        'address' => 'Moscow, Tverskaya 1',
    ],
];

$originalInput = $baseInput + ['promoCode' => 'WELCOME10'];
$featureInput = $baseInput + ['promoCodes' => ['WELCOME10', 'SHIPFREE']];

$originalResult = $originalOrderService->createOrder($originalInput);
$featureResult = $featureOrderService->createOrder($featureInput);

$originalOrder = $originalResult['order'] ?? null;
$featureOrder = $featureResult['order'] ?? null;
$originalPricing = $originalOrder['pricing'] ?? [];
$featurePricing = $featureOrder['pricing'] ?? [];
$originalDelivery = $originalOrder['delivery'] ?? [];
$featureDelivery = $featureOrder['delivery'] ?? [];

$inputRows = [
    ['customer.email', $baseInput['customer']['email']],
    ['items', 'Keyboard 300 x 2 + Mouse 150 x 1'],
    ['delivery', $baseInput['delivery']['type'] . ', address filled'],
    ['payment', $baseInput['payment']['method']],
    ['old input', 'promoCode = WELCOME10'],
    ['new input', 'promoCodes = WELCOME10, SHIPFREE'],
];

$registrationRows = [
    ['Old service', OriginalOrderService::class],
    ['New service', FeatureOrderService::class],
    ['WELCOME10', Welcome10PromoCodeRule::class . ' (-10%)'],
    ['SHIPFREE', FreeShipPromoCodeRule::class . ' (free delivery)'],
    ['FREESHIP', FreeShipPromoCodeRule::class . ' (legacy alias)'],
    ['VIP', VipPromoCodeRule::class . ' (legacy rule)'],
    [PromoCodeRulesRegistry::class, 'registered in public/task6.php'],
];

$comparisonRows = [];
if ($originalOrder !== null && $featureOrder !== null) {
    $comparisonRows = [
        ['Implementation', 'inline if/elseif in OrderService', 'registry + PromoCodeRule classes'],
        ['Promo input', (string)$originalInput['promoCode'], implode(', ', $featureInput['promoCodes'])],
        ['Promo output', (string)$originalPricing['promoCode'], implode(', ', $featurePricing['promoCodes'])],
        ['Subtotal', number_format((float)$originalPricing['subtotal'], 2, '.', ''), number_format((float)$featurePricing['subtotal'], 2, '.', '')],
        ['Discount', number_format((float)$originalPricing['discount'], 2, '.', ''), number_format((float)$featurePricing['discount'], 2, '.', '')],
        ['Delivery', number_format((float)$originalDelivery['cost'], 2, '.', ''), number_format((float)$featureDelivery['cost'], 2, '.', '')],
        ['Tax', number_format((float)$originalPricing['tax'], 2, '.', ''), number_format((float)$featurePricing['tax'], 2, '.', '')],
        ['Total', number_format((float)$originalPricing['total'], 2, '.', ''), number_format((float)$featurePricing['total'], 2, '.', '')],
    ];
} else {
    $comparisonRows = [
        ['Old result', (string)($originalResult['error'] ?? 'unknown error'), '-'],
        ['New result', '-', (string)($featureResult['error'] ?? 'unknown error')],
    ];
}

$checkRows = [];
if ($originalOrder !== null) {
    $expectedOriginalTotal = $originalPricing['subtotal'] - $originalPricing['discount'] + $originalPricing['tax'] + $originalDelivery['cost'];
    $checkRows[] = [
        'old total invariant',
        abs($originalPricing['total'] - $expectedOriginalTotal) < 0.0001 ? 'OK' : 'FAIL',
        sprintf(
            '%.2f = %.2f - %.2f + %.2f + %.2f',
            $originalPricing['total'],
            $originalPricing['subtotal'],
            $originalPricing['discount'],
            $originalPricing['tax'],
            $originalDelivery['cost'],
        ),
    ];
}

if ($featureOrder !== null) {
    $expectedFeatureTotal = $featurePricing['subtotal'] - $featurePricing['discount'] + $featurePricing['tax'] + $featureDelivery['cost'];
    $checkRows[] = [
        'new total invariant',
        abs($featurePricing['total'] - $expectedFeatureTotal) < 0.0001 ? 'OK' : 'FAIL',
        sprintf(
            '%.2f = %.2f - %.2f + %.2f + %.2f',
            $featurePricing['total'],
            $featurePricing['subtotal'],
            $featurePricing['discount'],
            $featurePricing['tax'],
            $featureDelivery['cost'],
        ),
    ];
}

$legacyInputInFeatureResult = $featureOrderService->createOrder($originalInput);
$checkRows[] = [
    'new service accepts legacy promoCode input',
    ($legacyInputInFeatureResult['ok'] ?? false) ? 'OK' : 'FAIL',
    implode(', ', $legacyInputInFeatureResult['order']['pricing']['promoCodes'] ?? []) ?: (string)($legacyInputInFeatureResult['error'] ?? ''),
];

$aliasInput = $baseInput + ['promoCodes' => ['FREESHIP']];
$aliasResult = $featureOrderService->createOrder($aliasInput);
$aliasDeliveryCost = (float)($aliasResult['order']['delivery']['cost'] ?? -1);
$checkRows[] = [
    'legacy alias FREESHIP',
    ($aliasResult['ok'] ?? false) && abs($aliasDeliveryCost) < 0.0001 ? 'OK' : 'FAIL',
    'delivery=' . number_format($aliasDeliveryCost, 2, '.', ''),
];

$tooManyPromoInput = $baseInput + ['promoCodes' => ['WELCOME10', 'SHIPFREE', 'VIP']];
$tooManyPromoResult = $featureOrderService->createOrder($tooManyPromoInput);
$checkRows[] = [
    '3 promo codes',
    ($tooManyPromoResult['ok'] ?? true) === false ? 'OK' : 'FAIL',
    (string)($tooManyPromoResult['error'] ?? 'error was not returned'),
];
?>
<section>
    <h2>Задание 6. Финал блока — “фича в легаси”</h2>

    <h3>Регистрация</h3>
    <table>
        <thead>
        <tr><th>Item</th><th>Value</th></tr>
        </thead>
        <tbody>
        <?php foreach ($registrationRows as [$item, $value]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($item) ?></td>
                <td><?= htmlspecialchars($value) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Input</h3>
    <table>
        <thead>
        <tr><th>Field</th><th>Value</th></tr>
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

    <h3>Сравнение со старым кодом</h3>
    <table>
        <thead>
        <tr><th>Field</th><th>Old legacy</th><th>Legacy with feature</th></tr>
        </thead>
        <tbody>
        <?php foreach ($comparisonRows as [$field, $oldValue, $newValue]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($field) ?></td>
                <td><?= htmlspecialchars((string)$oldValue) ?></td>
                <td><?= htmlspecialchars((string)$newValue) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Проверки</h3>
    <table>
        <thead>
        <tr><th>Scenario</th><th>Result</th><th>Details</th></tr>
        </thead>
        <tbody>
        <?php foreach ($checkRows as [$scenario, $outcome, $detail]): ?>
            <tr>
                <td class="field"><?= htmlspecialchars($scenario) ?></td>
                <td class="<?= $outcome === 'OK' ? 'ok' : 'err' ?>"><?= htmlspecialchars($outcome) ?></td>
                <td><?= htmlspecialchars((string)$detail) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
