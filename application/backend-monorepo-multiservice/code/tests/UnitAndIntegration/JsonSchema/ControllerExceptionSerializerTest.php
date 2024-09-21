<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use Galeas\Api\Common\Controller\BaseController;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\JsonSchema\ControllerExceptionsSerializer;
use Galeas\Api\JsonSchema\ExceptionSerializerFailed;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ControllerExceptionSerializerTest extends UnitTestBase
{
    public function testCommandHandler(): void
    {
        $serializer = new ControllerExceptionsSerializer();
        $result = $serializer->getSerializedExceptionsFromControllerClassAndMethod(
            MockController::class.'::commandHandler'
        );

        Assert::assertEquals(
            [
                '$schema' => 'http://json-schema.org/draft-04/schema#',
                'anyOf' => [
                    [
                        'title' => 'Other Errors',
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'payload' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'errors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'errorIdentifier' => [
                                        'type' => 'string',
                                    ],
                                    'errorMessage' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required' => [
                                    'errors',
                                    'errorIdentifier',
                                    'errorMessage',
                                ],
                            ],
                            'httpCode' => [
                                'type' => 'integer',
                            ],
                        ],
                        'required' => [
                            'payload',
                            'httpCode',
                        ],
                    ],
                    [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'payload' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'errors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'errorIdentifier' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'json_schema_validation_error',
                                        ],
                                    ],
                                    'errorMessage' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required' => [
                                    'errors',
                                    'errorIdentifier',
                                    'errorMessage',
                                ],
                            ],
                            'httpCode' => [
                                'type' => 'integer',
                                'enum' => [
                                    400,
                                ],
                            ],
                        ],
                        'required' => [
                            'payload',
                            'httpCode',
                        ],
                    ],
                    [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'payload' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'errors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'errorIdentifier' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'Common_EventStoreCannotRead',
                                            'Common_EventStoreCannotWrite',
                                            'Common_ProjectionCannotRead',
                                            'ControllerExceptionSerializerTest_MockException',
                                            'internal_server_error',
                                        ],
                                    ],
                                    'errorMessage' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required' => [
                                    'errors',
                                    'errorIdentifier',
                                    'errorMessage',
                                ],
                            ],
                            'httpCode' => [
                                'type' => 'integer',
                                'enum' => [
                                    500,
                                ],
                            ],
                        ],
                        'required' => [
                            'payload',
                            'httpCode',
                        ],
                    ],
                ],
            ],
            $result
        );
    }

    public function testQueryHandler(): void
    {
        $serializer = new ControllerExceptionsSerializer();
        $result = $serializer->getSerializedExceptionsFromControllerClassAndMethod(
            MockController::class.'::queryHandler'
        );

        Assert::assertEquals(
            [
                '$schema' => 'http://json-schema.org/draft-04/schema#',
                'anyOf' => [
                    [
                        'title' => 'Other Errors',
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'payload' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'errors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'errorIdentifier' => [
                                        'type' => 'string',
                                    ],
                                    'errorMessage' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required' => [
                                    'errors',
                                    'errorIdentifier',
                                    'errorMessage',
                                ],
                            ],
                            'httpCode' => [
                                'type' => 'integer',
                            ],
                        ],
                        'required' => [
                            'payload',
                            'httpCode',
                        ],
                    ],
                    [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'payload' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'errors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'errorIdentifier' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'json_schema_validation_error',
                                        ],
                                    ],
                                    'errorMessage' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required' => [
                                    'errors',
                                    'errorIdentifier',
                                    'errorMessage',
                                ],
                            ],
                            'httpCode' => [
                                'type' => 'integer',
                                'enum' => [
                                    400,
                                ],
                            ],
                        ],
                        'required' => [
                            'payload',
                            'httpCode',
                        ],
                    ],
                    [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'payload' => [
                                'type' => 'object',
                                'additionalProperties' => false,
                                'properties' => [
                                    'errors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'type' => 'string',
                                        ],
                                    ],
                                    'errorIdentifier' => [
                                        'type' => 'string',
                                        'enum' => [
                                            'Common_ProjectionCannotRead',
                                            'internal_server_error',
                                        ],
                                    ],
                                    'errorMessage' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'required' => [
                                    'errors',
                                    'errorIdentifier',
                                    'errorMessage',
                                ],
                            ],
                            'httpCode' => [
                                'type' => 'integer',
                                'enum' => [
                                    500,
                                ],
                            ],
                        ],
                        'required' => [
                            'payload',
                            'httpCode',
                        ],
                    ],
                ],
            ],
            $result
        );
    }

    public function testCannotFindLineWithHandlerServiceStringInHandler(): void
    {
        $this->expectExceptionMessage("Cannot find line which might have a handler service class in Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockController::noHandler");
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSerializer();
        $serializer->getSerializedExceptionsFromControllerClassAndMethod(
            MockController::class.'::noHandler'
        );
    }

    public function testCouldNotBuildReflectionMethodForControllerClassAndMethod(): void
    {
        $this->expectExceptionMessage("Could not build reflection method for Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockController::methodDoesNotExist");
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSerializer();
        $serializer->getSerializedExceptionsFromControllerClassAndMethod(
            MockController::class.'::methodDoesNotExist'
        );
    }

    public function testCannotFindAnnotationForHandlerMethod(): void
    {
        $this->expectExceptionMessage("Cannot find annotation for handle method in service of class Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockCommandHandlerWithoutAnnotation");
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSerializer();
        $serializer->getSerializedExceptionsFromControllerClassAndMethod(
            MockController::class.'::commandHandlerWithoutAnnotation'
        );
    }

    public function testAllHandlerExceptionClassesMustImplementTheBaseException(): void
    {
        $this->expectExceptionMessage("All handler exception classes must implement the base exception - Failed for Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockController::commandHandlerWithNonBaseException");
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSerializer();
        $serializer->getSerializedExceptionsFromControllerClassAndMethod(
            MockController::class.'::commandHandlerWithNonBaseException'
        );
    }
}

