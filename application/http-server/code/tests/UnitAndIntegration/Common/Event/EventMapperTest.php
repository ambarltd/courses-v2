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
use Galeas\Api\BoundedContext\Identity\User\ValueObject\RequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedOut;
use Galeas\Api\BoundedContext\Security\Session\Event\TokenRefreshed;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventMapper;
use Galeas\Api\Common\Event\SerializedEvent;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use Galeas\Api\JsonSchema\JsonSchemaValidator;
use JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class EventMapperTest extends UnitTestBase
{
    private function sampleMetadata(): array
    {
        return [
            1,
            'a' => 'a_1',
            'b' => [1, 2, 3],
            'c' => [
                'c_1' => 32,
                'c_2' => 33,
            ],
            12 => \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', '2017-11-15 15:07:47.450009'),
            '123' => true,
            '1312' => 13.020123,
            'abcdef' => 14,
            [
                'test' => \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', '2017-11-15 15:07:47.450010'),
                'test 2' => [
                    'test 3' => [
                        [
                            \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', '2017-11-15 15:07:47.450011'),
                            12,
                            15.1230123123,
                            '15.1230123123',
                        ],
                    ],
                ],
                'another test' => [5, 6, 7],
            ],
            '123123' => false,
            '1231234' => '',
            '12312345' => null,
        ];
    }

    private function sampleMetadataJson(): string
    {
        return sprintf(
            '{"0":1,"a":"a_1","b":[1,2,3],"c":{"c_1":32,"c_2":33},"12":{"type":"galeas_datetime","datetime":"%s","timezone":"UTC"},"123":true,"1312":13.020123,"abcdef":14,"1313":{"test":{"type":"galeas_datetime","datetime":"%s","timezone":"UTC"},"test 2":{"test 3":[[{"type":"galeas_datetime","datetime":"%s","timezone":"UTC"},12,15.1230123123,"15.1230123123"]]},"another test":[5,6,7]},"123123":false,"1231234":"","12312345":null}',
            '2017-11-15 15:07:47.450009',
            '2017-11-15 15:07:47.450010',
            '2017-11-15 15:07:47.450011'
        );
    }



    /**
     * @param Event[] $events
     *
     * @throws \Exception
     */
    private function assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema(array $events): void
    {
        Assert::assertEquals(
            $events,
            EventMapper::serializedEventsToEvents(
                EventMapper::eventsToSerializedEvents(
                    $events
                )
            )
        );

        Assert::assertEquals(
            $events,
            EventMapper::jsonEventsToEvents(
                EventMapper::eventsToJsonEvents(
                    $events
                )
            )
        );

        $jsonSchemaValidator = new Validator();
        $schemaValidator = new JsonSchemaValidator($jsonSchemaValidator);
        $schemaFetcher = new JsonSchemaFetcher();
        foreach ($events as $event) {
            $serializedEvent = EventMapper::eventsToSerializedEvents(
                [$event]
            )[0];

            try {
                $schema = $schemaFetcher->fetch('Event/'.$serializedEvent->eventName().'.json');
            } catch (\Throwable $exception) {
                Assert::fail('Cannot load a schema for '.$serializedEvent->eventName());

                return;
            }

            $json = EventMapper::eventsToJsonEvents(
                [$event]
            )[0];

            $errors = $schemaValidator->validate($json, $schema);

            $this->assertEquals(
                [],
                $errors,
                'Could not validate event against schema for event '.$serializedEvent->eventName().'. '.
                'Errors in json format: '.$this->jsonEncodeOrThrowException($errors)
            );
        }
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_Identity_User_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            SignedUp::fromProperties(
                $this->sampleMetadata(),
                'email',
                'password',
                'username',
                true
            ),
            PrimaryEmailVerified::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                'verified_with_code_123123123'
            ),
            PrimaryEmailChangeRequested::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                'new@example.com',
                'hashed_password_213123123'
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }

    /**
     * @test
     */
    public function test_Identity_User_AggregateFromEvents(): void
    {
        $creationEvent = SignedUp::fromProperties(
            $this->sampleMetadata(),
            'email@example.com',
            'password',
            'username',
            true
        );
        $aggregateId = $creationEvent->aggregateId();
        $transformationEvents = [
            PrimaryEmailVerified::fromProperties(
                $aggregateId,
                $aggregateId,
                $this->sampleMetadata(),
                'verified_with_code_123123123'
            ),
            PrimaryEmailChangeRequested::fromProperties(
                $aggregateId,
                $aggregateId,
                $this->sampleMetadata(),
                'new@example.com',
                'hashed_password_213123123'
            ),
        ];

        $aggregateFromEvents = EventMapper::aggregateFromEvents(
            $creationEvent,
            $transformationEvents
        );
        $expectedAggregate = User::fromProperties(
            $aggregateId,
            RequestedNewEmail::fromEmailsAndVerificationCode(
                Email::fromEmail(
                    'email@example.com'
                ),
                Email::fromEmail(
                    'new@example.com'
                ),
                VerificationCode::fromVerificationCode(
                    $transformationEvents[1]->newVerificationCode()
                )
            ),
            HashedPassword::fromHash(
                $creationEvent->hashedPassword()
            ),
            AccountDetails::fromDetails(
                'username',
                true
            )
        );

        Assert::assertEquals($expectedAggregate, $aggregateFromEvents);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_Security_Session_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            SignedIn::fromProperties(
                $this->sampleMetadata(),
                Id::createNew(),
                'username',
                'email',
                'hashedPassword',
                'deviceLabel',
                'ip'
            ),
            TokenRefreshed::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                'refresh_ip',
                'refresh_existingSessionToken'
            ),
            SignedOut::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                'signOut_ip',
                'signOut_SessionToken'
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }

    /**
     * @test
     */
    public function test_Security_Session_AggregateFromEvents(): void
    {
        $asUser = Id::createNew();

        $creationEvent = SignedIn::fromProperties(
            $this->sampleMetadata(),
            $asUser,
            'username',
            'email',
            'hashedPassword',
            'deviceLabel',
            'ip'
        );

        $aggregateId = $creationEvent->aggregateId();
        $transformationEvents = [
            TokenRefreshed::fromProperties(
                $aggregateId,
                $asUser,
                $this->sampleMetadata(),
                'refresh_ip',
                'refresh_existingSessionToken'
            ),
            SignedOut::fromProperties(
                $aggregateId,
                $asUser,
                $this->sampleMetadata(),
                'signOut_ip',
                'signOut_SessionToken'
            ),
        ];

        $aggregateFromEvents = EventMapper::aggregateFromEvents(
            $creationEvent,
            $transformationEvents
        );
        $expectedAggregate = Session::fromProperties(
            $aggregateId,
            SessionDetails::fromProperties(
                $asUser,
                'username',
                'email',
                'hashedPassword',
                'deviceLabel',
                'refresh_ip',
                $transformationEvents[0]->refreshedSessionToken()
            ),
            SessionIsSignedOut::fromProperties(
                'signOut_SessionToken',
                'signOut_ip'
            )
        );

        Assert::assertEquals($expectedAggregate, $aggregateFromEvents);
    }

    /**
     * @throws \Exception
     */
    private function jsonEncodeOrThrowException(array $encodeThis): string
    {
        $encoded = json_encode($encodeThis);

        if (is_string($encoded)) {
            return $encoded;
        }

        throw new \Exception();
    }
}
