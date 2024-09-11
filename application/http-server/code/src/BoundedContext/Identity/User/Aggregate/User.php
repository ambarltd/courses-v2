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

    /**
     * @var VerifiedEmail|UnverifiedEmail|RequestedNewEmail
     */
    private $primaryEmailStatus;

    /**
     * @var HashedPassword
     */
    private $hashedPassword;

    /**
     * @var AccountDetails
     */
    private $accountDetails;

    /**
     * @return VerifiedEmail|UnverifiedEmail|RequestedNewEmail
     */
    public function primaryEmailStatus()
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

    /**
     * @param VerifiedEmail|UnverifiedEmail|RequestedNewEmail $primaryEmailStatus
     */
    public static function fromProperties(
        Id $id,
        $primaryEmailStatus,
        HashedPassword $hashedPassword,
        AccountDetails $accountDetails
    ): User {
        $user = new self($id);

        $user->primaryEmailStatus = $primaryEmailStatus;
        $user->hashedPassword = $hashedPassword;
        $user->accountDetails = $accountDetails;

        return $user;
    }
}
