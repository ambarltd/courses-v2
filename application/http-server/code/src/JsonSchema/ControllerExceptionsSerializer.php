<?php

declare(strict_types=1);

namespace Galeas\Api\JsonSchema;

use Galeas\Api\Common\ExceptionBase\BaseException;

class ControllerExceptionsSerializer
{
    /**
     * @throws ExceptionSerializerFailed
     */
    public function getSerializedExceptionsFromControllerClassAndMethod(string $controllerClassAndMethod): array
    {
        $thrownClasses = $this->retrieveThrownClassesFromControllerClassAndMethod($controllerClassAndMethod);

        $serializedThrownClasses = [];
        foreach ($thrownClasses as $class) {
            if (
                array_key_exists(BaseException::class, class_parents($class)) &&
                is_callable($class.'::getErrorIdentifier') &&
                is_callable($class.'::getHttpCode')
            ) {
                $serializedThrownClasses[$class] = [
                    'className' => $class,
                    'errorCode' => $class::getHttpCode(),
                    'errorIdentifier' => $class::getErrorIdentifier(),
                ];
            } else {
                throw new ExceptionSerializerFailed('All handler exception classes must implement the base exception - Failed for '.$controllerClassAndMethod);
            }
        }

        $errorSchema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'anyOf' => [
                [
                    /*
                     * This handles errors coming from controllers, and request mappers. Not command handlers.
                     * Todo abstract this to a singular error, with useful messages.
                     * Or abstract a way to read which exceptions the controllers and request mappers throw.
                     *
                     * @see JsonPostRequestMapper
                     */
                    'title' => 'Other Errors',
                    'type' => 'object',
                    'additionalProperties' => false,
                    'properties' => [
                        'httpCode' => [
                            'type' => 'integer',
                        ],
                        'payload' => [
                            'type' => 'object',
                            'additionalProperties' => false,
                            'properties' => [
                                'errors' => [
                                    'type' => 'array',
                                    'items' => [
                                        'type' => 'string',
                                    ],
                                ],
                                'errorIdentifier' => [
                                    'type' => 'string',
                                ],
                                'errorMessage' => [
                                    'type' => 'string',
                                ],
                            ],
                            'required' => [
                                'errors',
                                'errorIdentifier',
                                'errorMessage',
                            ],
                        ],
                    ],
                    'required' => [
                        'payload',
                        'httpCode',
                    ],
                ],
            ],
        ];
        $hashMap = [
            400 => [
                'json_schema_validation_error',
            ],
            500 => [
                'internal_server_error',
            ],
        ];

