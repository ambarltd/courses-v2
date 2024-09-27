<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\ValueObject;

class VerifiedEmail
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
