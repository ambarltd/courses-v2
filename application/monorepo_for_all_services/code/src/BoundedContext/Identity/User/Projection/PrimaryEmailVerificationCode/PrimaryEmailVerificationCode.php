<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

class PrimaryEmailVerificationCode
{
    private string $id;

    private ?string $primaryEmailVerificationCode;

    private function __construct()
    {
    }

    public function getUserId(): string
    {
        return $this->id;
    }

    public function getPrimaryEmailVerificationCode(): ?string
    {
        return $this->primaryEmailVerificationCode;
    }

    public function updateVerificationCode(?string $primaryEmailVerificationCode): self
    {
        $this->primaryEmailVerificationCode = $primaryEmailVerificationCode;

        return $this;
    }

    public static function fromUserIdAndVerificationCode(string $userId, ?string $primaryEmailVerificationCode): self
    {
        $primaryEmailVerificationCodeObject = new self();
        $primaryEmailVerificationCodeObject->id = $userId;
        $primaryEmailVerificationCodeObject->primaryEmailVerificationCode = $primaryEmailVerificationCode;

        return $primaryEmailVerificationCodeObject;
    }
}
