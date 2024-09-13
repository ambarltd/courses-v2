<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Controller;

use Galeas\Api\Common\ExceptionBase\BaseException;
use Galeas\Api\Common\ExceptionBase\InternalServerErrorException;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use Galeas\Api\JsonSchema\JsonSchemaValidator;
use Galeas\Api\Service\RequestMapper\JsonPostRequestMapper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    /**
     * @var string
     */
    private $environment;

    /**
     * @var object[]
     */
    protected $services = [];

    /**
     * @var JsonPostRequestMapper
     */
    private $jsonPostRequestMapper;

    /**
     * @var JsonSchemaFetcher
     */
    private $jsonSchemaFetcher;

    /**
     * @var JsonSchemaValidator
     */
    private $jsonSchemaValidator;

    public function __construct(
        string $environment,
        array $services
    ) {
        $this->environment = $environment;
        foreach ($services as $service) {
            $this->services[get_class($service)] = $service;
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

    /**
     * todo deal with stdclass and throw without breaking the build.
     */
    protected function getService(string $serviceKey): object
    {
        foreach ($this->services as $key => $service) {
            if ($serviceKey === $key && is_object($service)) {
                return $service;
            }
        }

        return new \stdClass();
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
                ->jsonBodyFromRequest($request);

            $requestSchema = $this->jsonSchemaFetcher->fetch($requestSchema);

            $errors = $this->jsonSchemaValidator
                ->validate(
                    $json,
                    $requestSchema
                );

            if (!empty($errors)) {
                return JsonResponse::fromJsonString(
                    json_encode([
                        'errors' => $errors,
                        'errorIdentifier' => 'json_schema_validation_error',
                        'errorMessage' => 'Json Schema Validation Error',
                    ]),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest($request, $commandOrQueryClass);

            if (is_callable($commandOrQueryModifier)) {
                $commandOrQueryModifier($command);
            }

            if (!method_exists($commandOrQueryHandler, 'handle')) {
                throw new \RuntimeException('Handler must have handle method');
            }
            $response = $commandOrQueryHandler->handle($command);
            $jsonResponse = JsonResponse::fromJsonString(json_encode($response), $successStatusCode);

            $responseSchema = $this->jsonSchemaFetcher->fetch($responseSchema);

            $responseContent = $jsonResponse->getContent();

            if ($this->environmentShouldValidateResponseSchemas()) {
                $errors = $this->jsonSchemaValidator
                    ->validate(
                        is_string($responseContent) ? $responseContent : '',
                        $responseSchema
                    );
            }

            if (!empty($errors)) {
                return JsonResponse::fromJsonString(
                    json_encode([
                        'errors' => $errors,
                        'errorIdentifier' => 'invalid_response_against_json_schema',
                        'errorMessage' => 'Invalid Response Against Json Schema',
                    ]),
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            return $jsonResponse;
        } catch (BaseException $exception) {
            $errorMessage = substr($exception->getMessage(), 0, 8192);

            if (
                $exception instanceof InternalServerErrorException &&
                false === $this->environmentShouldShowStackTraces()
            ) {
                $errorMessage = '';
            }

            return JsonResponse::fromJsonString(
                json_encode([
                    'errors' => [],
                    'errorIdentifier' => $exception::getErrorIdentifier(),
                    'errorMessage' => $errorMessage,
                ]),
                $exception::getHttpCode()
            );
        } catch (\Throwable $throwable) {
            $errorMessage = substr($throwable->getMessage(), 0, 8192);

            if (false === $this->environmentShouldShowStackTraces()) {
                $errorMessage = '';
            }

            return JsonResponse::fromJsonString(
                json_encode([
                    'errors' => [],
                    'errorIdentifier' => 'internal_server_error',
                    'errorMessage' => $errorMessage,
                ]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function environmentShouldShowStackTraces(): bool
    {
        return
            'production' === $this->environment
        ;
    }

    private function environmentShouldValidateResponseSchemas(): bool
    {
        return
            'production' === $this->environment
        ;
    }
}
