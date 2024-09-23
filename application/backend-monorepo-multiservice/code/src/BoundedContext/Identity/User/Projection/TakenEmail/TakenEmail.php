<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail;

class TakenEmail
{
    private string $id;

    private ?string $canonicalVerifiedEmail = null;

    private ?string $canonicalRequestedEmail = null;

    private function __construct() {}

    public function getUserId(): string
    {
        return $this->id;
    }

    public function getCanonicalVerifiedEmail(): ?string
    {
        return $this->canonicalVerifiedEmail;
    }

    public function getCanonicalRequestedEmail(): ?string
    {
        return $this->canonicalRequestedEmail;
    }

    /**
     * @return $this
     */
    public function changeEmails(
        ?string $verifiedEmail,
        ?string $requestedEmail
    ): self {
        $this->canonicalVerifiedEmail = null;
        $this->canonicalRequestedEmail = null;

        if (\is_string($verifiedEmail)) {
            $this->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (\is_string($requestedEmail)) {
            $this->canonicalRequestedEmail = strtolower($requestedEmail);
        }

        return $this;
    }

    public static function fromUserIdAndEmails(
        string $userId,
        ?string $verifiedEmail,
        ?string $requestedEmail
    ): self {
        $takenEmail = new self();
        $takenEmail->id = $userId;

        if (\is_string($verifiedEmail)) {
            $takenEmail->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (\is_string($requestedEmail)) {
            $takenEmail->canonicalRequestedEmail = strtolower($requestedEmail);
        }

        return $takenEmail;
    }
}
