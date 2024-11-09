<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use Galeas\Api\JsonSchema\CouldNotFetchJsonSchema;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTest;

class JsonSchemaFetcherTest extends UnitTest
{
    public function testFetch(): void
    {
        $jsonSchemaFetcher = new JsonSchemaFetcher();
        $retrievedJsonSchema = $jsonSchemaFetcher->fetch('Request/V1_Identity_User_RequestPrimaryEmailChange.json');

        $expectedJsonSchema = '
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "type": "object",
                "additionalProperties": false,
                "properties": {
                    "password": {
                        "type": "string",
                        "minLength": 1,
                        "maxLength": 1024
                    },
                    "newEmailRequested": {
                        "type": "string",
                        "minLength": 3,
                        "maxLength": 320
                    },
                    "metadata": {
                        "properties": {
                            "environment": {
                                "type": "string",
                                "enum": [
                                    "native",
                                    "browser",
                                    "other",
                                    "unknown"
                                ]
                            },
                            "devicePlatform": {
                                "type": "string",
                                "enum": [
                                    "ios",
                                    "android",
                                    "mac",
                                    "windows",
                                    "linux",
                                    "other",
                                    "unknown"
                                ]
                            },
                            "deviceModel": {
                                "type": "string",
                                "minLength": 1,
                                "maxLength": 128
                            },
                            "deviceOSVersion": {
                                "type": "string",
                                "minLength": 1,
                                "maxLength": 128
                            },
                            "deviceOrientation": {
                                "type": "string",
                                "enum": [
                                    "portrait",
                                    "landscape",
                                    "does_not_apply",
                                    "other",
                                    "unknown"
                                ]
                            },
                            "referer": {
                                "type": [
                                    "string",
                                    "null"
                                ],
                                "minLength": 1,
                                "maxLength": 4096,
                                "description": "Overrides the \'Referer\' http header."
                            },
                            "userAgent": {
                                "type": [
                                    "string",
                                    "null"
                                ],
                                "minLength": 1,
                                "maxLength": 4096,
                                "description": "Overrides the \'User-Agent\' http header."
                            },
                            "latitude": {
                                "type": [
                                    "number",
                                    "null"
                                ],
                                "multipleOf": 1.0e-10,
                                "minimum": -90,
                                "maximum": 90,
                                "exclusiveMinimum": false,
                                "exclusiveMaximum": false
                            },
                            "longitude": {
                                "type": [
                                    "number",
                                    "null"
                                ],
                                "multipleOf": 1.0e-10,
                                "minimum": -180,
                                "maximum": 180,
                                "exclusiveMinimum": false,
                                "exclusiveMaximum": false
                            }
                        },
                        "type": "object",
                        "required": [
                            "environment",
                            "devicePlatform",
                            "deviceModel",
                            "deviceOSVersion",
                            "deviceOrientation"
                        ]
                    },
                    "withSessionToken": {
                        "type": ["string", "null"],
                        "minLength": 96,
                        "maxLength": 96,
                        "description": "The session token is expected in the header X-With-Session-Token. This field overrides the \'X-With-Session-Token\' http header if non-null."
                    }
                },
                "required": [
                    "password",
                    "newEmailRequested",
                    "metadata"
                ]
            }
        ';
        $expectedJsonSchema = json_encode(json_decode($expectedJsonSchema));

        Assert::assertEquals(
            $retrievedJsonSchema,
            $expectedJsonSchema
        );
    }

    /**
     * @throws \Exception
     */
    public function testCannotFetch(): void
    {
        $jsonSchemaFetcher = new JsonSchemaFetcher();

        $schemaNames = [
            'Request/V1_This_Does_Not_exist.json',
            'V1_This_Does_Not_exist.json',
            'b.json',
        ];

        foreach ($schemaNames as $schemaName) {
            try {
                $jsonSchemaFetcher->fetch($schemaName);
            } catch (\Exception $e) {
                Assert::assertInstanceOf(
                    CouldNotFetchJsonSchema::class,
                    $e,
                );

                continue;
            }

            throw new \Exception('Should not reach this point');
        }
    }
}
