<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Common\Event;

use Galeas\Api\Common\Event\SerializedEvent;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class SerializedEventTest extends UnitTestBase
{
    public function testCreate(): void
    {
        $serializedEvent = SerializedEvent::fromProperties(
           'eventId1',
           'aggregateId1',
           23,
           'causationId1',
           'correlationId1',
           'recordedOn1',
           'eventName1',
           $this->jsonEncodeOrThrowException(['field_1' => 'test_1']),
           $this->jsonEncodeOrThrowException(['field_2' => 'test_2'])
       );

        Assert::assertEquals(
           'eventId1',
           $serializedEvent->eventId()
       );

        Assert::assertEquals(
           'aggregateId1',
           $serializedEvent->aggregateId()
       );

        Assert::assertEquals(
           'causationId1',
           $serializedEvent->causationId()
       );

        Assert::assertEquals(
           'correlationId1',
           $serializedEvent->correlationId()
       );

        Assert::assertEquals(
           'recordedOn1',
           $serializedEvent->recordedOn()
       );

        Assert::assertEquals(
           'eventName1',
           $serializedEvent->eventName()
       );

        Assert::assertEquals(
           $this->jsonEncodeOrThrowException(['field_1' => 'test_1']),
           $serializedEvent->jsonPayload()
       );

        Assert::assertEquals(
           $this->jsonEncodeOrThrowException(['field_2' => 'test_2']),
           $serializedEvent->jsonMetadata()
       );
    }

    public function testToJsonFromJson(): void {

        $serializedEvent = SerializedEvent::fromProperties(
            'eventId1',
            'aggregateId1',
            23,
            'causationId1',
            'correlationId1',
            'recordedOn1',
            'eventName1',
            $this->jsonEncodeOrThrowException(['field_1' => 'test_1']),
            $this->jsonEncodeOrThrowException(['field_2' => 'test_2'])
        );
        Assert::assertEquals(
            $serializedEvent,
            SerializedEvent::fromJson($serializedEvent->toJson())
        );
    }

    private function jsonEncodeOrThrowException(array $encodeThis): string
    {
        $encoded = json_encode($encodeThis);

        if (is_string($encoded)) {
            return $encoded;
        }

        throw new \Exception();
    }
}
