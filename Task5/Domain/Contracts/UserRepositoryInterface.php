<?php

namespace Task5\Domain\Contracts;

use Task5\Domain\User;

interface UserRepositoryInterface
{
    /**
     * @return array<User>
     */
    public function users(): array;
    public function user(string $email): ?User;
    public function save(User $user): void;
}