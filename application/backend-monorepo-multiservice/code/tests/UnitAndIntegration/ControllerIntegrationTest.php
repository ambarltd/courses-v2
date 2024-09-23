<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Needed if we ever test controllers depending on commandHandlers & queryHandlers.
// - Replace the SQLEventStore services in services_test.yaml with one that sends
// events to all projectors and reactors after committing. Why? To simulate what
// happens in production, namely that a worker reads from our event store, and
// sends events to projection and reaction endpoints.
// - Clear the event store, projection databases, and reaction databases as part
// of setUp and tearDown.
abstract class ControllerIntegrationTest extends IntegrationTest
{
    protected function requestGet(string $url, array $parameters): array
    {
        $request = Request::create(
            $url,
            'GET',
            $parameters,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            null
        );
        $response = $this->kernelHandleRequest($request);

        return [
            'content' => $response->getContent(),
            'statusCode' => $response->getStatusCode(),
        ];
    }

    private function kernelHandleRequest(Request $request): Response
    {
        return $this->getKernel()->handle($request);
    }
}
