<?php

declare(strict_types=1);

namespace Task6\Infrastructure\Repositories;

use Task6\Domain\Contracts\UserRepositoryInterface;
use Task6\Domain\User;

final class UserRepository implements UserRepositoryInterface
{
    /**
     * @var array<User>
     */
    private(set) public array $users = [];

    public function __construct(
        private readonly string $storage,
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

    /**
     * @return array<User>
     */
    public function users(): array
    {
        return $this->users;
    }

    public function user(string $email): ?User
    {
        return $this->users[$email] ?? null;
    }

    public function save(User $user): void
    {
        $this->users[$user->email] = $user;

        file_put_contents(
            $this->storage,
            json_encode(['users' => $this->users], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}
