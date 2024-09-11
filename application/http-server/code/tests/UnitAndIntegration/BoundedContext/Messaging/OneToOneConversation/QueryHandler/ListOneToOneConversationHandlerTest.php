<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\QueryHandler;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation\ConversationListFromUserId;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Query\ListOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\QueryHandler\ListOneToOneConversation\ListOneToOneConversationHandler;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class ListOneToOneConversationHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $authorizerId = Id::createNew()->id();
        $command = new ListOneToOneConversation();
        $command->authorizerId = $authorizerId;

        $handler = new ListOneToOneConversationHandler(
            $this->mockForCommandHandlerWithCallback(
                ConversationListFromUserId::class,
                'conversationListFromUserId',
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
