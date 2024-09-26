<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\ValueObject\VerifiedEmailButRequestedNewEmail;

class UserDetails
{
    private string $id;

    private UnverifiedEmail|VerifiedEmail|VerifiedEmailButRequestedNewEmail $primaryEmailStatus;

    private function __construct() {}

    public function getUserId(): string
    {
        return $this->id;
    }

    public function getPrimaryEmailStatus(): UnverifiedEmail|VerifiedEmail|VerifiedEmailButRequestedNewEmail
    {
        return $this->primaryEmailStatus;
    }

    public function changePrimaryEmailStatus(UnverifiedEmail|VerifiedEmail|VerifiedEmailButRequestedNewEmail $primaryEmailStatus): self
    {
        $this->primaryEmailStatus = $primaryEmailStatus;

        return $this;
    }

    public static function fromProperties(string $userId, UnverifiedEmail|VerifiedEmail|VerifiedEmailButRequestedNewEmail $primaryEmailStatus): self
    {
        $userDetails = new self();
        $userDetails->id = $userId;
        $userDetails->primaryEmailStatus = $primaryEmailStatus;

        return $userDetails;
    }
}
