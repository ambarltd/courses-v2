<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\QueryHandler;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails\GetUserDetails;
use Galeas\Api\BoundedContext\Identity\User\Query\GetUserDetailsQuery;
use Galeas\Api\BoundedContext\Identity\User\QueryHandler\GetUserDetailsQueryHandler;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerUnitTest;

class GetUserDetailsQueryHandlerTest extends HandlerUnitTest
{
    public function testHandle(): void
    {
        $query = new GetUserDetailsQuery();
        $query->authenticatedUserId = 'user_id_1';

        $getUserDetails = $this->mockForCommandHandlerWithReturnValue(
            GetUserDetails::class,
            'getUserDetails',
            [
                'userId' => 'user_id_1',
                'username' => 'username_123',
                'primaryEmailStatus' => [
                    'verifiedEmail' => [
                        'email' => 'email_address_1',
                    ],
                ],
            ]
        );
        Assert::assertEquals(
            [
                'userId' => 'user_id_1',
                'username' => 'username_123',
                'primaryEmailStatus' => [
                    'verifiedEmail' => [
                        'email' => 'email_address_1',
                    ],
                ],
            ],
            (new GetUserDetailsQueryHandler($getUserDetails))->handle($query)
        );
    }
}
