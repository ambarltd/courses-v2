<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SignedOutTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $metadata = [1, 2, 3];
        $withIp = '127.0.0.1';
        $withSessionToken = 'session_token';

        $signedOut = SignedOut::fromProperties(
            $aggregateId,
            $authorizerId,
            $metadata,
            $withIp,
            $withSessionToken
        );

        Assert::assertEquals($aggregateId, $signedOut->aggregateId());
        Assert::assertEquals($authorizerId, $signedOut->authorizerId());
        Assert::assertEquals($metadata, $signedOut->eventMetadata());
        Assert::assertEquals($withIp, $signedOut->withIp());
        Assert::assertEquals($withSessionToken, $signedOut->withSessionToken());
    }

    /**
     * @test
     */
    public function testTransformAggregate(): void
    {
        $session = Session::fromProperties(
            Id::createNew(),
            SessionDetails::fromProperties(
                Id::createNew(),
                'test_username',
                'test_email',
                'test_hashed_password',
                'by_device_label',
                '127.0.0.1',
                'with_session_token'
            ),
            null
        );

        $signedOut = SignedOut::fromProperties(
            Id::createNew(),
            Id::createNew(),
            [1, 2, 3],
            '127.0.0.2',
            'new_session_token'
        );

        $transformedSession = $signedOut->transformSession($session);

        Assert::assertEquals(
            $session->id(),
            $transformedSession->id()
        );
        Assert::assertEquals(
            $session->sessionDetails(),
            $transformedSession->sessionDetails()
        );
        Assert::assertEquals(
            SessionIsSignedOut::fromProperties(
                $signedOut->withSessionToken(),
                $signedOut->withIp()
            ),
            $transformedSession->sessionIsSignedOut()
        );
    }
}
