<?php

namespace Task6\LegacyWithFeature;

use DateTimeImmutable;
use InvalidArgumentException;
use Task6\LegacyWithFeature\DTO\PaymentData;

class OrderService
{
    private string $storageFile;
    private string $adminEmail;
    private bool $debug;
    private PromoCodeRulesRegistry $promoCodeRulesRegistry;

    public function __construct(?PromoCodeRulesRegistry $promoCodeRulesRegistry = null, ?string $storageFile = null)
    {
        $this->storageFile = $storageFile ?? __DIR__ . '/../../var/orders.json';
        $this->adminEmail = 'admin@example.com';
        $this->debug = true;
        $this->promoCodeRulesRegistry = $promoCodeRulesRegistry ?? new PromoCodeRulesRegistry();
    }

    /**
     * Создаёт заказ, валидирует, считает стоимость/скидки/доставку, сохраняет, отправляет уведомления.
     * Возвращает массив "заказа".
     *
     * @param array $input Ожидается структура:
     * [
     *   'customer' => ['email' => '...', 'name' => '...'],
     *   'items' => [
     *      ['sku' => 'A1', 'title' => 'Item', 'price' => 199.99, 'qty' => 2],
     *   ],
     *   'payment' => ['method' => 'card|cash|invoice', 'cardNumber' => '....' (опц.)],
     *   'delivery' => ['type' => 'courier|pickup|post', 'address' => '...' (для courier/post)],
     *   'promoCodes' => ['WELCOME10|VIP|FREESHIP', 'WELCOME10|VIP|FREESHIP'] (опц.),
     * ]
     */
    public function createOrder(array $input): array
    {
        if (!isset($input['customer']['email'])) {
            return ['ok' => false, 'error' => 'customer email required'];
        }

        $email = trim((string)$input['customer']['email']);
        if ($email === '' || strpos($email, '@') === false) {
            return ['ok' => false, 'error' => 'invalid email'];
        }

        if (!isset($input['items']) || !is_array($input['items']) || count($input['items']) === 0) {
            return ['ok' => false, 'error' => 'items required'];
        }

        $orderId = (string)time() . '-' . rand(1000, 9999);

        $items = $input['items'];

        $subtotal = 0;
        foreach ($items as &$it) {
            $it['sku'] = isset($it['sku']) ? (string)$it['sku'] : '';
            $it['title'] = isset($it['title']) ? (string)$it['title'] : 'Unknown';
            $it['qty'] = isset($it['qty']) ? (int)$it['qty'] : 1;
            $it['price'] = isset($it['price']) ? (float)$it['price'] : 0.0;

            if ($it['qty'] < 1) $it['qty'] = 1;
            if ($it['price'] < 0) $it['price'] = 0;

            $subtotal += $it['price'] * $it['qty'];
        }

        $delivery = $input['delivery'] ?? [];
        $deliveryType = isset($delivery['type']) ? (string)$delivery['type'] : 'courier';

        $deliveryCost = 0;
        if ($deliveryType === 'courier') {
            $deliveryCost = ($subtotal >= 1000) ? 0 : 199;
            if (empty($delivery['address'])) {
                if ($this->debug) {
                    error_log("[DEBUG] courier without address for order {$orderId}");
                }
            }
        } elseif ($deliveryType === 'pickup') {
            $deliveryCost = 0;
        } elseif ($deliveryType === 'post') {
            $deliveryCost = 299;
        } else {
            $deliveryType = 'courier';
            $deliveryCost = 199;
        }

        $discount = 0;

        try {
            $promoCodes = $this->promoCodeRulesRegistry->promoCodesFromInput($input);
        } catch (InvalidArgumentException $exception) {
            return ['ok' => false, 'error' => $exception->getMessage()];
        }

        $paymentData = new PaymentData(
            subtotal: $subtotal,
            discount: $discount,
            delivery: $deliveryCost,
        );

        foreach ($promoCodes as $promoCode) {
            $promoCodeRule = $this->promoCodeRulesRegistry->get($promoCode);

            if ($promoCodeRule === null) {
                continue;
            }

            $paymentData = $promoCodeRule->apply($paymentData);
        }

        $discount = $paymentData->discount;
        $deliveryCost = $paymentData->delivery;

        $tax = ($subtotal - $discount) * 0.05;

        $total = ($subtotal - $discount) + $tax + $deliveryCost;
        if ($total < 0) $total = 0;

        $payment = $input['payment'] ?? [];
        $paymentMethod = isset($payment['method']) ? (string)$payment['method'] : 'card';

        $paymentStatus = 'pending';
        if ($paymentMethod === 'card') {
            $card = isset($payment['cardNumber']) ? preg_replace('/\s+/', '', (string)$payment['cardNumber']) : '';
            if (strlen($card) < 12) {
                return ['ok' => false, 'error' => 'invalid card number'];
            }
            $paymentStatus = 'paid';
        } elseif ($paymentMethod === 'cash') {
            $paymentStatus = 'cash_on_delivery';
        } elseif ($paymentMethod === 'invoice') {
            $paymentStatus = 'awaiting_invoice';
        } else {
            $paymentMethod = 'card';
        }

        $now = new DateTimeImmutable();
        $order = [
            'id' => $orderId,
            'createdAt' => $now->format('c'),
            'customer' => [
                'email' => $email,
                'name' => (string)($input['customer']['name'] ?? ''),
            ],
            'items' => $items,
            'delivery' => [
                'type' => $deliveryType,
                'address' => (string)($delivery['address'] ?? ''),
                'cost' => $deliveryCost,
            ],
            'payment' => [
                'method' => $paymentMethod,
                'status' => $paymentStatus,
            ],
            'pricing' => [
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'tax' => round($tax, 2),
                'total' => round($total, 2),
                'promoCodes' => $promoCodes,
            ],
        ];

        $this->ensureStorageDir();

        $existing = [];
        if (file_exists($this->storageFile)) {
            $raw = file_get_contents($this->storageFile);
            $existing = json_decode((string)$raw, true);
            if (!is_array($existing)) {
                $existing = [];
            }
        }

        $existing[] = $order;

        file_put_contents($this->storageFile, json_encode($existing, JSON_UNESCAPED_UNICODE));

        $this->notifyAdmin($order);
        $this->notifyCustomer($order);

        return ['ok' => true, 'order' => $order];
    }

    private function ensureStorageDir(): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    private function notifyAdmin(array $order): void
    {
        $msg = "New order {$order['id']} total={$order['pricing']['total']} customer={$order['customer']['email']}";
        if ($this->debug) {
            error_log("[MAIL to {$this->adminEmail}] {$msg}");
        }
    }

    private function notifyCustomer(array $order): void
    {
        $msg = "Thanks! Your order {$order['id']} total={$order['pricing']['total']}";
        if ($this->debug) {
            error_log("[MAIL to {$order['customer']['email']}] {$msg}");
        }
    }
}
