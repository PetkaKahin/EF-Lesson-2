<?php

namespace Task1;

use Task1\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private readonly string $storageFile
    ){
        $this->ensureStorageDir();
    }

    public function setOrder(Order $order): void
    {
        $raw = file_get_contents($this->storageFile);
        $existing = json_decode((string)$raw, true);
        if (!is_array($existing)) {
            $existing = [];
        }

        $existing[] = $order;

        file_put_contents($this->storageFile, json_encode($existing, JSON_UNESCAPED_UNICODE));
    }

    private function ensureStorageDir(): void
    {
        $dir = dirname($this->storageFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}