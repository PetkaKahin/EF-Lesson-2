<?php

namespace Task6\Domain\Contracts;

use Task6\Domain\User;

interface UserRepositoryInterface
{
    /**
     * @return array<User>
     */
    public function users(): array;
    public function user(string $email): ?User;
    public function save(User $user): void;
}