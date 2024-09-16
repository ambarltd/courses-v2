<?php

declare(strict_types=1);

namespace Galeas\Api\Common\Event;

use Galeas\Api\BoundedContext\Identity\User;
use Galeas\Api\BoundedContext\Security\Session;
use Galeas\Api\Common\Event\Exception as EventException;

abstract class EventReflectionBaseClass {
    /**
     * @var array<string, string>
     */
    private static array $eventNamesToEventClasses = [
        'Identity_User_SignedUp' => User\Event\SignedUp::class,
        'Identity_User_PrimaryEmailVerificationCodeSent' => User\Event\PrimaryEmailVerificationCodeSent::class,
        'Identity_User_PrimaryEmailVerified' => User\Event\PrimaryEmailVerified::class,
        'Identity_User_PrimaryEmailChangeRequested' => User\Event\PrimaryEmailChangeRequested::class,
        'Security_Session_SignedIn' => Session\Event\SignedIn::class,
        'Security_Session_TokenRefreshed' => Session\Event\TokenRefreshed::class,
        'Security_Session_SignedOut' => Session\Event\SignedOut::class,
    ];

    /**
     * @var array<string, string>
     */
    private static array $eventClassesToEventNames = [];

    /**
     * @var array<string, string>
     */
    private static array $eventClassesToCreationMethodNames = [];

    /**
     * @var array<string, string>
     */
    private static array $eventClassesToTransformationMethodNames = [];

    /**
     * @var array<string, \ReflectionMethod>
     */
    private static array $eventNamesToReflectionConstructorMethodsCache = [];

    public static function allEventClasses(): array {
        return array_values(self::$eventNamesToEventClasses);
    }
    /**
     * @throws EventException\NoEventClassMappingFound
     * @throws EventException\EventMappingReflectionError
     */
    protected static function eventClassToEventName(string $eventClass): string
    {
        self::setup();

        if (array_key_exists($eventClass, self::$eventClassesToEventNames)) {
            return self::$eventClassesToEventNames[$eventClass];
        }

        throw new EventException\NoEventClassMappingFound('No mapping found for class: '.$eventClass);
    }


    /**
     * @throws EventException\NoEventReflectionClassMappingMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    protected static function eventNameToReflectionClassMethod(string $eventName): \ReflectionMethod
    {
        self::setup();

        if (array_key_exists($eventName, self::$eventNamesToReflectionConstructorMethodsCache)) {
            return self::$eventNamesToReflectionConstructorMethodsCache[$eventName];
        }

        throw new EventException\NoEventReflectionClassMappingMethodFound('No mapping found for event name '.$eventName);
    }

    /**
     * @throws EventException\NoCreationMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    protected static function eventClassToCreationMethodName(string $eventClass): string
    {
        self::setup();

        if (array_key_exists($eventClass, self::$eventClassesToCreationMethodNames)) {
            return self::$eventClassesToCreationMethodNames[$eventClass];
        }

        throw new EventException\NoCreationMethodFound('No mapping found for class name '.$eventClass);
    }

    /**
     * @throws EventException\NoTransformationMethodFound
     * @throws EventException\EventMappingReflectionError
     */
    protected static function eventClassToTransformationMethodName(string $eventClass): string
    {
        self::setup();

        if (array_key_exists($eventClass, self::$eventClassesToTransformationMethodNames)) {
            return self::$eventClassesToTransformationMethodNames[$eventClass];
        }

        throw new EventException\NoTransformationMethodFound('No mapping found for class name '.$eventClass);
    }

    /**
     * @throws EventException\EventMappingReflectionError
     */
    private static function setup(): void
    {
        try {
            if ([] === self::$eventClassesToEventNames) {
                self::$eventClassesToEventNames = array_flip(self::$eventNamesToEventClasses);


                foreach (self::$eventNamesToEventClasses as $eventName => $eventClass) {
                    $reflectionClass = new \ReflectionClass($eventClass);

                    $creationMethod = self::findFirstMethodBeginningWith($reflectionClass, 'create');
                    if (null !== $creationMethod) {
                        self::$eventClassesToCreationMethodNames[$eventClass] = $creationMethod;
                    }

                    $transformationMethod = self::findFirstMethodBeginningWith($reflectionClass, 'transform');
                    if (null !== $transformationMethod) {
                        self::$eventClassesToTransformationMethodNames[$eventClass] = $transformationMethod;
                    }

                    $reflectionConstructorMethod = $reflectionClass->getMethod('reflectionConstructor');
                    $reflectionConstructorMethod->setAccessible(true);
                    self::$eventNamesToReflectionConstructorMethodsCache[$eventName] = $reflectionConstructorMethod;
                }
            }
        } catch (\ReflectionException $exception) {
            throw new EventException\EventMappingReflectionError('Reflection method failure');
        }
    }

    private static function findFirstMethodBeginningWith(\ReflectionClass $reflection, $beginningWith): ?string {
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            if (strpos($method->getName(), $beginningWith) === 0) {
                return $method->getName();
            }
        }

        return null;
    }
}