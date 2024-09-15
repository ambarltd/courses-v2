<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema;

use JsonSchema\Validator;

class JsonSchemaValidator
{
    private Validator $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return array (list of errors)
     */
    public function validate(string $json, string $jsonSchema): array
    {
        $this->validator->reset();
        $jsonObject = json_decode($json);
        $jsonSchemaObject = json_decode($jsonSchema);
        // parameters are passed by reference, hence the extra variables
        $this->validator->validate($jsonObject, $jsonSchemaObject);

        $returnedErrors = [];
        if (!$this->validator->isValid()) {
            foreach ($this->validator->getErrors() as $error) {
                $returnedError = sprintf(
                    '[%s] %s',
                    $error['property'],
                    $error['message']
                );
                $returnedErrors[] = $returnedError;
            }
        }

        return $returnedErrors;
    }
}
