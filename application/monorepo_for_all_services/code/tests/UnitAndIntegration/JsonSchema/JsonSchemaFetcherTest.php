<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use Galeas\Api\JsonSchema\CouldNotFetchJsonSchema;
use Galeas\Api\JsonSchema\JsonSchemaFetcher;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class JsonSchemaFetcherTest extends UnitTestBase
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
                            "withSessionToken": {
                                "type": [
                                    "string",
                                    "null"
                                ],
                                "minLength": 96,
                                "maxLength": 96,
                                "description": "Overrides the \'X-With-Session-Token\' http header."
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
     * @throws \Galeas\Api\JsonSchema\CouldNotFetchJsonSchema
     */
    public function testCannotFetch(): void
    {
        $this->expectException(CouldNotFetchJsonSchema::class);
        $jsonSchemaFetcher = new JsonSchemaFetcher();
        $jsonSchemaFetcher->fetch('Request/V1_This_Does_Not_exist.json');
        $jsonSchemaFetcher->fetch('V1_This_Does_Not_exist.json');
        $jsonSchemaFetcher->fetch('b.json');
    }
}
