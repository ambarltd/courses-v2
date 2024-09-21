<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\TakenEmail;

class TakenEmail
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $canonicalVerifiedEmail;

    /**
     * @var string|null
     */
    private $canonicalRequestedEmail;

    private function __construct()
    {
    }

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

        if (is_string($verifiedEmail)) {
            $this->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (is_string($requestedEmail)) {
            $this->canonicalRequestedEmail = strtolower($requestedEmail);
        }

        return $this;
    }

    /**
     * @return TakenEmail
     */
    public static function fromUserIdAndEmails(
        string $userId,
        ?string $verifiedEmail,
        ?string $requestedEmail
    ): self {
        $takenEmail = new self();
        $takenEmail->id = $userId;

        if (is_string($verifiedEmail)) {
            $takenEmail->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (is_string($requestedEmail)) {
            $takenEmail->canonicalRequestedEmail = strtolower($requestedEmail);
        }

        return $takenEmail;
    }
}
