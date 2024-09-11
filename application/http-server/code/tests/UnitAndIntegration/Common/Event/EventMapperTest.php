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
use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderDeleted;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderMoved;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderRenamed;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Event\LoggedFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Event\LoggedRootFolderOpened;
use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationDeletedBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationPulledBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationRejectedByRecipient;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
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
     * @test
     */
    public function test_OneEvent_Serialization_Object(): void
    {
        $event = ContactRequestAccepted::fromContacts(
            Id::createNew(),
            Id::createNew(),
            $this->sampleMetadata(),
            Id::createNew(),
            Id::createNew()
        );

        $expectedSerializedEvent = SerializedEvent::fromProperties(
            $event->eventId()->id(),
            $event->aggregateId()->id(),
            $event->authorizerId()->id(),
            $event->sourceEventId(),
            $event->eventOccurredOn()->format('Y-m-d H:i:s.u'),
            'Messaging_Contact_ContactRequestAccepted',
            sprintf(
                '{"accepterContact":{"type":"galeas_id","id":"%s"},"acceptedContact":{"type":"galeas_id","id":"%s"}}',
                $event->accepterContact()->id(),
                $event->acceptedContact()->id()
            ),
            $this->sampleMetadataJson()
        );

        Assert::assertEquals(
            $expectedSerializedEvent,
            EventMapper::eventsToSerializedEvents([$event])[0]
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_OneEvent_Serialization_JSON(): void
    {
        $event = ContactRequestAccepted::fromContacts(
            Id::createNew(),
            Id::createNew(),
            $this->sampleMetadata(),
            Id::createNew(),
            Id::createNew()
        );

        $expectedJsonEvent = $this->jsonEncodeOrThrowException([
            'eventId' => $event->eventId()->id(),
            'aggregateId' => $event->aggregateId()->id(),
            'authorizerId' => $event->authorizerId()->id(),
            'sourceEventId' => $event->sourceEventId(),
            'eventOccurredOn' => $event->eventOccurredOn()->format('Y-m-d H:i:s.u'),
            'eventName' => 'Messaging_Contact_ContactRequestAccepted',
            'payload' => [
                'accepterContact' => [
                    'type' => 'galeas_id',
                    'id' => $event->accepterContact()->id(),
                ],
                'acceptedContact' => [
                    'type' => 'galeas_id',
                    'id' => $event->acceptedContact()->id(),
                ],
            ],
            'metadata' => json_decode($this->sampleMetadataJson(), true),
        ]);

        Assert::assertEquals(
            $expectedJsonEvent,
            EventMapper::eventsToJsonEvents([$event])[0]
        );
    }

    /**
     * @test
     */
    public function test_OneEvent_Deserialization_Object(): void
    {
        $expectedEvent = ContactRequestAccepted::fromContacts(
            Id::fromId('JAFhTcpo2k8EJfH0KUWlX6_ZMyOylpgqI_bbWf1RmCYYTxCU49iGcg8t'),
            Id::fromId('z4o73ftBsMNEudXR7zH-NnXNGOL_XJCbfXCfurZBaEUf6Ytaruq5q1GP'),
            $this->sampleMetadata(),
            Id::fromId('uYe5cS4tW1a5YvPeT8vitTeFY4858ZJMJffLaxcYvnGaIGNfKsu_Kpwy'),
            Id::fromId('THKla7zM6uSpKVS-fPYLOoz_8KbuqOdomCg6CmRy7plI8_9c2lLxbiTX')
        );

        $serializedEvent = SerializedEvent::fromProperties(
            $expectedEvent->eventId()->id(),
            'JAFhTcpo2k8EJfH0KUWlX6_ZMyOylpgqI_bbWf1RmCYYTxCU49iGcg8t',
            'z4o73ftBsMNEudXR7zH-NnXNGOL_XJCbfXCfurZBaEUf6Ytaruq5q1GP',
            null,
            $expectedEvent->eventOccurredOn()->format('Y-m-d H:i:s.u'),
            'Messaging_Contact_ContactRequestAccepted',
            sprintf(
                '{"accepterContact":{"type":"galeas_id","id":"%s"},"acceptedContact":{"type":"galeas_id","id":"%s"}}',
                'uYe5cS4tW1a5YvPeT8vitTeFY4858ZJMJffLaxcYvnGaIGNfKsu_Kpwy',
                'THKla7zM6uSpKVS-fPYLOoz_8KbuqOdomCg6CmRy7plI8_9c2lLxbiTX'
            ),
            sprintf(
                '{"0":1,"a":"a_1","b":[1,2,3],"c":{"c_1":32,"c_2":33},"12":{"type":"galeas_datetime","datetime":"%s","timezone":"UTC"},"123":true,"1312":13.020123,"abcdef":14,"1313":{"test":{"type":"galeas_datetime","datetime":"%s","timezone":"UTC"},"test 2":{"test 3":[[{"type":"galeas_datetime","datetime":"%s","timezone":"UTC"},12,15.1230123123,"15.1230123123"]]},"another test":[5,6,7]},"123123":false,"1231234":"","12312345":null}',
                '2017-11-15 15:07:47.450009',
                '2017-11-15 15:07:47.450010',
                '2017-11-15 15:07:47.450011'
            )
        );

        Assert::assertEquals(
            $expectedEvent,
            EventMapper::serializedEventsToEvents([$serializedEvent])[0]
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_OneEvent_Deserialization_JSON(): void
    {
        $expectedEvent = ContactRequestAccepted::fromContacts(
            Id::fromId('JAFhTcpo2k8EJfH0KUWlX6_ZMyOylpgqI_bbWf1RmCYYTxCU49iGcg8t'),
            Id::fromId('z4o73ftBsMNEudXR7zH-NnXNGOL_XJCbfXCfurZBaEUf6Ytaruq5q1GP'),
            $this->sampleMetadata(),
            Id::fromId('uYe5cS4tW1a5YvPeT8vitTeFY4858ZJMJffLaxcYvnGaIGNfKsu_Kpwy'),
            Id::fromId('THKla7zM6uSpKVS-fPYLOoz_8KbuqOdomCg6CmRy7plI8_9c2lLxbiTX')
        );

        $jsonEvent = $this->jsonEncodeOrThrowException([
            'eventId' => $expectedEvent->eventId()->id(),
            'aggregateId' => 'JAFhTcpo2k8EJfH0KUWlX6_ZMyOylpgqI_bbWf1RmCYYTxCU49iGcg8t',
            'authorizerId' => 'z4o73ftBsMNEudXR7zH-NnXNGOL_XJCbfXCfurZBaEUf6Ytaruq5q1GP',
            'sourceEventId' => null,
            'eventOccurredOn' => $expectedEvent->eventOccurredOn()->format('Y-m-d H:i:s.u'),
            'eventName' => 'Messaging_Contact_ContactRequestAccepted',
            'payload' => [
                'accepterContact' => [
                    'type' => 'galeas_id',
                    'id' => 'uYe5cS4tW1a5YvPeT8vitTeFY4858ZJMJffLaxcYvnGaIGNfKsu_Kpwy',
                ],
                'acceptedContact' => [
                    'type' => 'galeas_id',
                    'id' => 'THKla7zM6uSpKVS-fPYLOoz_8KbuqOdomCg6CmRy7plI8_9c2lLxbiTX',
                ],
            ],
            'metadata' => json_decode($this->sampleMetadataJson(), true),
        ]);

        Assert::assertEquals(
            $expectedEvent,
            EventMapper::jsonEventsToEvents([$jsonEvent])[0]
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
    public function test_Messaging_Contact_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            ContactRequested::fromContacts(
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
            ContactRequestAccepted::fromContacts(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
            ContactRequestRejected::fromContacts(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
            ContactRequestCancelled::fromContacts(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
            ContactDeleted::fromContacts(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
            ContactRequestedAgain::fromContacts(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }

    /**
     * @test
     */
    public function test_Messaging_Contact_AggregateFromEvents(): void
    {
        $userA = Id::createNew();
        $userB = Id::createNew();

        $creationEvent = ContactRequested::fromContacts(
            $userA,
            $this->sampleMetadata(),
            $userA,
            $userB
        );
        $aggregateId = $creationEvent->aggregateId();
        $transformationEvents = [
            ContactRequestAccepted::fromContacts(
                $aggregateId,
                $userA,
                $this->sampleMetadata(),
                $userA,
                $userB
            ),
            ContactRequestRejected::fromContacts(
                $aggregateId,
                $userA,
                $this->sampleMetadata(),
                $userA,
                $userB
            ),
            ContactRequestCancelled::fromContacts(
                $aggregateId,
                $userA,
                $this->sampleMetadata(),
                $userA,
                $userB
            ),
            ContactDeleted::fromContacts(
                $aggregateId,
                $userA,
                $this->sampleMetadata(),
                $userA,
                $userB
            ),
            ContactRequestedAgain::fromContacts(
                $aggregateId,
                $userA,
                $this->sampleMetadata(),
                $userA,
                $userB
            ),
        ];

        $aggregateFromEvents = EventMapper::aggregateFromEvents(
            $creationEvent,
            $transformationEvents
        );
        $expectedAggregate = Contact::fromStatus(
            $aggregateId,
            PendingContactRequest::fromContacts(
                $userA,
                $userB
            )
        );

        Assert::assertEquals($expectedAggregate, $aggregateFromEvents);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_Messaging_OneToOneConversation_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            OneToOneConversationStarted::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew(),
                new \DateTimeImmutable(),
                32
            ),
            OneToOneConversationPulledBySender::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata()
            ),
            OneToOneConversationDeletedBySender::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata()
            ),
            OneToOneConversationRejectedByRecipient::fromProperties(
                Id::createNew(),
                Id::createNew(),
                $this->sampleMetadata()
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }

    /**
     * @test
     */
    public function test_Messaging_OneToOneConversation_AggregateFromEvents(): void
    {
        $userA = Id::createNew();
        $userB = Id::createNew();

        $creationEvent = OneToOneConversationStarted::fromProperties(
            $userA,
            $this->sampleMetadata(),
            $userA,
            $userB,
            new \DateTimeImmutable(),
            98
        );

        $aggregateId = $creationEvent->aggregateId();
        $transformationEvents = [
            OneToOneConversationPulledBySender::fromProperties(
                $aggregateId,
                $userA,
                $this->sampleMetadata()
            ),
            OneToOneConversationDeletedBySender::fromProperties(
                $aggregateId,
                $userA,
                $this->sampleMetadata()
            ),
            OneToOneConversationRejectedByRecipient::fromProperties(
                $aggregateId,
                $userB,
                $this->sampleMetadata()
            ),
        ];

        $aggregateFromEvents = EventMapper::aggregateFromEvents(
            $creationEvent,
            $transformationEvents
        );
        $expectedAggregate = OneToOneConversation::fromProperties(
            $aggregateId,
            $userA,
            $userB,
            $creationEvent->maxNumberOfViews(),
            $creationEvent->expirationDate(),
            PushStatus::rejectedByRecipient()
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
     * @test
     *
     * @throws \Exception
     */
    public function test_Library_Folder_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            FolderCreated::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                'folder1',
                null
            ),
            FolderCreated::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                'folder1',
                Id::createNew()
            ),
            FolderDeleted::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew()
            ),
            FolderMoved::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                Id::createNew()
            ),
            FolderRenamed::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew(),
                'newName'
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }

    /**
     * @test
     */
    public function test_Library_Folder_AggregateFromEvents(): void
    {
        $ownerId = Id::createNew();
        $creationEvent = FolderCreated::fromProperties(
            $ownerId,
            $this->sampleMetadata(),
            'folder1',
            null
        );

        $transformationEvents = [
          FolderRenamed::fromProperties(
              $ownerId,
              $this->sampleMetadata(),
              $creationEvent->aggregateId(),
              'newName'
          ),
          FolderDeleted::fromProperties(
              $ownerId,
              $this->sampleMetadata(),
              $creationEvent->aggregateId()
          ),
        ];

        $aggregateFromEvents = EventMapper::aggregateFromEvents(
            $creationEvent,
            $transformationEvents
        );

        $expectedAggregate = Folder::fromProperties(
            $creationEvent->aggregateId(),
            'newName',
            null,
            $ownerId,
            true
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_Library_FolderOpened_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            LoggedFolderOpened::fromProperties(
                Id::createNew(),
                $this->sampleMetadata(),
                Id::createNew()
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function test_Library_LoggedRootFolderOpened_Serialization_Deserialization_ObjectAndJSON_PlusSchema(): void
    {
        $events = [
            LoggedRootFolderOpened::fromProperties(
                Id::createNew(),
                $this->sampleMetadata()
            ),
        ];

        $this->assertSerializationAndDeserialization_ObjectAndJSON_PlusSchema($events);
    }
}
