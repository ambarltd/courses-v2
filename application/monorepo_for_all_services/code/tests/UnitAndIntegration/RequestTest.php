<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Symfony\Component\HttpFoundation\Request;

abstract class RequestTest extends KernelTestBase
{
    protected function requestGet(string $url, array $parameters): array {
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
            "content" => $response->getContent(),
            "statusCode" => $response->getStatusCode(),
        ];
    }
}
