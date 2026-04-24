<?php

declare(strict_types=1);

namespace Task1\DTO;

use InvalidArgumentException;

final readonly class CustomerData
{
    public string $email;
    public string $name;

    public function __construct(
        string $email,
        string $name
    )
    {
        $this->email = trim($email);
        $this->name = trim($name);

        $this->validate();
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
        ];
    }

    private function validate(): void
    {
        if ($this->email === '' || strpos($this->email, '@') === false) {
            throw new InvalidArgumentException('email is invalid');
        }

        if ($this->name === '') {
            throw new InvalidArgumentException('name is required');
        }
    }
}
