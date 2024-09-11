<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Controller;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\DeleteOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\PullOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\RejectOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\StartOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\DeleteOneToOneConversation\DeleteOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\PullOneToOneConversation\PullOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation\RejectOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\StartOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Query\ListOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\QueryHandler\ListOneToOneConversation\ListOneToOneConversationHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class OneToOneConversationController extends BaseController
{
    public function __construct(
        string $environment,
        StartOneToOneConversationHandler $startOneToOneConversationHandler,
        PullOneToOneConversationHandler $pullOneToOneConversationHandler,
        DeleteOneToOneConversationHandler $deleteOneToOneConversationHandler,
        RejectOneToOneConversationHandler $rejectOneToOneConversationHandler,
        ListOneToOneConversationHandler $listOneToOneConversationHandler
    ) {
        parent::__construct(
            $environment,
            [
                $startOneToOneConversationHandler,
                $pullOneToOneConversationHandler,
                $deleteOneToOneConversationHandler,
                $rejectOneToOneConversationHandler,
                $listOneToOneConversationHandler,
            ]
        );
    }

    /**
     * @Route("/messaging/one-to-one-conversation/start-one-to-one-conversation", name="V1_Messaging_OneToOneConversation_StartOneToOneConversation", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_OneToOneConversation_StartOneToOneConversation")
     * @ResponseSchema(name="V1_Messaging_OneToOneConversation_StartOneToOneConversation")
     */
    public function startOneToOneConversation(Request $request): Response
    {
        $response = $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_OneToOneConversation_StartOneToOneConversation.json',
            'Response/V1_Messaging_OneToOneConversation_StartOneToOneConversation.json',
            StartOneToOneConversation::class,
            $this->getService(StartOneToOneConversationHandler::class),
            null,
            Response::HTTP_OK
        );

        return $response;
    }

    /**
     * @Route("/messaging/one-to-one-conversation/pull-one-to-one-conversation", name="V1_Messaging_OneToOneConversation_PullOneToOneConversation", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_OneToOneConversation_PullOneToOneConversation")
     * @ResponseSchema(name="V1_Messaging_OneToOneConversation_PullOneToOneConversation")
     */
    public function pullConversation(Request $request): Response
    {
        $response = $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_OneToOneConversation_PullOneToOneConversation.json',
            'Response/V1_Messaging_OneToOneConversation_PullOneToOneConversation.json',
            PullOneToOneConversation::class,
            $this->getService(PullOneToOneConversationHandler::class),
            null,
            Response::HTTP_OK
        );

        return $response;
    }

    /**
     * @Route("/messaging/one-to-one-conversation/delete-one-to-one-conversation", name="V1_Messaging_OneToOneConversation_DeleteOneToOneConversation", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_OneToOneConversation_DeleteOneToOneConversation")
     * @ResponseSchema(name="V1_Messaging_OneToOneConversation_DeleteOneToOneConversation")
     */
    public function deleteConversation(Request $request): Response
    {
        $response = $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_OneToOneConversation_DeleteOneToOneConversation.json',
            'Response/V1_Messaging_OneToOneConversation_DeleteOneToOneConversation.json',
            DeleteOneToOneConversation::class,
            $this->getService(DeleteOneToOneConversationHandler::class),
            null,
            Response::HTTP_OK
        );

        return $response;
    }

    /**
     * @Route("/messaging/one-to-one-conversation/reject-one-to-one-conversation", name="V1_Messaging_OneToOneConversation_RejectOneToOneConversation", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_OneToOneConversation_RejectOneToOneConversation")
     * @ResponseSchema(name="V1_Messaging_OneToOneConversation_RejectOneToOneConversation")
     */
    public function rejectConversation(Request $request): Response
    {
        $response = $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_OneToOneConversation_RejectOneToOneConversation.json',
            'Response/V1_Messaging_OneToOneConversation_RejectOneToOneConversation.json',
            RejectOneToOneConversation::class,
            $this->getService(RejectOneToOneConversationHandler::class),
            null,
            Response::HTTP_OK
        );

        return $response;
    }

    /**
     * @Route("/messaging/one-to-one-conversation/list-one-one-conversation", name="V1_Messaging_OneToOneConversation_ListOneToOneConversation", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_OneToOneConversation_ListOneToOneConversation")
     * @ResponseSchema(name="V1_Messaging_OneToOneConversation_ListOneToOneConversation")
     */
    public function listConversations(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_OneToOneConversation_ListOneToOneConversation.json',
            'Response/V1_Messaging_OneToOneConversation_ListOneToOneConversation.json',
            ListOneToOneConversation::class,
            $this->getService(ListOneToOneConversationHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
