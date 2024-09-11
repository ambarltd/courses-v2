<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Controller;

use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Command\LogRootFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\CommandHandler\LogRootFolderOpened\LogRootFolderOpenedHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class LoggedRootFolderOpenedController extends BaseController
{
    public function __construct(
        string $environment,
        LogRootFolderOpenedHandler $logRootFolderOpenedHandler
    ) {
        parent::__construct($environment, [$logRootFolderOpenedHandler]);
    }

    /**
     * @Route("/library/logged-root-folder-opened/log-root-folder-opened", name="V1_Library_LoggedRootFolderOpened_LogRootFolderOpened", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_LoggedRootFolderOpened_LogRootFolderOpened")
     * @ResponseSchema(name="V1_Library_LoggedRootFolderOpened_LogRootFolderOpened")
     */
    public function open(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_LoggedRootFolderOpened_LogRootFolderOpened.json',
            'Response/V1_Library_LoggedRootFolderOpened_LogRootFolderOpened.json',
            LogRootFolderOpened::class,
            $this->getService(LogRootFolderOpenedHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
