<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\Common\Event\AggregateFromEvents;
use Galeas\Api\Common\Event\EventReflectionBaseClass;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class AggregateFromEventsTest extends UnitTest
{
    public function testAggregateFromEvents(): void
    {
        $userEvents = SampleEvents::userEvents();
        $sessionEvents = SampleEvents::sessionEvents();
        $productEvents = SampleEvents::creditCardProductEvents();
        $this->runAssertions($userEvents, 'createUser', 'transformUser');
        $this->runAssertions($sessionEvents, 'createSession', 'transformSession');
        $this->runAssertions($productEvents, 'createProduct', 'transformProduct');

        $this->assertWeTestedAllRegisteredEvents(array_merge(
            $userEvents,
            $sessionEvents,
            $productEvents
        ));
    }

    private function runAssertions(array $events, string $creationMethod, string $transformationMethod): void
    {
        $creationEvent = $events[0];
        $transformationEvents = \array_slice($events, 1);

        $expectedAggregate = $creationEvent->{$creationMethod}();
        foreach ($transformationEvents as $event) {
            $expectedAggregate = $event->{$transformationMethod}($expectedAggregate);
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

    private function assertWeTestedAllRegisteredEvents(array $events): void
    {
        $testedClasses = array_map(
            static fn ($event) => $event::class,
            $events
        );
        sort($testedClasses);
        $allRegisteredEventClasses = EventReflectionBaseClass::allEventClasses();
        sort($allRegisteredEventClasses);

        Assert::assertEquals($testedClasses, $allRegisteredEventClasses);
    }
}
