<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\Common\Event\EventDeserializer;
use Galeas\Api\Common\Event\EventReflectionBaseClass;
use Galeas\Api\Common\Event\EventSerializer;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use Galeas\Api\JsonSchema\JsonSchemaValidator;
use JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Util\SampleEvents;

class EventSerializerJsonSchemaTest extends UnitTestBase
{
    public function testSchemaMatching() {
        $events = array_merge(
            SampleEvents::userEvents(),
            SampleEvents::sessionEvents(),
        );

        $jsonSchemaValidator = new Validator();
        $schemaValidator = new JsonSchemaValidator($jsonSchemaValidator);
        $schemaFetcher = new JsonSchemaFetcher();

        foreach ($events as $event) {
            $serializedEvent = EventSerializer::eventsToSerializedEvents([$event])[0];
            $jsonEvent = $serializedEvent->toJson();

            try {
                $schema = $schemaFetcher->fetch('Event/'.$serializedEvent->eventName().'.json');
            } catch (\Throwable $exception) {
                Assert::fail(sprintf(
                    'Cannot load a schema for %s. Exception :%s.',
                    $serializedEvent->eventName(),
                    $exception->getMessage()
                ));
            }

            $errors = $schemaValidator->validate($jsonEvent, $schema);

            Assert::assertEquals(
                [],
                $errors,
                sprintf(
                    'Could not validate event against schema for event %s. Errors in json format: %s',
                    $serializedEvent->eventName(),
                    json_encode($errors)
                ),
            );
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