<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class UnverifiedEmail
{
    private Email $email;

    private VerificationCode $verificationCode;

    private function __construct(
        Email $email,
        VerificationCode $verificationCode
    ) {
        $this->email = $email;
        $this->verificationCode = $verificationCode;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function verificationCode(): VerificationCode
    {
        return $this->verificationCode;
    }

    public static function fromEmailAndVerificationCode(
        Email $email,
        VerificationCode $verificationCode
    ): UnverifiedEmail {
        return new self(
            $email,
            $verificationCode
        );
    }
}
