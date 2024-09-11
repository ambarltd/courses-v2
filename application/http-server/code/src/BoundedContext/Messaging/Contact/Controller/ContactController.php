<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Controller;

use Galeas\Api\BoundedContext\Messaging\Contact\Command\AcceptContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\CancelContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\DeleteContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\RejectContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\RequestContact;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\AcceptContactRequest\AcceptContactRequestHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\CancelContactRequestHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\DeleteContactHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\RejectContactRequestHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\RequestContactHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\Query\ListContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\QueryHandler\ListContacts\ListContactsHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class ContactController extends BaseController
{
    public function __construct(
        string $environment,
        RequestContactHandler $requestContactHandler,
        RejectContactRequestHandler $rejectContactRequestHandler,
        AcceptContactRequestHandler $acceptContactRequestHandler,
        CancelContactRequestHandler $cancelContactRequestHandler,
        DeleteContactHandler $deleteContactHandler,
        ListContactsHandler $listContactsHandler
    ) {
        parent::__construct(
            $environment,
            [
                $requestContactHandler,
                $rejectContactRequestHandler,
                $acceptContactRequestHandler,
                $cancelContactRequestHandler,
                $deleteContactHandler,
                $listContactsHandler,
            ]
        );
    }

    /**
     * @Route("/messaging/contact/request-contact", name="V1_Messaging_Contact_RequestContact", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_Contact_RequestContact")
     * @ResponseSchema(name="V1_Messaging_Contact_RequestContact")
     */
    public function requestContact(Request $request): Response
    {
        $response = $this->jsonPostRequestJsonResponse(
                $request,
                'Request/V1_Messaging_Contact_RequestContact.json',
                'Response/V1_Messaging_Contact_RequestContact.json',
                RequestContact::class,
                $this->getService(RequestContactHandler::class),
                null,
                Response::HTTP_OK
            );

        return $response;
    }

    /**
     * @Route("/messaging/contact/reject-contact-request", name="V1_Messaging_Contact_RejectContactRequest", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_Contact_RejectContactRequest")
     * @ResponseSchema(name="V1_Messaging_Contact_RejectContactRequest")
     */
    public function rejectContactRequest(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_Contact_RejectContactRequest.json',
            'Response/V1_Messaging_Contact_RejectContactRequest.json',
            RejectContactRequest::class,
            $this->getService(RejectContactRequestHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/messaging/contact/accept-contact-request", name="V1_Messaging_Contact_AcceptContactRequest", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_Contact_AcceptContactRequest")
     * @ResponseSchema(name="V1_Messaging_Contact_AcceptContactRequest")
     */
    public function acceptContactRequest(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_Contact_AcceptContactRequest.json',
            'Response/V1_Messaging_Contact_AcceptContactRequest.json',
            AcceptContactRequest::class,
            $this->getService(AcceptContactRequestHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/messaging/contact/cancel-contact-request", name="V1_Messaging_Contact_CancelContactRequest", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_Contact_CancelContactRequest")
     * @ResponseSchema(name="V1_Messaging_Contact_CancelContactRequest")
     */
    public function cancelContactRequest(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_Contact_CancelContactRequest.json',
            'Response/V1_Messaging_Contact_CancelContactRequest.json',
            CancelContactRequest::class,
            $this->getService(CancelContactRequestHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/messaging/contact/delete-contact", name="V1_Messaging_Contact_DeleteContact", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_Contact_DeleteContact")
     * @ResponseSchema(name="V1_Messaging_Contact_DeleteContact")
     */
    public function deleteContact(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_Contact_DeleteContact.json',
            'Response/V1_Messaging_Contact_DeleteContact.json',
            DeleteContact::class,
            $this->getService(DeleteContactHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/messaging/contact/list-contacts", name="V1_Messaging_Contact_ListContacts", methods={"POST"})
     *
     * @RequestSchema(name="V1_Messaging_Contact_ListContacts")
     * @ResponseSchema(name="V1_Messaging_Contact_ListContacts")
     */
    public function listContacts(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Messaging_Contact_ListContacts.json',
            'Response/V1_Messaging_Contact_ListContacts.json',
            ListContacts::class,
            $this->getService(ListContactsHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
