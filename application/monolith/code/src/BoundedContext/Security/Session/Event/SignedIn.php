<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\SessionToken\SessionTokenCreator;

class SignedIn implements EventCreatedSession
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $asUser;

    /**
     * @var string|null
     */
    private $withUsername;

    /**
     * @var string|null
     */
    private $withEmail;

    /**
     * @var string
     */
    private $withHashedPassword;

    /**
     * @var string
     */
    private $byDeviceLabel;

    /**
     * @var string
     */
    private $withIp;

    /**
     * @var string
     */
    private $sessionTokenCreated;

    public function asUser(): Id
    {
        return $this->asUser;
    }

    public function withUsername(): ?string
    {
        return $this->withUsername;
    }

    public function withEmail(): ?string
    {
        return $this->withEmail;
    }

    /**
     * @return mixed
     */
    public function withHashedPassword()
    {
        return $this->withHashedPassword;
    }

    public function byDeviceLabel(): string
    {
        return $this->byDeviceLabel;
    }

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function sessionTokenCreated(): string
    {
        return $this->sessionTokenCreated;
    }

    /**
     * @return SignedIn
     */
    public static function fromProperties(
        array $metadata,
        Id $asUser,
        ?string $withUsername,
        ?string $withEmail,
        string $withHashedPassword,
        string $byDeviceLabel,
        string $withIp
    ): self {
        $aggregateId = Id::createNew();
        $event = new self($aggregateId, $asUser, $metadata);

        $event->asUser = $asUser;
        $event->withUsername = $withUsername;
        $event->withEmail = $withEmail;
        $event->withHashedPassword = $withHashedPassword;
        $event->byDeviceLabel = $byDeviceLabel;
        $event->withIp = $withIp;
        $event->sessionTokenCreated = SessionTokenCreator::create();

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function createSession(): Session
    {
        return Session::fromProperties(
            $this->aggregateId(),
            SessionDetails::fromProperties(
                $this->asUser,
                $this->withUsername,
                $this->withEmail,
                $this->withHashedPassword,
                $this->byDeviceLabel,
                $this->withIp,
                $this->sessionTokenCreated
            ),
            null
        );
    }
}
