<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema\Controller;

use Galeas\Api\JsonSchema\AnnotationReaderFailed;
use Galeas\Api\JsonSchema\ControllerExceptionsSchemaGenerator;
use Galeas\Api\JsonSchema\CouldNotFindControllerAndMethod;
use Galeas\Api\JsonSchema\ExceptionSerializerFailed;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use Galeas\Api\JsonSchema\SchemaAnnotationReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Route as RouteNotAnnotation;
use Symfony\Component\Routing\RouterInterface;

class SchemaController extends AbstractController
{
    private string $kernelEnvironment;
    private JsonSchemaFetcher $jsonSchemaFetcher;

    private RouterInterface $router;

    private SchemaAnnotationReader $schemaAnnotationReader;

    private ControllerExceptionsSchemaGenerator $controllerExceptionsSerializer;

    public function __construct(
        string $kernelEnvironment,
        JsonSchemaFetcher $jsonSchemaFetcher,
        RouterInterface $router,
        SchemaAnnotationReader $schemaAnnotationReader,
        ControllerExceptionsSchemaGenerator $controllerExceptionsSerializer
    ) {
        $this->kernelEnvironment = $kernelEnvironment;
        $this->jsonSchemaFetcher = $jsonSchemaFetcher;
        $this->router = $router;
        $this->schemaAnnotationReader = $schemaAnnotationReader;
        $this->controllerExceptionsSerializer = $controllerExceptionsSerializer;
    }

    #[Route('/', name: 'root', methods: ['GET'])]
    public function root(Request $request): Response
    {
        $response = JsonResponse::fromJsonString(
            '{}',
            Response::HTTP_OK
        );
        $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

        return $response;
    }

