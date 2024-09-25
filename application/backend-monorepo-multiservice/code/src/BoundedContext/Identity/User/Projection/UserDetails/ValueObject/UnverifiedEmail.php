<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject;

class UnverifiedEmail
{
    private string $email;

    private function __construct() {}

    public function getEmail(): string
    {
        return $this->email;
    }

    public static function fromProperties(string $email): self
    {
        $instance = new self();
        $instance->email = $email;

        return $instance;
    }
}
