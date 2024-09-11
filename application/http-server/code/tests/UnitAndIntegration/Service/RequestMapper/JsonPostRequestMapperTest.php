<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\Service\RequestMapper;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\Command\VerifyPrimaryEmail;
use Galeas\Api\BoundedContext\Security\Session\Command\SignIn;
use Galeas\Api\BoundedContext\Security\Session\Event\SignedIn;
use Galeas\Api\BoundedContext\Security\Session\Projection\Session\SessionProcessor;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Service\RequestMapper\JsonPostRequestMapper;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class JsonPostRequestMapperTest extends KernelTestBase
{
    /**
     * @var SignedIn
     */
    private $signedInEventForAuthorizerResolving;

    public function setUp(): void
    {
        parent::setUp();

        $this->signedInEventForAuthorizerResolving = SignedIn::fromProperties(
            [],
            Id::createNew(),
            'with_username',
            'with_email',
            'with_hashed_password',
            'by_device_label',
            '127.127.127.190'
        );

        $this->getContainer()
            ->get(SessionProcessor::class)
            ->process($this->signedInEventForAuthorizerResolving);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateCommand(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        /** @var VerifyPrimaryEmail $command */
        $command = $requestMapper->createCommandOrQueryFromEndUserRequest(
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
        Assert::assertObjectNotHasAttribute('hack', $command);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateCommandOverrideReferer(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        /** @var VerifyPrimaryEmail $command */
        $command = $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateCommandOverrideRefererNull(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        /** @var VerifyPrimaryEmail $command */
        $command = $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateCommandOverrideUserAgent(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        /** @var VerifyPrimaryEmail $command */
        $command = $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateCommandOverrideUserAgentNull(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        /** @var VerifyPrimaryEmail $command */
        $command = $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateCommand_ResolveWithIp(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        /** @var SignIn $command */
        $command = $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @expectedException \Galeas\Api\Service\RequestMapper\Exception\InvalidContentType
     *
     * @throws \Exception
     */
    public function testRejectsNonJsonContentType(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @expectedException \Galeas\Api\Service\RequestMapper\Exception\InvalidJson
     */
    public function testRejectsInvalidJson(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        $requestMapper->createCommandOrQueryFromEndUserRequest(
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

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testJsonFromRequest(): void
    {
        $requestMapper = $this->getContainer()->get(JsonPostRequestMapper::class);

        $json = $requestMapper->jsonBodyFromRequest(
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

    /**
     * @throws \Exception
     */
    private function jsonEncodeOrThrowException(array $encodeThis): string
    {
        $encoded = json_encode($encodeThis);

        if (is_string($encoded)) {
            return $encoded;
        }

        throw new \Exception();
    }
}
