<?php

declare(strict_types=1);

namespace Galeas\Api\Service\RequestMapper;

use Galeas\Api\BoundedContext\Security\Session\Projection\Session\UserIdFromSignedInSessionToken;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Service\RequestMapper\Exception\CannotResolveAuthorizerFromSessionTokenDatabase;
use Galeas\Api\Service\RequestMapper\Exception\InvalidContentType;
use Galeas\Api\Service\RequestMapper\Exception\InvalidJson;
use Galeas\Api\Service\RequestMapper\Exception\MissingExpectedSessionToken;
use Symfony\Component\HttpFoundation\Request;

class JsonPostRequestMapper
{

    /**
     * @var UserIdFromSignedInSessionToken
     */
    private $userIdFromSignedInSessionToken;

    /**
     * @var int
     */
    private $sessionExpiresAfterSeconds;

    /**
     * @throws \RuntimeException
     */
    public function __construct(
        UserIdFromSignedInSessionToken $userIdFromSignedInSessionToken,
        string $sessionExpiresAfterSeconds
    ) {
        $this->userIdFromSignedInSessionToken = $userIdFromSignedInSessionToken;
        if (!is_numeric($sessionExpiresAfterSeconds)) {
            throw new \RuntimeException('Invalid sessionExpiresAfterSeconds: '.$sessionExpiresAfterSeconds);
        }
        $this->sessionExpiresAfterSeconds = intval($sessionExpiresAfterSeconds);
    }

    /**
     * @throws InvalidJson|InvalidContentType
     */
    private function requestJsonToRequestArray(Request $request): array
    {
        $contentType = $request->headers->get('content-type');

        if (!is_string($contentType)) {
            throw new InvalidContentType();
        }
        if (
            is_string($contentType) &&
            'application/json' !== substr($contentType, 0, 16)
        ) {
            throw new InvalidContentType();
        }

        try {
            $content = $request->getContent();
        } catch (\LogicException $exception) {
            throw new InvalidContentType();
        }

        $requestArray = [];
        if (!empty($content)) {
            $requestArray = json_decode($content, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new InvalidJson(sprintf('%s is not in a valid JSON format', $content));
            }
        }

        return $requestArray;
    }

    /**
     * @throws GeoDatabaseCrash
     */
    private function processRequestArrayComingFromEndUser(
        array $requestArray,
        string $ipAddress,
        ?string $authorizerId,
        ?string $sourceEventId,
        ?string $userAgent,
        ?string $referer,
        ?string $withSessionToken
    ): array {
        $requestArray['authorizerId'] = $authorizerId;
        $requestArray['sourceEventId'] = $sourceEventId;
        $requestArray['withSessionToken'] = $withSessionToken;

        $receivedEnvironment = null;
        if (
            array_key_exists('metadata', $requestArray) &&
            array_key_exists('environment', $requestArray['metadata']) &&
            in_array($requestArray['metadata']['environment'], ['native', 'browser', 'other', 'unknown'], true)
        ) {
            $receivedEnvironment = $requestArray['metadata']['environment'];
        }

        $receivedDevicePlatform = null;
        if (
            array_key_exists('metadata', $requestArray) &&
            array_key_exists('devicePlatform', $requestArray['metadata']) &&
            in_array($requestArray['metadata']['devicePlatform'], ['ios', 'android', 'mac', 'windows', 'linux', 'other', 'unknown'], true)
        ) {
            $receivedDevicePlatform = $requestArray['metadata']['devicePlatform'];
        }

        $receivedDeviceModel = null;
        if (
            array_key_exists('metadata', $requestArray) &&
            array_key_exists('deviceModel', $requestArray['metadata']) &&
            is_string($requestArray['metadata']['deviceModel']) &&
            '' !== $requestArray['metadata']['deviceModel']
        ) {
            $receivedDeviceModel = $requestArray['metadata']['deviceModel'];
        }

        $receivedDeviceOSVersion = null;
        if (
            array_key_exists('metadata', $requestArray) &&
            array_key_exists('deviceOSVersion', $requestArray['metadata']) &&
            is_string($requestArray['metadata']['deviceOSVersion']) &&
            '' !== $requestArray['metadata']['deviceOSVersion']
        ) {
            $receivedDeviceOSVersion = $requestArray['metadata']['deviceOSVersion'];
        }

        $receivedDeviceOrientation = null;
        if (
            array_key_exists('metadata', $requestArray) &&
            array_key_exists('deviceOrientation', $requestArray['metadata']) &&
            is_string($requestArray['metadata']['deviceOrientation']) &&
            in_array($requestArray['metadata']['deviceOrientation'], ['portrait', 'landscape', 'does_not_apply', 'other', 'unknown'], true)
        ) {
            $receivedDeviceOrientation = $requestArray['metadata']['deviceOrientation'];
        }

        // Override metadata such that only whitelisted fields can be passed by the end user.
        $requestArray['metadata'] = [
            'environment' => $receivedEnvironment,
            'devicePlatform' => $receivedDevicePlatform,
            'deviceModel' => $receivedDeviceModel,
            'deviceOSVersion' => $receivedDeviceOSVersion,
            'deviceOrientation' => $receivedDeviceOrientation,
            'ipAddress' => $ipAddress,
            'userAgent' => array_key_exists('userAgent', $requestArray['metadata']) ? $requestArray['metadata']['userAgent'] : $userAgent,
            'referer' => array_key_exists('referer', $requestArray['metadata']) ? $requestArray['metadata']['referer'] : $referer,
            'withSessionToken' => $withSessionToken,
        ];

        return $requestArray;
    }

