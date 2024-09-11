<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Controller;

use Galeas\Api\BoundedContext\Library\Folder\Command\CreateFolder;
use Galeas\Api\BoundedContext\Library\Folder\Command\DeleteFolder;
use Galeas\Api\BoundedContext\Library\Folder\Command\MoveFolder;
use Galeas\Api\BoundedContext\Library\Folder\Command\RenameFolder;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\CreateFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\DeleteFolder\DeleteFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\MoveFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder\RenameFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\Query\GetFolderContents;
use Galeas\Api\BoundedContext\Library\Folder\Query\GetRootFolderContents;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\GetFolderContentsHandler;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetRootFolderContents\GetRootFolderContentsHandler;
use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\Controller\RequestSchema;
use Galeas\Api\Common\Controller\ResponseSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/v1")
 */
class FolderController extends BaseController
{
    public function __construct(
        string $environment,
        CreateFolderHandler $createFolderHandler,
        RenameFolderHandler $renameFolderHandler,
        DeleteFolderHandler $deleteFolderHandler,
        MoveFolderHandler $moveFolderHandler,
        GetFolderContentsHandler $getFolderContentsHandler,
        GetRootFolderContentsHandler $getRootFolderContentsHandler
    ) {
        parent::__construct(
            $environment,
            [
                $createFolderHandler,
                $renameFolderHandler,
                $deleteFolderHandler,
                $moveFolderHandler,
                $getFolderContentsHandler,
                $getRootFolderContentsHandler,
            ]
        );
    }

    /**
     * @Route("/library/folder/create-folder", name="V1_Library_Folder_CreateFolder", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_Folder_CreateFolder")
     * @ResponseSchema(name="V1_Library_Folder_CreateFolder")
     */
    public function create(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_Folder_CreateFolder.json',
            'Response/V1_Library_Folder_CreateFolder.json',
            CreateFolder::class,
            $this->getService(CreateFolderHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/library/folder/rename-folder", name="V1_Library_Folder_RenameFolder", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_Folder_RenameFolder")
     * @ResponseSchema(name="V1_Library_Folder_RenameFolder")
     */
    public function rename(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_Folder_RenameFolder.json',
            'Response/V1_Library_Folder_RenameFolder.json',
            RenameFolder::class,
            $this->getService(RenameFolderHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/library/folder/delete-folder", name="V1_Library_Folder_DeleteFolder", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_Folder_DeleteFolder")
     * @ResponseSchema(name="V1_Library_Folder_DeleteFolder")
     */
    public function delete(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_Folder_DeleteFolder.json',
            'Response/V1_Library_Folder_DeleteFolder.json',
            DeleteFolder::class,
            $this->getService(DeleteFolderHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/library/folder/move-folder", name="V1_Library_Folder_MoveFolder", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_Folder_MoveFolder")
     * @ResponseSchema(name="V1_Library_Folder_MoveFolder")
     */
    public function move(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_Folder_MoveFolder.json',
            'Response/V1_Library_Folder_MoveFolder.json',
            MoveFolder::class,
            $this->getService(MoveFolderHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/library/folder/get-folder-contents", name="V1_Library_Folder_GetFolderContents", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_Folder_GetFolderContents")
     * @ResponseSchema(name="V1_Library_Folder_GetFolderContents")
     */
    public function getFolderContents(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_Folder_GetFolderContents.json',
            'Response/V1_Library_Folder_GetFolderContents.json',
            GetFolderContents::class,
            $this->getService(GetFolderContentsHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/library/folder/get-root-folder-contents", name="V1_Library_Folder_GetRootFolderContents", methods={"POST"})
     *
     * @RequestSchema(name="V1_Library_Folder_GetRootFolderContents")
     * @ResponseSchema(name="V1_Library_Folder_GetRootFolderContents")
     */
    public function getRootFolderContents(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/V1_Library_Folder_GetRootFolderContents.json',
            'Response/V1_Library_Folder_GetRootFolderContents.json',
            GetRootFolderContents::class,
            $this->getService(GetRootFolderContentsHandler::class),
            null,
            Response::HTTP_OK
        );
    }
}