class MockCommand
{
    public int $maxInt;
}

class MockQuery
{
    public string $message;
}

class MockCommandHandler
{
    /**
     * @throws MockException
     * @throws ProjectionCannotRead|EventStoreCannotWrite
     * @throws EventStoreCannotRead
     */
    public function handle(MockCommand $mockCommand): void
    {
        $random = rand(1, $mockCommand->maxInt);
        switch ($random) {
            case 1:
                throw new MockException();
                break;
            case 2:
                throw new ProjectionCannotRead(new \InvalidArgumentException());
                break;
            case 3:
                throw new EventStoreCannotWrite(new \InvalidArgumentException());
                break;
            case 4:
                throw new \Galeas\Api\Common\ExceptionBase\EventStoreCannotRead(new \RuntimeException());
                break;
            case 5:
            default:
                throw new ABC(new \RuntimeException());
                break;
        }
    }
}

class MockQueryHandler
{
    /**
     * @throws ProjectionCannotRead
     */
    public function handle(MockQuery $mockCommand): void
    {
        switch ($mockCommand->message) {
            case 'x':
            default:
                throw new ProjectionCannotRead(new \RuntimeException());
                break;
        }
    }
}

class MockCommandHandlerWithoutAnnotation
{
    public function handle(MockCommand $mockCommand): void
    {
    }
}

class MockCommandHandlerWithNonBaseException
{
    /**
     * @throws \RuntimeException
     */
    public function handle(MockCommand $mockCommand): void
    {
        throw new \RuntimeException(strval($mockCommand->maxInt));
    }
}

class MockException extends InternalServerErrorException
{
    public static function getErrorIdentifier(): string
    {
        return 'ControllerExceptionSerializerTest_MockException';
    }
}

class MockController extends BaseController
{
    public function commandHandler(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/Mock.json',
            'Response/Mock.json',
            MockCommand::class,
            $this->getService(MockCommandHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    public function queryHandler(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/Mock.json',
            'Response/Mock.json',
            MockQuery::class,
            $this->getService(MockQueryHandler::class),
            null,
            Response::HTTP_OK
        );
    }

    public function noHandler(): Response
    {
        return new Response();
    }

    public function commandHandlerWithoutAnnotation(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/Mock.json',
            'Response/Mock.json',
            MockCommand::class,
            $this->getService(MockCommandHandlerWithoutAnnotation::class),
            null,
            Response::HTTP_OK
        );
    }

    public function commandHandlerWithNonBaseException(Request $request): Response
    {
        return $this->jsonPostRequestJsonResponse(
            $request,
            'Request/Mock.json',
            'Response/Mock.json',
            MockCommand::class,
            $this->getService(MockCommandHandlerWithNonBaseException::class),
            null,
            Response::HTTP_OK
        );
    }
}
