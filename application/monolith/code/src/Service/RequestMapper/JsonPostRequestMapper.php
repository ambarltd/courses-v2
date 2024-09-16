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
    private UserIdFromSignedInSessionToken $userIdFromSignedInSessionToken;

    private int $sessionExpiresAfterSeconds;

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
    public function jsonBodyFromRequest(Request $request): string
    {
        $requestArray = $this->requestJsonToRequestArray($request);

        $return = json_encode($requestArray);

        if (is_string($return)) {
            return $return;
        }

        throw new InvalidJson();
    }
    
    /**
     * In Commands, the End User Requester can never set properties named "authenticatedUserId" or "withIp".
     *
     * Instead:
     * - authenticatedUserId: JsonPostRequestMapper sets the value, by transforming a session token.
     * - withIp: JsonPostRequestMapper sets the value, by looking at the remote address.
     *
     * @throws InvalidContentType|InvalidJson
     * @throws CannotResolveAuthorizerFromSessionTokenDatabase|MissingExpectedSessionToken
     */
    public function createCommandOrQueryFromEndUserRequest(Request $request, string $commandOrQueryClass): object
    {
        $requestArray = $this->requestJsonToRequestArray($request);
        $overridenArray = $this->overrideSensitiveFieldsInRequestArrayAndRemoveMetadata($requestArray, $request, $commandOrQueryClass);
        $validatedMetadata = $this->metadataToValidatedMetadata($requestArray["metadata"], $request);

        $safeArray = $overridenArray;
        $safeArray["metadata"] = $validatedMetadata;

        return $this->safeArrayToCommandOrQuery($commandOrQueryClass, $safeArray);
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
    
    private function overrideSensitiveFieldsInRequestArrayAndRemoveMetadata(
        array $requestArray, 
        Request $request, 
        string $commandOrQueryClass
    ): array {
        $requestArray["authenticatedUserId"] = null;
        $requestArray["withIp"] = null;
        $requestArray["withSessionToken"] = null;

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
                $requestArray["authenticatedUserId"] = $this->userIdFromSignedInSessionToken
                    ->userIdFromSignedInSessionToken(
                        $withSessionToken,
                        (new \DateTimeImmutable())->modify('-'.$this->sessionExpiresAfterSeconds.' seconds')
                    );
                $requestArray['withSessionToken'] = $withSessionToken;
            }
        } catch (ProjectionCannotRead $exception) {
            throw new CannotResolveAuthorizerFromSessionTokenDatabase();
        }

        if (
            property_exists(new $commandOrQueryClass(), 'authenticatedUserId') &&
            null === $requestArray["authenticatedUserId"]
        ) {
            throw new MissingExpectedSessionToken();
        }

        if (
            property_exists(new $commandOrQueryClass(), 'withSessionToken') &&
            null === $requestArray["withSessionToken"]
        ) {
            throw new MissingExpectedSessionToken();
        }

        if (
            property_exists(new $commandOrQueryClass(), 'withIp')
        ) {
            $requestArray['withIp'] = $request->server->get('REMOTE_ADDR');
        }
        
        unset($requestArray["metadata"]);
        
        return $requestArray;
    }

    private function metadataToValidatedMetadata(
        array $metadata,
        Request $request
    ): array {

        $receivedEnvironment = null;
        if (
            array_key_exists('environment', $metadata) &&
            in_array($metadata['environment'], ['native', 'browser', 'other', 'unknown'], true)
        ) {
            $receivedEnvironment = $metadata['environment'];
        }

        $receivedDevicePlatform = null;
        if (
            array_key_exists('devicePlatform', $metadata) &&
            in_array($metadata['devicePlatform'], ['ios', 'android', 'mac', 'windows', 'linux', 'other', 'unknown'], true)
        ) {
            $receivedDevicePlatform = $metadata['devicePlatform'];
        }

        $receivedDeviceModel = null;
        if (
            array_key_exists('deviceModel', $metadata) &&
            is_string($metadata['deviceModel']) &&
            '' !== $metadata['deviceModel']
        ) {
            $receivedDeviceModel = $metadata['deviceModel'];
        }

        $receivedDeviceOSVersion = null;
        if (
            array_key_exists('deviceOSVersion', $metadata) &&
            is_string($metadata['deviceOSVersion']) &&
            '' !== $metadata['deviceOSVersion']
        ) {
            $receivedDeviceOSVersion = $metadata['deviceOSVersion'];
        }

        $receivedDeviceOrientation = null;
        if (
            array_key_exists('deviceOrientation', $metadata) &&
            is_string($metadata['deviceOrientation']) &&
            in_array($metadata['deviceOrientation'], ['portrait', 'landscape', 'does_not_apply', 'other', 'unknown'], true)
        ) {
            $receivedDeviceOrientation = $metadata['deviceOrientation'];
        }

        $receivedUserAgent = $request->headers->get('User-Agent', null);
        if (is_array($receivedUserAgent)) {
            $receivedUserAgent = array_values($receivedUserAgent)[0];
        }
        if (
            array_key_exists('userAgent', $metadata) &&
            (is_string($metadata['userAgent']) || null == $metadata['userAgent'])
        ) {
            $receivedUserAgent = $metadata['userAgent'];
        }

        $receivedReferer = $request->headers->get('Referer', null);
        if (is_array($receivedReferer)) {
            $receivedReferer = array_values($receivedReferer)[0];
        }
        if (
            array_key_exists('referer', $metadata) &&
            (is_string($metadata['referer']) || null == $metadata['referer'])
        ) {
            $receivedReferer = $metadata['referer'];
        }

        $receivedSessionToken = null;
        if (
            array_key_exists('withSessionToken', $metadata) &&
            is_string($metadata['withSessionToken']) &&
            '' !== $metadata['withSessionToken']
        ) {
            $receivedSessionToken = $metadata['withSessionToken'];
        }

        $receivedLatitude = null;
        if (
            array_key_exists('latitude', $metadata) &&
            (is_float($metadata['latitude']) || is_int($metadata['latitude']))
        ) {
            $receivedLatitude = $metadata['latitude'];
        }

        $receivedLongitude = null;
        if (
            array_key_exists('longitude', $metadata) &&
            (is_float($metadata['longitude']) || is_int($metadata['longitude']))
        ) {
            $receivedLongitude = $metadata['longitude'];
        }

        // Override metadata such that only allow listed fields can be passed by the end user.
        return [
            'environment' => $receivedEnvironment,
            'devicePlatform' => $receivedDevicePlatform,
            'deviceModel' => $receivedDeviceModel,
            'deviceOSVersion' => $receivedDeviceOSVersion,
            'deviceOrientation' => $receivedDeviceOrientation,
            'latitude' => $receivedLatitude,
            'longitude' => $receivedLongitude,
            'ipAddress' => $request->server->get('REMOTE_ADDR'),
            'userAgent' => $receivedUserAgent,
            'referer' => $receivedReferer,
            'withSessionToken' => $receivedSessionToken,
        ];
    }

    private function safeArrayToCommandOrQuery(string $commandOrQueryClass, array $safeArray): object
    {
        $command = new $commandOrQueryClass();

        foreach ($safeArray as $propertyName => $value) {
            if (property_exists($commandOrQueryClass, $propertyName)) {
                $command->$propertyName = $value;
            }
        }

        return $command;
    }
}
