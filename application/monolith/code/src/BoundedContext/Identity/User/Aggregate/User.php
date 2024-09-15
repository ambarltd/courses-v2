<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Aggregate;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\RequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

class User implements Aggregate
{
    use AggregateTrait;

    private VerifiedEmail|UnverifiedEmail|RequestedNewEmail $primaryEmailStatus;

    private HashedPassword $hashedPassword;

    private AccountDetails $accountDetails;

    public function primaryEmailStatus(): VerifiedEmail|UnverifiedEmail|RequestedNewEmail
    {
        return $this->primaryEmailStatus;
    }

    public function hashedPassword(): HashedPassword
    {
        return $this->hashedPassword;
    }

    public function accountDetails(): AccountDetails
    {
        return $this->accountDetails;
    }

    public static function fromProperties(
        Id $id,
        int $aggregateVersion,
        VerifiedEmail|UnverifiedEmail|RequestedNewEmail $primaryEmailStatus,
        HashedPassword $hashedPassword,
        AccountDetails $accountDetails
    ): User {
        $user = new self($id, $aggregateVersion);

        $user->primaryEmailStatus = $primaryEmailStatus;
        $user->hashedPassword = $hashedPassword;
        $user->accountDetails = $accountDetails;

        return $user;
    }
}
