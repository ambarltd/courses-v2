<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail;

class UserWithEmail
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

    /**
     * @var Unverified|Verified|RequestedChange
     */
    private $status;

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
     * @return Unverified|Verified|RequestedChange
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Unverified|Verified|RequestedChange $status
     *
     * @return $this
     */
    public function changeEmails(
        ?string $verifiedEmail,
        ?string $requestedEmail,
        $status
    ): self {
        $this->canonicalVerifiedEmail = null;
        $this->canonicalRequestedEmail = null;
        $this->status = $status;

        if (is_string($verifiedEmail)) {
            $this->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (is_string($requestedEmail)) {
            $this->canonicalRequestedEmail = strtolower($requestedEmail);
        }

        return $this;
    }

    /**
     * @param Unverified|Verified|RequestedChange $status
     */
    public static function fromUserIdAndEmails(
        string $userId,
        ?string $verifiedEmail,
        ?string $requestedEmail,
        $status
    ): self {
        $takenEmail = new self();
        $takenEmail->id = $userId;

        if (is_string($verifiedEmail)) {
            $takenEmail->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (is_string($requestedEmail)) {
            $takenEmail->canonicalRequestedEmail = strtolower($requestedEmail);
        }
        $takenEmail->status = $status;

        return $takenEmail;
    }
}
