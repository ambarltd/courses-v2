<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class VerifiedEmail
{
    private Email $email;

    private function __construct(Email $email)
    {
        $this->email = $email;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public static function fromEmail(Email $email): VerifiedEmail
    {
        return new self($email);
    }
}
