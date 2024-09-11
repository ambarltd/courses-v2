<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\QueryHandler;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactListFromUserId;
use Galeas\Api\BoundedContext\Messaging\Contact\Query\ListContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\QueryHandler\ListContacts\ListContactsHandler;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class ListContactsHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $authorizerId = Id::createNew()->id();
        $command = new ListContacts();
        $command->authorizerId = $authorizerId;

        $handler = new ListContactsHandler(
            $this->mockForCommandHandlerWithCallback(
                ContactListFromUserId::class,
                'contactListFromUserId',
                function (string $userId) use ($authorizerId): array {
                    if ($userId === $authorizerId) {
                        return ['expected'];
                    }

                    return ['not-expected'];
                }
            )
        );
        Assert::assertEquals(
            ['expected'],
            $handler->handle($command)
        );
    }
}
