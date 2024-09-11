<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema;

class SchemaAnnotationReader
{
    /**
     * @throws AnnotationReaderFailed
     */
    public function getRequestSchema(string $controllerClassAndMethod): string
    {
        return $this->getNamePropertyValueOfAnnotation($controllerClassAndMethod, 'RequestSchema');
    }

    /**
     * @throws AnnotationReaderFailed
     */
    public function getResponseSchema(string $controllerClassAndMethod): string
    {
        return $this->getNamePropertyValueOfAnnotation($controllerClassAndMethod, 'ResponseSchema');
    }

    /**
     * @param string $controllerClassAndMethod E.g. \Namespace\Class::method
     * @param string $schemaType               E.g. RequestSchema, ResponseSchema
     *
     * @return string the property value of the annotation
     *
     * @throws AnnotationReaderFailed
     */
    private function getNamePropertyValueOfAnnotation(string $controllerClassAndMethod, string $schemaType): string
    {
        try {
            $reflectionMethod = new \ReflectionMethod($controllerClassAndMethod);
            $annotation = $reflectionMethod->getDocComment();
            if (!is_string($annotation)) {
                // The constructor for \ReflectionMethod is not interpreted as throwing \ReflectionException
                // To avoid static code analyzers from misinterpreting this, the exception is also thrown
                // when there is no annotation.
                throw new \ReflectionException();
            }
            $annotationLines = explode("\n", $annotation);

            foreach ($annotationLines as $line) {
                if (strpos($line, '@'.$schemaType)) {
                    return $this->extractNamePropertyValue($line);
                }
            }

            throw new AnnotationReaderFailed('No line has @'.$schemaType.' in the docComment of '.$controllerClassAndMethod);
        } catch (\ReflectionException $exception) {
            throw new AnnotationReaderFailed('No docComment found for '.$controllerClassAndMethod.' or '.$controllerClassAndMethod.' is not a valid class and method.');
        }
    }

    /**
     * @throws AnnotationReaderFailed
     */
    private function extractNamePropertyValue(string $documentLine): string
    {
        // Removes the spaces.
        $row = preg_replace('/\s+/', '', $documentLine);

        // Extracts the value of name
        if (
            null !== $row &&
            preg_match('/name=["\']([\w_,-]+)["\']/', $row, $matches)
        ) {
            if (array_key_exists(1, $matches)) {
                return $matches[1];
            }
        }

        throw new AnnotationReaderFailed('Could not find name property value in line: '.$documentLine);
    }
}
