<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\RequestMapper;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Security\Session\Command\RefreshToken;
use Galeas\Api\BoundedContext\Security\Session\Command\SignIn;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\UserIdFromSignedInSessionToken;
use Galeas\Api\Service\RequestMapper\Exception\InvalidContentType;
use Galeas\Api\Service\RequestMapper\JsonPostRequestMapper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class JsonPostRequestMapperTest extends UnitTestBase
{
    private JsonPostRequestMapper $jsonPostRequestMapper;

    public function setUp(): void
    {
        parent::setUp();
        $userIdFromSignedInSessionToken = $this->createMock(UserIdFromSignedInSessionToken::class);
        $userIdFromSignedInSessionToken->method('userIdFromSignedInSessionToken')
            ->with(
                $this->equalTo('token_123'),
                $this->anything()
            )
            ->willReturn('user_id_123');
        $this->jsonPostRequestMapper = new JsonPostRequestMapper(
            $userIdFromSignedInSessionToken,
            "7200"
        );
    }

    public function testCreateCommand(): void
    {
        /** @var VerifyPrimaryEmail $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'verificationCode' => 'abcdefghijklmnopqrstuvwxyz',
                    'metadata' => [
                        'latitude' => 51.5074,
                        'longitude' => 0.1278,
                        'devicePlatform' => 'linux',
                        'deviceModel' => 'Penguin 1.0',
                        'deviceOSVersion' => 'Ubuntu 14.04',
                        'deviceOrientation' => 'landscape',
                        // overrides not allowed
                        'city' => 'hack',
                        'environment' => 'native',
                        'ipAddress' => '50.50.50.50',
                        'hack' => 'hack',
                    ],
                    // overrides not allowed
                    'hack' => 'hack',
                ])
            ),
            VerifyPrimaryEmail::class
        );

        Assert::assertInstanceOf(VerifyPrimaryEmail::class, $command);
        Assert::assertEquals('abcdefghijklmnopqrstuvwxyz', $command->verificationCode);
        Assert::assertEquals(51.5074, $command->metadata['latitude']);
        Assert::assertEquals(0.1278, $command->metadata['longitude']);
        Assert::assertEquals('linux', $command->metadata['devicePlatform']);
        Assert::assertEquals('Penguin 1.0', $command->metadata['deviceModel']);
        Assert::assertEquals('Ubuntu 14.04', $command->metadata['deviceOSVersion']);
        Assert::assertEquals('landscape', $command->metadata['deviceOrientation']);

        // overrides not allowed
        Assert::assertEquals('77.96.237.178', $command->metadata['ipAddress']);
        Assert::assertEquals('Test_UserAgent', $command->metadata['userAgent']);
        Assert::assertEquals('example.com', $command->metadata['referer']);
        Assert::assertArrayNotHasKey('hack', $command->metadata);
        Assert::assertObjectNotHasProperty('hack', $command);
    }

    public function testAuthenticatedUserIdOnRefreshToken(): void
    {
        /** @var RefreshToken $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'authenticatedUserId' => 'hackAuthenticatedUserId',
                    'withIp' => 'hackIpAddress',
                    'withSessionToken' => 'hackWithSessionToken',
                    'metadata' => [
                        'withSessionToken' => 'token_123',
                        'latitude' => 51.5074,
                        'longitude' => 0.1278,
                        'devicePlatform' => 'linux',
                        'deviceModel' => 'Penguin 1.0',
                        'deviceOSVersion' => 'Ubuntu 14.04',
                        'deviceOrientation' => 'landscape',
                        // overrides not allowed
                        'city' => 'hack',
                        'environment' => 'native',
                        'ipAddress' => '50.50.50.50',
                        'hack' => 'hack',
                    ],
                    // overrides not allowed
                    'hack' => 'hack',
                ])
            ),
            RefreshToken::class
        );

        Assert::assertInstanceOf(RefreshToken::class, $command);
        Assert::assertEquals('user_id_123', $command->authenticatedUserId);
        Assert::assertEquals('77.96.237.178', $command->withIp);
        Assert::assertEquals('token_123', $command->withSessionToken);
        Assert::assertEquals(51.5074, $command->metadata['latitude']);
        Assert::assertEquals(0.1278, $command->metadata['longitude']);
        Assert::assertEquals('linux', $command->metadata['devicePlatform']);
        Assert::assertEquals('Penguin 1.0', $command->metadata['deviceModel']);
        Assert::assertEquals('Ubuntu 14.04', $command->metadata['deviceOSVersion']);
        Assert::assertEquals('landscape', $command->metadata['deviceOrientation']);

        // overrides not allowed
        Assert::assertEquals('77.96.237.178', $command->metadata['ipAddress']);
        Assert::assertEquals('Test_UserAgent', $command->metadata['userAgent']);
        Assert::assertEquals('example.com', $command->metadata['referer']);
        Assert::assertEquals('token_123', $command->metadata['withSessionToken']);
        Assert::assertArrayNotHasKey('hack', $command->metadata);
        Assert::assertObjectNotHasProperty('hack', $command);

    }

    public function testCreateCommandOverrideReferer(): void
    {
        /** @var VerifyPrimaryEmail $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'metadata' => [
                        'referer' => 'www.example.com',
                    ],
                ])
            ),
            VerifyPrimaryEmail::class
        );

        Assert::assertEquals('www.example.com', $command->metadata['referer']);
    }

    public function testCreateCommandOverrideRefererNull(): void
    {
        /** @var VerifyPrimaryEmail $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'metadata' => [
                        'referer' => null,
                    ],
                ])
            ),
            VerifyPrimaryEmail::class
        );

        Assert::assertEquals(null, $command->metadata['referer']);
    }

    public function testCreateCommandOverrideUserAgent(): void
    {
        /** @var VerifyPrimaryEmail $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'metadata' => [
                        'userAgent' => 'Test_UserAgent_Override',
                    ],
                ])
            ),
            VerifyPrimaryEmail::class
        );

        Assert::assertEquals('Test_UserAgent_Override', $command->metadata['userAgent']);
    }

    public function testCreateCommandOverrideUserAgentNull(): void
    {
        /** @var VerifyPrimaryEmail $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'metadata' => [
                        'userAgent' => null,
                    ],
                ])
            ),
            VerifyPrimaryEmail::class
        );

        Assert::assertEquals(null, $command->metadata['userAgent']);
    }

    public function testCreateCommand_ResolveWithIp(): void
    {
        /** @var SignIn $command */
        $command = $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'withUsernameOrEmail' => 'username_123',
                    'withPassword' => 'username_123',
                    'byDeviceLabel' => 'username_123',
                    'withIp' => 'hack',
                    'metadata' => [
                        'latitude' => 51.5074,
                        'longitude' => 0.1278,
                        'devicePlatform' => 'linux',
                        'deviceModel' => 'Penguin 1.0',
                        'deviceOSVersion' => 'Ubuntu 14.04',
                        'deviceOrientation' => 'landscape',
                    ],
                ])
            ),
            SignIn::class
        );

        Assert::assertInstanceOf(SignIn::class, $command);
        Assert::assertEquals(
            '77.96.237.178',
            $command->withIp
        );
    }

    public function testRejectsNonJsonContentType(): void
    {
        $this->expectException(InvalidContentType::class);
        $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'text/html',
                ],
                $this->jsonEncodeOrThrowException([])
            ),
            SignUp::class
        );
    }

    public function testRejectsInvalidJson(): void
    {
        $this->expectException(\Galeas\Api\Service\RequestMapper\Exception\InvalidJson::class);
        $this->jsonPostRequestMapper->createCommandOrQueryFromEndUserRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                ],
                '{{}}'
            ),
            SignUp::class
        );
    }

    public function testJsonFromRequest(): void
    {
        $json = $this->jsonPostRequestMapper->jsonBodyFromRequest(
            Request::create(
                '',
                'GET',
                [],
                [],
                [],
                [
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_USER_AGENT' => 'Test_UserAgent',
                    'REMOTE_ADDR' => '77.96.237.178',
                    'HTTP_REFERER' => 'example.com',
                ],
                $this->jsonEncodeOrThrowException([
                    'verificationCode' => 'abcdefghijklmnopqrstuvwxyz',
                    'metadata' => [
                        'latitude' => 51.5074,
                        'longitude' => 0.1278,
                        'devicePlatform' => 'linux',
                        'deviceModel' => 'Penguin 1.0',
                        'deviceOSVersion' => 'Ubuntu 14.04',
                        'deviceOrientation' => 'landscape',
                        // overrides not allowed
                        'environment' => 'native',
                        'ipAddress' => '50.50.50.50',
                        'hack' => 'hack',
                    ],
                    // overrides not allowed
                    'hack' => 'hack',
                ])
            )
        );

        Assert::assertEquals(
            '{"verificationCode":"abcdefghijklmnopqrstuvwxyz","metadata":{"latitude":51.5074,"longitude":0.1278,"devicePlatform":"linux","deviceModel":"Penguin 1.0","deviceOSVersion":"Ubuntu 14.04","deviceOrientation":"landscape","environment":"native","ipAddress":"50.50.50.50","hack":"hack"},"hack":"hack"}',
            $json
        );
    }

    private function jsonEncodeOrThrowException(array $encodeThis): string
    {
        $encoded = json_encode($encodeThis);

        if (is_string($encoded)) {
            return $encoded;
        }

        throw new \Exception();
    }
}
