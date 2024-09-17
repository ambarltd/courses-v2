<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\Common\Event\EventDeserializer;
use Galeas\Api\Common\Event\EventReflectionBaseClass;
use Galeas\Api\Common\Event\EventSerializer;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class SerializationAndDeserializationTest extends UnitTestBase
{
    public function testSerializationAndDeserialization() {
        $events = array_merge(
            SampleEvents::userEvents(),
            SampleEvents::sessionEvents(),
        );

        $serializedEvents = EventSerializer::eventsToSerializedEvents($events);
        $deserializedEvents = EventDeserializer::serializedEventsToEvents($serializedEvents);

        foreach ($events as $key => $event) {
            Assert::assertEquals($event, $deserializedEvents[$key]);
        }

        $this->assertWeTestedAllRegisteredEvents($events);
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