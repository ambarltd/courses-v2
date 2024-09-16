<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\ValueObject;

use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class AccountDetailsTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $accountDetails = AccountDetails::fromDetails(
            'username',
            true
        );

        Assert::assertEquals('username', $accountDetails->username());
        Assert::assertEquals(true, $accountDetails->termsOfUseAccepted());
        $accountDetails = AccountDetails::fromDetails(
            'username_2',
            false
        );

        Assert::assertEquals('username_2', $accountDetails->username());
        Assert::assertEquals(false, $accountDetails->termsOfUseAccepted());
    }
}
