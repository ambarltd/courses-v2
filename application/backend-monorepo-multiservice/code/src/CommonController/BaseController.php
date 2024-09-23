<?php

declare(strict_types=1);

namespace Galeas\Api\CommonController;

use Galeas\Api\CommonException\BaseException;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use Galeas\Api\JsonSchema\JsonSchemaValidator;
use Galeas\Api\Service\Logger\PhpOutLogger;
use Galeas\Api\Service\RequestMapper\JsonPostRequestMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    /**
     * @var object[]
     */
    protected array $services = [];

    private JsonPostRequestMapper $jsonPostRequestMapper;

    private JsonSchemaFetcher $jsonSchemaFetcher;

    private JsonSchemaValidator $jsonSchemaValidator;

    private PhpOutLogger $phpOutLogger;

    private bool $shouldValidateResponseSchemas = false;

    /**
     * @param array<object> $services
     */
    public function __construct(
        array $services
    ) {
        foreach ($services as $service) {
            $this->services[$service::class] = $service;
        }
    }

    public function setJsonPostRequestMapper(JsonPostRequestMapper $jsonPostRequestMapper): void
    {
        $this->jsonPostRequestMapper = $jsonPostRequestMapper;
    }

    public function setJsonSchemaFetcher(JsonSchemaFetcher $jsonSchemaFetcher): void
    {
        $this->jsonSchemaFetcher = $jsonSchemaFetcher;
    }

    public function setJsonSchemaValidator(JsonSchemaValidator $jsonSchemaValidator): void
    {
        $this->jsonSchemaValidator = $jsonSchemaValidator;
    }

    public function setPhpOutLogger(PhpOutLogger $phpOutLogger): void
    {
        $this->phpOutLogger = $phpOutLogger;
    }

    public function jsonPostRequestJsonResponse(
        Request $request,
        string $requestSchema,
        string $responseSchema,
        string $commandOrQueryClass,
        object $commandOrQueryHandler,
        ?callable $commandOrQueryModifier,
        int $successStatusCode
    ): JsonResponse {
        try {
            $json = $this->jsonPostRequestMapper
                ->jsonBodyFromRequest($request)
            ;
            $requestSchema = $this->jsonSchemaFetcher->fetch($requestSchema);

            $errors = $this->jsonSchemaValidator
                ->validate(
                    $json,
                    $requestSchema
                )
            ;

            if (!empty($errors)) {
                $this->phpOutLogger->warning('json_schema_validation_error');

                $response = json_encode([
                    'errors' => $errors,
                    'errorIdentifier' => 'json_schema_validation_error',
                    'errorMessage' => 'Json Schema Validation Error',
                ]);
                if (false === $response) {
                    throw new \RuntimeException('error json_encode failed');
                }

                return JsonResponse::fromJsonString(
                    $response,
                    Response::HTTP_BAD_REQUEST
                );
            }

            $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest($request, $commandOrQueryClass);

            if (\is_callable($commandOrQueryModifier)) {
                $commandOrQueryModifier($command);
            }

            if (!method_exists($commandOrQueryHandler, 'handle')) {
                throw new \RuntimeException('Handler must have handle method');
            }
            $response = $commandOrQueryHandler->handle($command);
            $jsonResponse = JsonResponse::fromJsonString(json_encode($response), $successStatusCode);

            $responseSchema = $this->jsonSchemaFetcher->fetch($responseSchema);

            $responseContent = $jsonResponse->getContent();

            if ($this->shouldValidateResponseSchemas) {
                $errors = $this->jsonSchemaValidator
                    ->validate(
                        \is_string($responseContent) ? $responseContent : '',
                        $responseSchema
                    )
                ;
            }

            if (!empty($errors)) {
                $this->phpOutLogger->warning('invalid_response_against_json_schema');

                $response = json_encode([
                    'errors' => $errors,
                    'errorIdentifier' => 'invalid_response_against_json_schema',
                    'errorMessage' => 'Invalid Response Against Json Schema',
                ]);
                if (false === $response) {
                    throw new \RuntimeException('error json_encode failed');
                }

                return JsonResponse::fromJsonString(
                    $response,
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return $jsonResponse;
        } catch (BaseException $exception) {
            $errorMessage = substr($exception->getMessage(), 0, 8_192);
            $stackTrace = substr($exception->getTraceAsString(), 0, 8_192);
            $this->phpOutLogger->warning(\sprintf(
                'BaseException caught, classFQN %s, message: %s, stack trace: %s',
                $exception::class,
                $errorMessage,
                $stackTrace
            ));

            $response = json_encode([
                'errors' => [],
                'errorIdentifier' => $exception::getErrorIdentifier(),
                'errorMessage' => '',
            ]);
            if (false === $response) {
                throw new \RuntimeException('error json_encode failed');
            }

            return JsonResponse::fromJsonString(
                $response,
                $exception::getHttpCode()
            );
        } catch (\Throwable $throwable) {
            $errorMessage = substr($throwable->getMessage(), 0, 8_192);
            $stackTrace = substr($throwable->getTraceAsString(), 0, 8_192);
            $this->phpOutLogger->warning(\sprintf(
                'Throwable caught, classFQN %s, message: %s, stack trace: %s',
                $throwable::class,
                $errorMessage,
                $stackTrace
            ));

            $response = json_encode([
                'errors' => [],
                'errorIdentifier' => 'internal_server_error',
                'errorMessage' => $errorMessage,
            ]);
            if (false === $response) {
                throw new \RuntimeException('error json_encode failed');
            }

            return JsonResponse::fromJsonString(
                $response,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * todo deal with stdclass and throw without breaking the build.
     */
    protected function getService(string $serviceKey): object
    {
        foreach ($this->services as $key => $service) {
            if ($serviceKey === $key && \is_object($service)) {
                return $service;
            }
        }

        return new \stdClass();
    }
}
