<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\QueryHandler;

use Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\GetUserDetails;
use Galeas\Api\BoundedContext\Identity\User\Query\GetUserDetailsQuery;
use Galeas\Api\CommonException\ProjectionCannotRead;

class GetUserDetailsQueryHandler
{
    private GetUserDetails $getUserDetails;

    public function __construct(GetUserDetails $getUserDetails)
    {
        $this->getUserDetails = $getUserDetails;
    }

    /**
     * @return array{userId: string, primaryEmailStatus: array{unverifiedEmail: array{email: string}}|array{verifiedButRequestedNewEmail: array{requestedEmail: string, verifiedEmail: string}}|array{verifiedEmail: array{email: string}}}
     *
     * @throws ProjectionCannotRead
     */
    public function handle(GetUserDetailsQuery $getUserDetailsQuery): array
    {
        return $this->getUserDetails->getUserDetails($getUserDetailsQuery->authenticatedUserId);
    }
}
