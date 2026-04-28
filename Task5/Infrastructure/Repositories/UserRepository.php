<?php

namespace Task5\Infrastructure\Repositories;

use Task5\Domain\Order;
use Task5\Domain\Contracts\UserRepositoryInterface;
use Task5\Domain\User;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var array<Order>
     */
    private(set) public array $users;

    public function __construct(
        private readonly string $storage
    )
    {
        if (file_exists($storage)) {
            $raw = file_get_contents($storage);
            $data = json_decode((string)$raw, true);
            if (is_array($data)) {
                $this->users = $data['users'] ?? [];
            }
        }
    }

    public function save(User $user): void
    {
        $this->users[$user->email] = $user;
    }

    public function users(): array
    {
        return $this->users;
    }

    public function user(string $email): ?User
    {
        return $this->users[$email] ?? null;
    }
}