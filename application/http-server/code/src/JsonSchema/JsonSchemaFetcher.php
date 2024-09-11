<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema;

class JsonSchemaFetcher
{
    /**
     * @throws CouldNotFetchJsonSchema
     */
    public function fetch(string $schemaName): string
    {
        $filePath = sprintf('file://%s/Schemas/%s', __DIR__, $schemaName);

        $decodedJson = $this->decodedJsonFromFilePath($filePath);

        if (is_object($decodedJson)) {
            $resolvedJson = $this->resolveDecodedJsonRefs($decodedJson);
        } else {
            $resolvedJson = $decodedJson;
        }

        $encoded = json_encode($resolvedJson);

        if (is_string($encoded)) {
            return $encoded;
        }

        throw new CouldNotFetchJsonSchema('Encoding Error');
    }

    /**
     * @param object $jsonObject
     *
     * @return object|array|mixed
     *
     * @throws CouldNotFetchJsonSchema
     */
    private function resolveDecodedJsonRefs($jsonObject)
    {
        $properties = array_keys(get_object_vars($jsonObject));
        foreach ($properties as $property) {
            $value = $jsonObject->{$property};
            if ('$ref' === $property && is_string($value)) {
                $decodedJson = $this->decodedJsonFromFilePath($value);
                if (is_object($decodedJson)) {
                    return $this->resolveDecodedJsonRefs($decodedJson);
                } else {
                    return $decodedJson;
                }
            }

            if (is_object($value)) {
                $newValue = $this->resolveDecodedJsonRefs($value);
                $jsonObject->{$property} = $newValue;
            }
        }

        return $jsonObject;
    }

    /**
     * @param string $filePath
     *
     * @return object|array|mixed
     *
     * @throws CouldNotFetchJsonSchema
     */
    private function decodedJsonFromFilePath($filePath)
    {
        try {
            $startsWith = substr($filePath, 0, 7);
            $pathIsRelative = 'file://' !== $startsWith;

            if ($pathIsRelative) {
                $filePath = sprintf('file://%s/Schemas/%s', __DIR__, $filePath);
            }

            $json = file_get_contents($filePath);

            if (is_string($json)) {
                return json_decode($json);
            } else {
                throw new \RuntimeException();
            }
        } catch (\Throwable $exception) { // extra catch for file_get_contents errors
            throw new CouldNotFetchJsonSchema('Decoding error for file path '.$filePath);
        }
    }
}
