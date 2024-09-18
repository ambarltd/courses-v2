<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema\Controller;

use Galeas\Api\JsonSchema\AnnotationReaderFailed;
use Galeas\Api\JsonSchema\ControllerExceptionsSerializer;
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

    private ControllerExceptionsSerializer $controllerExceptionsSerializer;

    public function __construct(
        string $kernelEnvironment,
        JsonSchemaFetcher $jsonSchemaFetcher,
        RouterInterface $router,
        SchemaAnnotationReader $schemaAnnotationReader,
        ControllerExceptionsSerializer $controllerExceptionsSerializer
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
            json_encode(new \stdClass()),
            Response::HTTP_OK
        );
        $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

        return $response;
    }

    #[Route('/schema/list', name: 'schema_list', methods: ['GET'])]
    public function schemaList(Request $request): Response
    {
        $betterRoutes = array_values(
            $this->allNonSchemaRoutesExceptRoot($request->getSchemeAndHttpHost())
        );

        $response = JsonResponse::fromJsonString(
            json_encode($betterRoutes),
            Response::HTTP_OK
        );
        $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

        return $response;
    }

    #[Route('/schema/request', name: 'schema_request', methods: ['GET'])]
    public function schemaRequest(Request $request): Response
    {
        try {
            $path = $request->query->get('path');
            $schemaName = $this->getRequestSchemaFromRoutePath(is_string($path) ? $path : '');

            if (null === $schemaName) {
                return JsonResponse::fromJsonString(
                    json_encode(),
                    Response::HTTP_NOT_FOUND
                );
            }

            $schema = $this->jsonSchemaFetcher->fetch('Request/'.$schemaName.'.json');

            $response = JsonResponse::fromJsonString(
                $schema,
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            return JsonResponse::fromJsonString(
                json_encode([
                    'error' => 'Server Error',
                    'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                    'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
                ]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/schema/response', name: 'schema_response', methods: ['GET'])]
    public function schemaResponse(Request $request): Response
    {
        try {
            $path = $request->query->get('path');
            $schemaName = $this->getResponseSchemaNameFromRoutePath(is_string($path) ? $path : '');

            if (null === $schemaName) {
                return JsonResponse::fromJsonString(
                    json_encode(['error' => 'Route not found for path '.$path]),
                    Response::HTTP_NOT_FOUND
                );
            }

            $schema = $this->jsonSchemaFetcher->fetch('Response/'.$schemaName.'.json');

            $response = JsonResponse::fromJsonString(
                $schema,
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            return JsonResponse::fromJsonString(
                json_encode([
                    'error' => 'Server Error',
                    'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                    'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
                ]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/schema/error', name: 'schema_error', methods: ['GET'])]
    public function schemaError(Request $request): Response
    {
        try {
            $path = $request->query->get('path');
            $errorSchema = $this->getExceptionSchemaFromRoutePath(is_string($path) ? $path : '');

            if (null === $errorSchema) {
                return JsonResponse::fromJsonString(
                    json_encode(['error' => 'Route or schema not found']),
                    Response::HTTP_NOT_FOUND
                );
            }

            $response = JsonResponse::fromJsonString(
                json_encode($errorSchema),
                Response::HTTP_OK
            );
            $response->setEncodingOptions(JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);

            return $response;
        } catch (\Throwable $throwable) {
            return JsonResponse::fromJsonString(
                json_encode([
                    'error' => 'Server Error',
                    'message' => $this->environmentShouldShowStackTraces() ? $throwable->getMessage() : null,
                    'stackTrace' => $this->environmentShouldShowStackTraces() ? $throwable->getTraceAsString() : null,
                ]),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function allNonSchemaRoutesExceptRoot(string $schemeAndHttpHost): array
    {
        $routes = $this->router->getRouteCollection()->all();

        $betterRoutes = array_map(
            function (RouteNotAnnotation $route) use ($schemeAndHttpHost): array {
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
            function (array $route) use ($schemeAndHttpHost): bool {
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
            function (array $routeA, array $routeB): bool {
                return $routeA['path'] > $routeB['path'];
            }
        );

        return $betterRoutes;
    }

    /**
     * @throws AnnotationReaderFailed
     */
    private function getRequestSchemaFromRoutePath(string $routePath): ?string
    {
        $routes = $this->router->getRouteCollection()->all();

        foreach ($routes as $routeName => $routeObject) {
            if ($routeObject->getPath() === $routePath) {
                $controllerAndMethod = $routeObject->getDefault('_controller');

                return $this->schemaAnnotationReader->getRequestSchema($controllerAndMethod);
            }
        }

        return null;
    }

    /**
     * @throws AnnotationReaderFailed
     */
    private function getResponseSchemaNameFromRoutePath(string $routePath): ?string
    {
        $routes = $this->router->getRouteCollection()->all();

        foreach ($routes as $routeName => $routeObject) {
            if ($routeObject->getPath() === $routePath) {
                $controllerAndMethod = $routeObject->getDefault('_controller');

                return $this->schemaAnnotationReader->getResponseSchema($controllerAndMethod);
            }
        }

        return null;
    }

    /**
     * @throws ExceptionSerializerFailed
     */
    private function getExceptionSchemaFromRoutePath(string $routePath): ?array
    {
        $routes = $this->router->getRouteCollection()->all();

        foreach ($routes as $routeName => $routeObject) {
            if ($routeObject->getPath() === $routePath) {
                $controllerAndMethod = $routeObject->getDefault('_controller');

                return $this->controllerExceptionsSerializer->getSerializedExceptionsFromControllerClassAndMethod($controllerAndMethod);
            }
        }

        return null;
    }

    private function environmentShouldShowStackTraces(): bool
    {
        return 'test' === $this->kernelEnvironment;
    }
}
