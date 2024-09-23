<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\Common\Event\EventDeserializer;
use Galeas\Api\Common\Event\EventReflectionBaseClass;
use Galeas\Api\Common\Event\EventSerializer;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SerializationAndDeserializationTest extends UnitTest
{
    public function testSerializationAndDeserialization(): void
    {
        $events = array_merge(
            SampleEvents::userEvents(),
            SampleEvents::sessionEvents(),
            SampleEvents::creditCardProductEvents(),
        );

        $serializedEvents = EventSerializer::eventsToSerializedEvents($events);
        $deserializedEvents = EventDeserializer::serializedEventsToEvents($serializedEvents);

        foreach ($events as $key => $event) {
            Assert::assertEquals($event, $deserializedEvents[$key]);
        }

        $this->assertWeTestedAllRegisteredEvents($events);
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
