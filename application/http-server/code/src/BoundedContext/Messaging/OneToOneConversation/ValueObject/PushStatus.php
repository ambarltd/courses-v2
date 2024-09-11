<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject;

class PushStatus
{
    /**
     * @var bool
     */
    private $pushed;

    /**
     * @var bool
     */
    private $pulledBySender;

    /**
     * @var bool
     */
    private $deletedBySender;

    /**
     * @var bool
     */
    private $rejectedByRecipient;

    private function __construct()
    {
        $this->pushed = false;
        $this->pulledBySender = false;
        $this->deletedBySender = false;
        $this->rejectedByRecipient = false;
    }

    public function isPushed(): bool
    {
        return $this->pushed;
    }

    public function isPulledBySender(): bool
    {
        return $this->pulledBySender;
    }

    public function isDeletedBySender(): bool
    {
        return $this->deletedBySender;
    }

    public function isRejectedByRecipient(): bool
    {
        return $this->rejectedByRecipient;
    }

    public static function pushed(): self
    {
        $status = new self();
        $status->pushed = true;

        return $status;
    }

    public static function pulledBySender(): self
    {
        $status = new self();
        $status->pulledBySender = true;

        return $status;
    }

    public static function deletedBySender(): self
    {
        $status = new self();
        $status->deletedBySender = true;

        return $status;
    }

    public static function rejectedByRecipient(): self
    {
        $status = new self();
        $status->rejectedByRecipient = true;

        return $status;
    }
}
