<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail;

class UserWithEmail
{
    private string $id;

    private ?string $canonicalVerifiedEmail = null;

    private ?string $canonicalRequestedEmail = null;

    private RequestedChange|Unverified|Verified $status;

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
     * @return RequestedChange|Unverified|Verified
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param RequestedChange|Unverified|Verified $status
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

        if (\is_string($verifiedEmail)) {
            $this->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (\is_string($requestedEmail)) {
            $this->canonicalRequestedEmail = strtolower($requestedEmail);
        }

        return $this;
    }

    /**
     * @param RequestedChange|Unverified|Verified $status
     */
    public static function fromUserIdAndEmails(
        string $userId,
        ?string $verifiedEmail,
        ?string $requestedEmail,
        $status
    ): self {
        $takenEmail = new self();
        $takenEmail->id = $userId;

        if (\is_string($verifiedEmail)) {
            $takenEmail->canonicalVerifiedEmail = strtolower($verifiedEmail);
        }
        if (\is_string($requestedEmail)) {
            $takenEmail->canonicalRequestedEmail = strtolower($requestedEmail);
        }
        $takenEmail->status = $status;

        return $takenEmail;
    }
}
