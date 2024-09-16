<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\ValueObject;

class VerificationCode
{
    private string $verificationCode;

    private function __construct(string $verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    public function verificationCode(): string
    {
        return $this->verificationCode;
    }

    public static function fromVerificationCode(string $verificationCode): VerificationCode
    {
        return new self($verificationCode);
    }
}
