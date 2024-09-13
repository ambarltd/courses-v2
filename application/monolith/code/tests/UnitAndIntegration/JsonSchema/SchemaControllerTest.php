<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HttpWithKernelTestBase;

class SchemaControllerTest extends HttpWithKernelTestBase
{
    /**
     * @test
     */
    public function testSchemaList(): void
    {
        $response = $this->getApiUrl('/schema/list');

        Assert::assertEquals(200, $response->getStatusCode(), 'Could not get /schema/list');

        $content = $response->getBody()->getContents();

        $jsonToArrayContent = json_decode($content);

        foreach ($jsonToArrayContent as $routeData) {
            $requestSchema = $routeData->schema->request;
            Assert::assertEquals(
                200,
                $this->getUrl($requestSchema)->getStatusCode(),
                'Could not find request schema '.$requestSchema);

            $responseSchema = $routeData->schema->response;
            Assert::assertEquals(
                200,
                $this->getUrl($responseSchema)->getStatusCode(),
                'Could not find response schema '.$responseSchema);

            $errorSchema = $routeData->schema->error;

            Assert::assertEquals(
                200,
                $this->getUrl($errorSchema)->getStatusCode(),
                'Could not find error schema '.$errorSchema);
        }
    }
}
