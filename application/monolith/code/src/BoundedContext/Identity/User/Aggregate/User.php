<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Aggregate;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

class User implements Aggregate
{
    use AggregateTrait;

    private VerifiedEmail|UnverifiedEmail|VerifiedButRequestedNewEmail $primaryEmailStatus;

    private HashedPassword $hashedPassword;

    private AccountDetails $accountDetails;

    public function primaryEmailStatus(): VerifiedEmail|UnverifiedEmail|VerifiedButRequestedNewEmail
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
        Id                                                         $aggregateId,
        int                                                        $aggregateVersion,
        VerifiedEmail|UnverifiedEmail|VerifiedButRequestedNewEmail $primaryEmailStatus,
        HashedPassword                                             $hashedPassword,
        AccountDetails                                             $accountDetails
    ): User {
        $user = new self($aggregateId, $aggregateVersion);

        $user->primaryEmailStatus = $primaryEmailStatus;
        $user->hashedPassword = $hashedPassword;
        $user->accountDetails = $accountDetails;

        return $user;
    }
}