    private function requestArrayToCommandOrQuery(string $commandOrQueryClass, array $requestArray): object
    {
        $command = new $commandOrQueryClass();

        foreach ($requestArray as $propertyName => $value) {
            if (property_exists($commandOrQueryClass, $propertyName)) {
                $command->$propertyName = $value;
            }
        }

        return $command;
    }

    /**
     * @throws InvalidContentType|InvalidJson
     * @throws CannotResolveAuthorizerFromSessionTokenDatabase|MissingExpectedSessionToken
     */
    public function createCommandOrQueryFromEndUserRequest(Request $request, string $commandOrQueryClass): object
    {
        $requestArray = $this->requestJsonToRequestArray($request);

        $authorizerId = null;
        $withSessionToken = $request->headers->get('X-With-Session-Token', null);
        if (is_array($withSessionToken)) {
            $withSessionToken = array_values($withSessionToken)[0];
        }

        if (
            array_key_exists('metadata', $requestArray) &&
            array_key_exists('withSessionToken', $requestArray['metadata']) &&
            (is_string($requestArray['metadata']['withSessionToken']) || is_null($requestArray['metadata']['withSessionToken']))
        ) {
            $withSessionToken = $requestArray['metadata']['withSessionToken'];
        }

        try {
            if (is_string($withSessionToken)) {
                $authorizerId = $this->userIdFromSignedInSessionToken
                    ->userIdFromSignedInSessionToken(
                        $withSessionToken,
                        (new \DateTimeImmutable())->modify('-'.$this->sessionExpiresAfterSeconds.' seconds')
                    );
            }
        } catch (ProjectionCannotRead $exception) {
            throw new CannotResolveAuthorizerFromSessionTokenDatabase();
        }

        if (
            property_exists(new $commandOrQueryClass(), 'authorizerId') &&
            null === $authorizerId
        ) {
            throw new MissingExpectedSessionToken();
        }

        if (
            property_exists(new $commandOrQueryClass(), 'withIp')
        ) {
            $requestArray['withIp'] = $request->server->get('REMOTE_ADDR');
        }

        $userAgent = $request->headers->get('User-Agent', null);
        if (is_array($userAgent)) {
            $userAgent = array_values($userAgent)[0];
        }
        $referer = $request->headers->get('Referer', null);

        if (is_array($referer)) {
            $referer = array_values($referer)[0];
        }

        $requestArray = $this->processRequestArrayComingFromEndUser(
            $requestArray,
            // instead of dealing with possible X-Forwarded-For tainting in $request->getClientIp()
            $request->server->get('REMOTE_ADDR'),
            $authorizerId,
            null,
            $userAgent,
            $referer,
            $withSessionToken
        );

        return $this->requestArrayToCommandOrQuery($commandOrQueryClass, $requestArray);
    }

    /**
     * @throws InvalidJson|InvalidContentType
     */
    public function jsonBodyFromRequest(Request $request): string
    {
        $requestArray = $this->requestJsonToRequestArray($request);

        $return = json_encode($requestArray);

        if (is_string($return)) {
            return $return;
        }

        throw new InvalidJson();
    }
}
