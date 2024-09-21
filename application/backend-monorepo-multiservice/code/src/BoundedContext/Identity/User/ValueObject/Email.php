<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class Email
{
    private string $email;

    private function __construct(string $email)
    {
        $this->email = $email;
    }

    public function email(): string
    {
        return $this->email;
    }

    public static function fromEmail(string $email): Email
    {
        return new self($email);
    }
}
