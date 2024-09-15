<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\JsonSchema;

use Galeas\Api\JsonSchema\JsonSchemaValidator;
use JsonSchema\Validator;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class JsonSchemaValidatorTest extends UnitTestBase
{
    public function testValid(): void
    {
        $validator = new Validator();
        $jsonSchemaValidator = new JsonSchemaValidator($validator);

        $schema = '
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "anyOf": [
                    {
                        "type": "object",
                        "additionalProperties": false,
                        "properties": {
                            "errors": {
                                "type": "array",
                                "items": {
                                    "type": "string"
                                }
                            },
                            "httpCode": {
                                "type": "integer",
                                "enum": [
                                    400
                                ]
                            },
                            "errorIdentifier": {
                                "type": "string",
                                "enum": [
                                    "errorA",
                                    "errorB"
                                ]
                            },
                            "errorMessage": {
                                "type": "string",
                                "minLength": 1
                            }
                        },
                        "required": [
                            "errors",
                            "httpCode",
                            "errorIdentifier",
                            "errorMessage"
                        ]
                    }
                ]
            }
        ';
        $json = '
            {
                "errors": ["test"],
                "httpCode": 400,
                "errorIdentifier": "errorB",
                "errorMessage": "a"
            }
        ';

        Assert::assertEquals(
            [],
            $jsonSchemaValidator->validate($json, $schema)
        );
    }

    public function testInvalid(): void
    {
        $validator = new Validator();
        $jsonSchemaValidator = new JsonSchemaValidator($validator);

        $schema = '
            {
                "$schema": "http://json-schema.org/draft-04/schema#",
                "anyOf": [
                    {
                        "type": "object",
                        "additionalProperties": false,
                        "properties": {
                            "errors": {
                                "type": "array",
                                "items": {
                                    "type": "string"
                                }
                            },
                            "httpCode": {
                                "type": "integer",
                                "enum": [
                                    400
                                ]
                            },
                            "errorIdentifier": {
                                "type": "string",
                                "enum": [
                                    "errorA",
                                    "errorB"
                                ]
                            },
                            "errorMessage": {
                                "type": "string",
                                "minLength": 1
                            }
                        },
                        "required": [
                            "errors",
                            "httpCode",
                            "errorIdentifier",
                            "errorMessage"
                        ]
                    }
                ]
            }
        ';
        $json = '
            {
                "errors": "test",
                "httpCode": 401,
                "errorIdentifier": "errorC",
                "errorMessage": ""
            }
        ';

        Assert::assertEquals(
            [
                '[errors] String value found, but an array is required',
                '[httpCode] Does not have a value in the enumeration [400]',
                '[errorIdentifier] Does not have a value in the enumeration ["errorA","errorB"]',
                '[errorMessage] Must be at least 1 characters long',
                '[] Failed to match at least one schema',
            ],
            $jsonSchemaValidator->validate($json, $schema)
        );
    }
}