        foreach ($serializedThrownClasses as $codeAndIdentifier) {
            $hashMap[$codeAndIdentifier['errorCode']][] = $codeAndIdentifier['errorIdentifier'];
        }
        ksort($hashMap);
        foreach ($hashMap as $errorCode => $errorIdentifiers) {
            $errorIdentifiers = array_unique($errorIdentifiers);
            sort($errorIdentifiers);
            $errorSchema['anyOf'][] = [
                'type' => 'object',
                'additionalProperties' => false,
                'properties' => [
                    'httpCode' => [
                        'type' => 'integer',
                        'enum' => [$errorCode],
                    ],
                    'payload' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'properties' => [
                            'errors' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                            'errorIdentifier' => [
                                'type' => 'string',
                                'enum' => $errorIdentifiers,
                            ],
                            'errorMessage' => [
                                'type' => 'string',
                            ],
                        ],
                        'required' => [
                            'errors',
                            'errorIdentifier',
                            'errorMessage',
                        ],
                    ],
                ],
                'required' => [
                    'payload',
                    'httpCode',
                ],
            ];
        }

        return $errorSchema;
    }

    /**
     * @return string[]
     *
     * @throws ExceptionSerializerFailed
     */
    private function retrieveThrownClassesFromControllerClassAndMethod(string $controllerClassAndMethod): array
    {
        $handlerServiceClass = $this->locateHandlerServiceFromControllerClassAndMethod($controllerClassAndMethod);
        $allThrownExceptionAnnotationsInHandler = $this->thrownExceptionAnnotationsInClassName($handlerServiceClass);

        $thrownClasses = $this->resolveClassNamesFromAnnotationClassNames(
            $allThrownExceptionAnnotationsInHandler,
            $handlerServiceClass,
            sprintf('Error occurred while resolving exceptions from %s', $controllerClassAndMethod)
        );

        if ([] === $thrownClasses) {
            throw new ExceptionSerializerFailed('All handlers must have exceptions, given they all touch the event store or a projection');
        }

        return $thrownClasses;
    }

    /**
     * @throws ExceptionSerializerFailed
     */
    private function locateHandlerServiceFromControllerClassAndMethod(string $controllerClassAndMethod): string
    {
        $unresolvedHandlerServiceClass = $this->handlerServiceClassFromControllerClassAndMethod($controllerClassAndMethod);

        try {
            $reflectionMethod = new \ReflectionMethod($controllerClassAndMethod);
            $controllerClass = $reflectionMethod->getDeclaringClass()->getName();
            if (!is_string($controllerClass)) {
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $reflectionException) {
            throw new ExceptionSerializerFailed('Could not build reflection method for '.$controllerClassAndMethod);
        }

        $resolveClassErrorMessage = sprintf('Error occurred while resolving service class from %s', $controllerClassAndMethod);
        $resolvedClasses = $this->resolveClassNamesFromAnnotationClassNames(
            [$unresolvedHandlerServiceClass],
            $controllerClass,
            $resolveClassErrorMessage
        );

        if (!is_array($resolvedClasses) || !array_key_exists(0, $resolvedClasses) || !is_string($resolvedClasses[0])) {
            throw new ExceptionSerializerFailed($resolveClassErrorMessage);
        }

        return $resolvedClasses[0];
    }

    /**
     * @throws ExceptionSerializerFailed
     */
    private function handlerServiceClassFromControllerClassAndMethod(string $controllerClassAndMethod): string
    {
        try {
            $reflectionMethod = new \ReflectionMethod($controllerClassAndMethod);
            $fileName = $reflectionMethod->getFileName();
            if (!is_string($fileName)) {
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $reflectionException) {
            throw new ExceptionSerializerFailed('Could not build reflection method for '.$controllerClassAndMethod);
        }

        $fileLines = file($fileName);
        if (!is_array($fileLines)) {
            throw new ExceptionSerializerFailed('Could not find file lines in file '.$fileName.' for '.$controllerClassAndMethod);
        }

        $startLine = $reflectionMethod->getStartLine();
        $endLine = $reflectionMethod->getEndLine();
        if (
            false === is_int($startLine) ||
            false === is_int($endLine)
        ) {
            throw new ExceptionSerializerFailed('Could not find starting line nor end line in '.$fileName.' for '.$controllerClassAndMethod);
        }

        $methodLines = array_slice($fileLines, $startLine, $endLine - $startLine);

        $matchingLine = null;
        foreach ($methodLines as $line) {
            if (preg_match('/(Handler)/', $line)) {
                $lineWithoutSpaces = preg_replace('/\s+/', '', $line);
                if (is_string($lineWithoutSpaces)) {
                    $matchingLine = $lineWithoutSpaces;
                }
                break;
            }
        }

        if (null === $matchingLine) {
            throw new ExceptionSerializerFailed('Cannot find line which might have a handler service class in '.$controllerClassAndMethod);
        }

        $matches = [];
        preg_match('/getService\(([\w_\-\d]+)\:\:class\)/', $matchingLine, $matches);
        $handlerServiceClass = null;
        if (array_key_exists(1, $matches)) {
            $handlerServiceClass = $matches[1];
        }
        if (!is_string($handlerServiceClass)) {
            throw new ExceptionSerializerFailed('Cannot find service class after finding a line which might have had a handler service class in '.$controllerClassAndMethod);
        }

        return $handlerServiceClass;
    }

    /**
     * Collects every Throwable in an annotation.
     * In the examples below, % is used instead of @ to avoid conflicts in PHPStan or in IDEs.
     * Collects from multiple statements
     *   Eg: %throws Exception1.
     *
     *       %throws Exception2)
     *       %throws \RuntimeException
     * Collects from multiple definition in a single statement
     *   Eg: %throws Exception1|Exception2
     *
     * @throws ExceptionSerializerFailed
     */
    private function thrownExceptionAnnotationsInClassName(string $handlerService): array
    {
        try {
            $reflectionMethod = new \ReflectionMethod($handlerService, 'handle');
            $annotation = $reflectionMethod->getDocComment();
            if (!is_string($annotation)) {
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $reflectionException) {
            throw new ExceptionSerializerFailed('Cannot find annotation for handle method in service of class '.$handlerService);
        }

        $annotationLines = explode("\n", $annotation);

        $allThrows = [];

        foreach ($annotationLines as $line) {
            $line = preg_replace('/\s+/', '', $line);
            if (null === $line) {
                continue;
            }
            if ($stringPositionOfThrows = strpos($line, '@throws')) {
                $allThrows = array_merge(
                    $allThrows,
                    explode(
                        '|',
                        substr(
                            $line,
                            $stringPositionOfThrows + strlen('@throws')
                        )
                    )
                );
            }
        }

        return $allThrows;
    }

    /**
     * @throws ExceptionSerializerFailed
     */
    private function resolveClassNamesFromAnnotationClassNames(array $annotationClassNames, string $occurringInClassName, string $additonalErrorMessage)
    {
        $classNames = [];
        $useStatements = $this->useStatementsInClassName($occurringInClassName);
        $namespace = $this->namespaceFromClassName($occurringInClassName);

        foreach ($annotationClassNames as $annotationClassName) {
            if (0 === strpos($annotationClassName, '\\')) {
                $className = $annotationClassName;
            } elseif (array_key_exists(trim($annotationClassName), $useStatements)) {
                $className = $useStatements[$annotationClassName];
            } else {
                //same namespace as handler
                $className = $namespace.'\\'.$annotationClassName;
            }

            if (
                (!class_exists($className)) ||
                (!is_string($className))
            ) {
                throw new ExceptionSerializerFailed(sprintf('A corresponding class cannot be found for annotation %s. Additional information: ', $annotationClassName));
            }

            $classNames[] = $className;
        }

        return $classNames;
    }

    /**
     * @throws ExceptionSerializerFailed
     */
    private function useStatementsInClassName(string $className): array
    {
        try {
            $reflectionClass = new \ReflectionClass($className);
            $fileName = $reflectionClass->getFileName();
            if (!is_string($fileName)) {
                throw new \ReflectionException();
            }
        } catch (\ReflectionException $reflectionException) {
            throw new ExceptionSerializerFailed('Could not find file for service of class '.$className);
        }

        $fileLines = file($fileName);
        if (!is_array($fileLines)) {
            throw new ExceptionSerializerFailed('Could not find file lines for service of class '.$className);
        }

        $useStatements = [];
        foreach ($fileLines as $line) {
            $line = trim(rtrim(trim($line), ';'));

            if (0 === strpos($line, 'use')) { //finds the use statement
                $statement = substr($line, strlen('use'));
                $statementBreakUsingAs = explode(' as ', $statement); //has to have those spaces around ' as '

                $alias = array_key_exists(1, $statementBreakUsingAs) ? trim($statementBreakUsingAs[1]) : null;
                $class = trim($statementBreakUsingAs[0]);

                //if there is alias
                if ($alias) {
                    $useStatements[$alias] = $class;
                } else {
                    $classParts = explode('\\', $class);
                    $className = end($classParts);
                    $useStatements[$className] = $class;
                }
            }

            //stops checking for use statement once class definition starts
            if (strpos($line, 'class ')) { //has to have the space 'class '
                break;
            }
        }

        return $useStatements;
    }

    /**
     * @throws ExceptionSerializerFailed
     */
    private function namespaceFromClassName(string $className): string
    {
        try {
            $reflectionMethod = new \ReflectionClass($className);

            return $reflectionMethod->getNamespaceName();
        } catch (\ReflectionException $reflectionException) {
            throw new ExceptionSerializerFailed('Reflection exception for class '.$className);
        }
    }
}
