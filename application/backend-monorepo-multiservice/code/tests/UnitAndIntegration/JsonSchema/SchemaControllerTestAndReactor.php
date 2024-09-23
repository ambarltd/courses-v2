<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\ControllerIntegrationTest;

class SchemaControllerTestAndReactor extends ControllerIntegrationTest
{
    public function testSchemaList(): void
    {
        $response = $this->requestGet('/schema/list', []);
        Assert::assertEquals(200, $response['statusCode']);
        $jsonToArrayContent = json_decode($response['content']);

        foreach ($jsonToArrayContent as $routeData) {
            $requestSchemaPath = str_replace('http://localhost', '', $routeData->schema->request);
            $pathAndParameterString = explode('?', $requestSchemaPath);
            $response = $this->requestGet(
                $pathAndParameterString[0],
                [
                    'path' => str_replace('path=', '', $pathAndParameterString[1]),
                ]
            );
            Assert::assertEquals(
                200,
                $response['statusCode'],
                \sprintf(
                    'Could not find request schema %s with contents: %s',
                    $requestSchemaPath,
                    $response['contents']
                )
            );

            $responseSchemaPath = str_replace('http://localhost', '', $routeData->schema->response);
            $pathAndParameterString = explode('?', $responseSchemaPath);
            $response = $this->requestGet(
                $pathAndParameterString[0],
                [
                    'path' => str_replace('path=', '', $pathAndParameterString[1]),
                ]
            );
            Assert::assertEquals(
                200,
                $response['statusCode'],
                \sprintf(
                    'Could not find request schema %s with contents: %s',
                    $responseSchemaPath,
                    $response['contents']
                )
            );

            $errorSchemaPath = str_replace('http://localhost', '', $routeData->schema->error);
            $pathAndParameterString = explode('?', $errorSchemaPath);
            $response = $this->requestGet(
                $pathAndParameterString[0],
                [
                    'path' => str_replace('path=', '', $pathAndParameterString[1]),
                ]
            );
            if (200 !== $response['statusCode']) {
                var_dump($response);

                exit;
            }
            Assert::assertEquals(
                200,
                $response['statusCode'],
                \sprintf(
                    'Could not find request schema %s with contents: %s',
                    $errorSchemaPath,
                    $response['contents']
                )
            );
        }
    }
}
