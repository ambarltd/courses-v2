<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use Galeas\Api\CommonController\BaseController;
use Galeas\Api\CommonException\EventStoreCannotRead;
use Galeas\Api\CommonException\EventStoreCannotWrite;
use Galeas\Api\CommonException\InternalServerErrorException;
use Galeas\Api\CommonException\ProjectionCannotRead;
use Galeas\Api\JsonSchema\ControllerExceptionsSchemaGenerator;
use Galeas\Api\JsonSchema\ExceptionSerializerFailed;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class ControllerExceptionSchemaGeneratorTest extends UnitTest
{
    /**
     * @throws \Exception
     */
    public function testCommandHandler(): void
    {
        $serializer = new ControllerExceptionsSchemaGenerator();
        $result = $serializer->getExceptionSchemaFromControllerClassAndMethod(
            MockController::class.'::commandHandler'
        );
        $result = json_decode($result, true);
        if (false === $result) {
            throw new \Exception('Could not decode JSON');
        }

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

    /**
     * @throws \Exception
     */
    public function testQueryHandler(): void
    {
        $serializer = new ControllerExceptionsSchemaGenerator();
        $result = $serializer->getExceptionSchemaFromControllerClassAndMethod(
            MockController::class.'::queryHandler'
        );
        $result = json_decode($result, true);

        if (false === $result) {
            throw new \Exception('Could not decode JSON');
        }

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
        $this->expectExceptionMessage('Cannot find line which might have a handler service class in Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockController::noHandler');
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSchemaGenerator();
        $serializer->getExceptionSchemaFromControllerClassAndMethod(
            MockController::class.'::noHandler'
        );
    }

    public function testCouldNotBuildReflectionMethodForControllerClassAndMethod(): void
    {
        $this->expectExceptionMessage('Could not build reflection method for Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockController::methodDoesNotExist');
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSchemaGenerator();
        $serializer->getExceptionSchemaFromControllerClassAndMethod(
            MockController::class.'::methodDoesNotExist'
        );
    }

    public function testCannotFindAnnotationForHandlerMethod(): void
    {
        $this->expectExceptionMessage('Cannot find annotation for handle method in service of class Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockCommandHandlerWithoutAnnotation');
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSchemaGenerator();
        $serializer->getExceptionSchemaFromControllerClassAndMethod(
            MockController::class.'::commandHandlerWithoutAnnotation'
        );
    }

    public function testAllHandlerExceptionClassesMustImplementTheBaseException(): void
    {
        $this->expectExceptionMessage('All handler exception classes must implement the base exception - Failed for Tests\Galeas\Api\UnitAndIntegration\JsonSchema\MockController::commandHandlerWithNonBaseException');
        $this->expectException(ExceptionSerializerFailed::class);
        $serializer = new ControllerExceptionsSchemaGenerator();
        $serializer->getExceptionSchemaFromControllerClassAndMethod(
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
     * @throws EventStoreCannotWrite|ProjectionCannotRead
     * @throws EventStoreCannotRead
     */
    public function handle(MockCommand $mockCommand): void
    {
        $random = random_int(1, $mockCommand->maxInt);

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
                throw new EventStoreCannotRead(new \RuntimeException());

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
    public function handle(MockCommand $mockCommand): void {}
}

class MockCommandHandlerWithNonBaseException
{
    /**
     * @throws \RuntimeException
     */
    public function handle(MockCommand $mockCommand): void
    {
        throw new \RuntimeException((string) $mockCommand->maxInt);
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