    #[Route('/schema/list', name: 'schema_list', methods: ['GET'])]
    public function schemaList(Request $request): Response
    {
        try {
            $betterRoutes = array_values(
                $this->allNonSchemaRoutesExceptRoot($request->getSchemeAndHttpHost())
            );

            $encodedResponse = json_encode($betterRoutes);
            if (false === $encodedResponse) {
                throw new \Exception('Could not encode routes');
            }
            $response = JsonResponse::fromJsonString(
                $encodedResponse,
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            $response = json_encode([
                'error' => 'Server Error',
                'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
            ]);
            if (false === $response) {
                $response = '{"error": "Server Error"}, "message": "", "stackTrace": ""}';
            }

            return JsonResponse::fromJsonString(
                $response,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/schema/request', name: 'schema_request', methods: ['GET'])]
    public function schemaRequest(Request $request): Response
    {
        try {
            $path = $request->query->get('path');
            $schemaName = $this->getRequestSchemaFromRoutePath(\is_string($path) ? $path : '');

            if (null === $schemaName) {
                throw new \Exception('Could not get request schema from route path');
            }

            $schema = $this->jsonSchemaFetcher->fetch('Request/'.$schemaName.'.json');

            $response = JsonResponse::fromJsonString(
                $schema,
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            $response = json_encode([
                'error' => 'Server Error',
                'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
            ]);
            if (false === $response) {
                $response = '{"error": "Server Error"}, "message": "", "stackTrace": ""}';
            }

            return JsonResponse::fromJsonString(
                $response,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/schema/response', name: 'schema_response', methods: ['GET'])]
    public function schemaResponse(Request $request): Response
    {
        try {
            $path = $request->query->get('path');
            $schemaName = $this->getResponseSchemaFromRoutePath(\is_string($path) ? $path : '');

            if (null === $schemaName) {
                throw new \Exception('Could not get response schema name from route path');
            }

            $schema = $this->jsonSchemaFetcher->fetch('Response/'.$schemaName.'.json');

            $response = JsonResponse::fromJsonString(
                $schema,
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            $response = json_encode([
                'error' => 'Server Error',
                'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
            ]);
            if (false === $response) {
                $response = '{"error": "Server Error"}, "message": "", "stackTrace": ""}';
            }

            return JsonResponse::fromJsonString(
                $response,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/schema/error', name: 'schema_error', methods: ['GET'])]
    public function schemaError(Request $request): Response
    {
        try {
            $path = $request->query->get('path');
            $errorSchema = $this->getExceptionSchemaFromRoutePath(\is_string($path) ? $path : '');

            if (null === $errorSchema) {
                throw new \Exception('Could not get error schema from route path');
            }

            $response = JsonResponse::fromJsonString(
                $errorSchema,
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            $response = json_encode([
                'error' => 'Server Error',
                'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
            ]);
            if (false === $response) {
                $response = '{"error": "Server Error"}, "message": "", "stackTrace": ""}';
            }

            return JsonResponse::fromJsonString(
                $response,
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * @return array<int,array<string, array<string>|string>>
     */
    private function allNonSchemaRoutesExceptRoot(string $schemeAndHttpHost): array
    {
        $routes = $this->router->getRouteCollection()->all();

        $betterRoutes = array_map(
            static function (RouteNotAnnotation $route) use ($schemeAndHttpHost): array {
                return [
                    'path' => $schemeAndHttpHost.$route->getPath(),
                    'methods' => $route->getMethods(),
                    'schema' => [
                        'request' => $schemeAndHttpHost.'/schema/request?path='.$route->getPath(),
                        'response' => $schemeAndHttpHost.'/schema/response?path='.$route->getPath(),
                        'error' => $schemeAndHttpHost.'/schema/error?path='.$route->getPath(),
                    ],
                ];
            },
            $routes
        );

        $betterRoutes = array_filter(
            $betterRoutes,
            static function (array $route) use ($schemeAndHttpHost): bool {
                $notThesePaths = [
                    $schemeAndHttpHost.'/',
                    $schemeAndHttpHost.'/schema/list',
                    $schemeAndHttpHost.'/schema/request',
                    $schemeAndHttpHost.'/schema/response',
                    $schemeAndHttpHost.'/schema/error',
                    $schemeAndHttpHost.'/api/.*/projection/.*',
                    $schemeAndHttpHost.'/api/.*/reaction/.*',
                ];

                foreach ($notThesePaths as $notThisPath) {
                    // Check if the route path matches the regex pattern
                    // We use # as our escape character to avoid forward slashes being
                    // interpreted as a regex escape character.
                    if (preg_match('#^'.str_replace('/', '\/', $notThisPath).'$#', $route['path'])) {
                        return false;
                    }
                }

                return true;
            }
        );

        usort(
            $betterRoutes,
            static fn (array $routeA, array $routeB): int => $routeA['path'] > $routeB['path'] ? 1 : -1
        );

        return $betterRoutes;
    }

    /**
     * @throws AnnotationReaderFailed|CouldNotFindControllerAndMethod
     */
    private function getRequestSchemaFromRoutePath(string $routePath): ?string
    {
        $routes = $this->router->getRouteCollection()->all();

        foreach ($routes as $routeName => $routeObject) {
            if ($routeObject->getPath() === $routePath) {
                $controllerAndMethod = $routeObject->getDefault('_controller');

                if (!\is_string($controllerAndMethod)) {
                    throw new CouldNotFindControllerAndMethod();
                }

                return $this->schemaAnnotationReader->getRequestSchema($controllerAndMethod);
            }
        }

        return null;
    }

    /**
     * @throws AnnotationReaderFailed|CouldNotFindControllerAndMethod
     */
    private function getResponseSchemaFromRoutePath(string $routePath): ?string
    {
        $routes = $this->router->getRouteCollection()->all();

        foreach ($routes as $routeName => $routeObject) {
            if ($routeObject->getPath() === $routePath) {
                $controllerAndMethod = $routeObject->getDefault('_controller');

                if (!\is_string($controllerAndMethod)) {
                    throw new CouldNotFindControllerAndMethod();
                }

                return $this->schemaAnnotationReader->getResponseSchema($controllerAndMethod);
            }
        }

        return null;
    }

    /**
     * @throws CouldNotFindControllerAndMethod|\Exception|ExceptionSerializerFailed
     */
    private function getExceptionSchemaFromRoutePath(string $routePath): ?string
    {
        $routes = $this->router->getRouteCollection()->all();

        foreach ($routes as $routeName => $routeObject) {
            if ($routeObject->getPath() === $routePath) {
                $controllerAndMethod = $routeObject->getDefault('_controller');

                if (!\is_string($controllerAndMethod)) {
                    throw new CouldNotFindControllerAndMethod();
                }

                return $this->controllerExceptionsSerializer->getExceptionSchemaFromControllerClassAndMethod($controllerAndMethod);
            }
        }

        return null;
    }

    private function environmentShouldShowStackTraces(): bool
    {
        return 'test' === $this->kernelEnvironment;
    }
}
