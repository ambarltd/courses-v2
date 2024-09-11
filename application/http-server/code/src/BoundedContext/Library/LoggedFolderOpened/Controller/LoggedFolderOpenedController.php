<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Controller;

use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Command\LogFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened\LogFolderOpenedHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class LoggedFolderOpenedController extends BaseController
{
    public function __construct(
        string $environment,
        LogFolderOpenedHandler $logFolderOpenedHandler
    ) {
        parent::__construct($environment, [$logFolderOpenedHandler]);
    }

    /**
     * @Route("/library/logged-folder-opened/log-folder-opened", name="V1_Library_LoggedFolderOpened_LogFolderOpened", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_LoggedFolderOpened_LogFolderOpened")
     * @ResponseSchema(name="V1_Library_LoggedFolderOpened_LogFolderOpened")
     */
    public function open(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_LoggedFolderOpened_LogFolderOpened.json',
            'Response/V1_Library_LoggedFolderOpened_LogFolderOpened.json',
            LogFolderOpened::class,
            $this->getService(LogFolderOpenedHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
