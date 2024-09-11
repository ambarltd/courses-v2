<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\PrimaryEmailVerificationCode;

class PrimaryEmailVerificationCode
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $primaryEmailVerificationCode;

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

    /**
     * @return $this
     */
    public function updateVerificationCode(?string $primaryEmailVerificationCode)
    {
        $this->primaryEmailVerificationCode = $primaryEmailVerificationCode;

        return $this;
    }

    /**
     * @return PrimaryEmailVerificationCode
     */
    public static function fromUserIdAndVerificationCode(string $userId, ?string $primaryEmailVerificationCode): self
    {
        $primaryEmailVerificationCodeObject = new self();
        $primaryEmailVerificationCodeObject->id = $userId;
        $primaryEmailVerificationCodeObject->primaryEmailVerificationCode = $primaryEmailVerificationCode;

        return $primaryEmailVerificationCodeObject;
    }
}
