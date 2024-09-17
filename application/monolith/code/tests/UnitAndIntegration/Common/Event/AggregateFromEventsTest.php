<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailChangeRequested;
use Galeas\Api\BoundedContext\Identity\User\Event\PrimaryEmailVerified;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\AccountDetails;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\HashedPassword;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Event\AggregateFromEvents;
use Galeas\Api\Common\Event\EventReflectionBaseClass;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class AggregateFromEventsTest extends UnitTestBase {
    public function testAggregateFromEvents(): void
    {
        $userEvents = SampleEvents::userEvents();
        $sessionEvents = SampleEvents::sessionEvents();
        $this->runAssertions($userEvents, "createUser", "transformUser");
        $this->runAssertions($sessionEvents, "createSession", "transformSession");

        $this->assertWeTestedAllRegisteredEvents(array_merge(
            $userEvents,
            $sessionEvents
        ));
    }

    private function runAssertions(array $events, string $creationMethod, string $transformationMethod)
    {
        $creationEvent = $events[0];
        $transformationEvents = array_slice($events, 1);

        $expectedAggregate = $creationEvent->$creationMethod();
        foreach ($transformationEvents as $event) {
            $expectedAggregate = $event->$transformationMethod($expectedAggregate);
        }

        $actualAggregate = AggregateFromEvents::aggregateFromEvents(
            $creationEvent,
            $transformationEvents
        );

        Assert::assertEquals(
            $expectedAggregate,
            $actualAggregate
        );

    }

    private function assertWeTestedAllRegisteredEvents(array $events) {
        $testedClasses = array_map(
            function ($event) {
                return get_class($event);
            },
            $events
        );
        sort($testedClasses);
        $allRegisteredEventClasses = EventReflectionBaseClass::allEventClasses();
        sort($allRegisteredEventClasses);

        Assert::assertEquals($testedClasses, $allRegisteredEventClasses);
    }
}